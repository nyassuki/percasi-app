const db = require('../config/database');
const bcrypt = require('bcrypt');
const pool = require('../config/database');
const logger = require('../utils/logger');
const Security = require('../utils/security');

class Wallet {
    /**
     * Helper: Menghitung Signature Baris Wallet (Anti-Tamper)
     * Menggunakan presisi 2 desimal untuk konsistensi HMAC.
     */
    static generateWalletSignature(data) {
        const payload = {
            user_id: data.user_id,
            balance: parseFloat(data.balance).toFixed(2),
            locked_balance: parseFloat(data.locked_balance).toFixed(2),
            status: data.status
        };
        return Security.generateSignature(payload);
    }

    /**
     * Cari Wallet dengan Integrity Check
     */
    static async findByUserId(userId, connection = null) {
        const client = connection || db;
        const query = `SELECT * FROM wallets WHERE user_id = ? LIMIT 1`;
        try {
            const [rows] = await client.execute(query, [userId]);
            if (rows.length === 0) return null;

            const wallet = rows[0];

            // VERIFIKASI INTEGRITAS DATA
            const expectedSignature = this.generateWalletSignature(wallet);
            if (wallet.signature !== expectedSignature) {
                logger.error(`[CRITICAL] Wallet Tampering Detected! User: ${userId}`);
                wallet.is_tampered = true;
            }

            return wallet;
        } catch (error) {
            logger.error("[Wallet.findByUserId]", error);
            throw error;
        }
    }

    /**
     * Buat Wallet Baru (Inisialisasi Signature & History)
     */
    static async create(userId, pinHash = null) {
        const connection = await pool.getConnection();
        try {
            await connection.beginTransaction();

            const initialData = {
                user_id: userId,
                balance: "0.00",
                locked_balance: "0.00",
                status: 'active'
            };

            const signature = this.generateWalletSignature(initialData);

            await connection.execute(`
                INSERT INTO wallets 
                (user_id, balance, locked_balance, currency, pin_hash, signature, status, created_at, updated_at)
                VALUES (?, 0.00, 0.00, 'IDR', ?, ?, 'active', NOW(), NOW())
            `, [userId, pinHash, signature]);

            // Catat history awal
            await this.logStatusChange(connection, {
                userId,
                oldStatus: null,
                newStatus: 'active',
                reason: 'Initial wallet creation'
            });

            await connection.commit();
            return { ...initialData, currency: 'IDR' };
        } catch (error) {
            await connection.rollback();
            logger.error("[Wallet.create]", error);
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * ATOMIC UPDATE BALANCE (Internal/Transfer/Deposit)
     */
    static async updateWalletBalance(userId, amount, flow, connection) {
        const client = connection || pool;
        const operator = flow === 'in' ? '+' : '-';

        try {
            // Lock Row
            const [wallets] = await client.execute(
                `SELECT * FROM wallets WHERE user_id = ? FOR UPDATE`, [userId]
            );

            if (wallets.length === 0) throw new Error('USER_NOT_FOUND');
            const wallet = wallets[0];
            if (wallet.status !== 'active') throw new Error(`WALLET_${wallet.status.toUpperCase()}`);

            const numAmount = parseFloat(amount);
            const oldBalance = parseFloat(wallet.balance);
            const newBalance = flow === 'in' ? oldBalance + numAmount : oldBalance - numAmount;

            if (newBalance < 0) throw new Error('INSUFFICIENT_BALANCE');

            const newSignature = this.generateWalletSignature({
                user_id: userId,
                balance: newBalance,
                locked_balance: wallet.locked_balance,
                status: wallet.status
            });

            await client.execute(
                `UPDATE wallets SET balance = ?, signature = ?, updated_at = NOW() WHERE user_id = ?`,
                [newBalance, newSignature, userId]
            );

            return newBalance;
        } catch (err) {
            throw err;
        }
    }

    /**
     * ATOMIC UPDATE LOCKED BALANCE (Withdrawal Flow)
     * Mengatur perpindahan dana dari balance ke locked_balance atau sebaliknya.
     */
    static async updateLockedBalance(userId, amount, action, connection) {
        const client = connection || pool;
        try {
            const [rows] = await client.execute(`SELECT * FROM wallets WHERE user_id = ? FOR UPDATE`, [userId]);
            const wallet = rows[0];

            let newBalance = parseFloat(wallet.balance);
            let newLocked = parseFloat(wallet.locked_balance);
            const numAmount = parseFloat(amount);

            if (action === 'lock') {
                // Request Withdraw: Balance -> Locked
                if (newBalance < numAmount) throw new Error('INSUFFICIENT_BALANCE');
                newBalance -= numAmount;
                newLocked += numAmount;
            } else if (action === 'unlock_return') {
                // Reject Withdraw: Locked -> Balance
                newLocked -= numAmount;
                newBalance += numAmount;
            } else if (action === 'unlock_out') {
                // Approve Withdraw: Locked Out
                newLocked -= numAmount;
            }

            const newSignature = this.generateWalletSignature({
                user_id: userId,
                balance: newBalance,
                locked_balance: newLocked,
                status: wallet.status
            });

            await client.execute(
                `UPDATE wallets SET balance = ?, locked_balance = ?, signature = ?, updated_at = NOW() WHERE user_id = ?`,
                [newBalance, newLocked, newSignature, userId]
            );

            return { newBalance, newLocked };
        } catch (err) {
            throw err;
        }
    }

    /**
     * RECONCILIATION: Repair Saldo dari Ledger (Transactions)
     */
    static async reconcileFromLedger(userId, adminId = null) {
        const connection = await pool.getConnection();
        try {
            await connection.beginTransaction();

            // 1. Hitung total murni dari riwayat transaksi
            const [sumRows] = await connection.execute(`
                SELECT 
                    SUM(CASE WHEN flow = 'in' AND status = 'success' THEN amount ELSE 0 END) as total_in,
                    SUM(CASE WHEN flow = 'out' AND status != 'failed' THEN amount ELSE 0 END) as total_out,
                    SUM(CASE WHEN type = 'withdrawal' AND status = 'pending' THEN amount ELSE 0 END) as current_locked
                FROM transactions WHERE user_id = ?
            `, [userId]);

            const newBalance = parseFloat(sumRows[0].total_in || 0) - parseFloat(sumRows[0].total_out || 0);
            const newLocked = parseFloat(sumRows[0].current_locked || 0);

            // 2. Ambil status saat ini
            const [current] = await connection.execute("SELECT status FROM wallets WHERE user_id = ?", [userId]);
            const status = current[0].status;

            // 3. Update dengan Signature Baru
            const newSignature = this.generateWalletSignature({
                user_id: userId,
                balance: newBalance,
                locked_balance: newLocked,
                status: status
            });

            await connection.execute(`
                UPDATE wallets 
                SET balance = ?, locked_balance = ?, signature = ?, frozen_reason = NULL, updated_at = NOW() 
                WHERE user_id = ?
            `, [newBalance, newLocked, newSignature, userId]);

            await this.logStatusChange(connection, {
                userId,
                oldStatus: status,
                newStatus: status,
                reason: 'Manual Balance Reconciliation from Ledger',
                adminId
            });

            await connection.commit();
            return newBalance;
        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * Keamanan: Freeze Akun (Dengan History)
     */
    static async freeze(userId, reason, adminId = null) {
        const connection = await pool.getConnection();
        try {
            await connection.beginTransaction();

            const [rows] = await connection.execute("SELECT * FROM wallets WHERE user_id = ? FOR UPDATE", [userId]);
            if (rows.length === 0) return;
            const wallet = rows[0];

            if (wallet.status === 'frozen') return;

            const newSignature = this.generateWalletSignature({ ...wallet, status: 'frozen' });

            await connection.execute(
                `UPDATE wallets SET status = 'frozen', signature = ?, frozen_reason = ?, updated_at = NOW() WHERE user_id = ?`,
                [newSignature, reason, userId]
            );

            await this.logStatusChange(connection, {
                userId,
                oldStatus: wallet.status,
                newStatus: 'frozen',
                reason,
                adminId
            });

            await connection.commit();
            logger.warn(`[SECURITY] Wallet ${userId} FROZEN. Reason: ${reason}`);
        } catch (error) {
            await connection.rollback();
            logger.error("[Wallet.freeze]", error);
        } finally {
            connection.release();
        }
    }

    /**
     * Helper: Mencatat Perubahan Status
     */
    static async logStatusChange(connection, { userId, oldStatus, newStatus, reason, adminId }) {
        const sql = `
            INSERT INTO wallet_status_history 
            (user_id, old_status, new_status, reason, changed_by_admin_id) 
            VALUES (?, ?, ?, ?, ?)
        `;
        await connection.execute(sql, [userId, oldStatus, newStatus, reason, adminId || null]);
    }

    static async MatchWalletPIN(userId, pin) {
        const query = `SELECT pin_hash, status FROM wallets WHERE user_id = ?`;
        const [rows] = await db.execute(query, [userId]);
        if (rows.length === 0 || rows[0].status !== 'active') return false;
        return rows[0].pin_hash ? await bcrypt.compare(pin, rows[0].pin_hash) : null;
    }

    static async UpdatePIN(userId, pinHash) {
        await db.execute(`UPDATE wallets SET pin_hash = ?, updated_at = NOW() WHERE user_id = ?`, [pinHash, userId]);
        return true;
    }
}

module.exports = Wallet;