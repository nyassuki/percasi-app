/**
 * file: backend-node/src/services/walletService.js
 * description: Service Keuangan Terpusat dengan Integrity Check, 
 * Envelope Encryption, dan Atomic Transactions.
 */

const pool = require('../config/database');
const Wallet = require('../models/Wallet');
const Transaction = require('../models/Transaction');
const Security = require('../utils/security');
const logger = require('../utils/logger');
const crypto = require('crypto');

class WalletService {

    /**
     * Membuat Wallet Baru (Biasanya dipanggil saat Register User)
     */
    static async createWallet(userId, pinHash = null) {
        try {
            return await Wallet.create(userId, pinHash);
        } catch (error) {
            logger.error(`[SERVICE_CREATE_WALLET_ERR] User: ${userId} | ${error.message}`);
            throw error;
        }
    }

    /**
     * Mengajukan penarikan dana (Withdrawal).
     * Memindahkan saldo ke 'locked_balance' menunggu persetujuan admin.
     */
    static async requestWithdrawal(userId, amount, bankAccountId, pin, idempotencyKey = null) {
        const connection = await pool.getConnection();
        try {
            await connection.beginTransaction();

            // 1. Cek Status KYC
            const [userRows] = await connection.execute(
                `SELECT kyc_status, kyc_rejection_reason FROM users WHERE id = ?`, [userId]
            );
            if (userRows.length === 0) throw new Error('USER_NOT_FOUND');
            if (userRows[0].kyc_status !== 'verified') throw new Error('KYC_NOT_VERIFIED');

            // 2. Ambil Wallet & Validasi PIN
            const wallet = await Wallet.findByUserId(userId, connection);
            if (!wallet || wallet.is_tampered) throw new Error('WALLET_COMPROMISED_OR_NOT_FOUND');
            if (wallet.status !== 'active') throw new Error(`WALLET_${wallet.status.toUpperCase()}`);

            const isPinValid = await Wallet.MatchWalletPIN(userId, pin);
            if (!isPinValid) throw new Error('INVALID_PIN');

            // 3. Update Saldo (Move to Locked) menggunakan method model yang atomik
            const numAmount = parseFloat(amount);
            const { newBalance } = await Wallet.updateLockedBalance(userId, numAmount, 'lock', connection);

            // 4. Catat Transaksi Pending
            const ik = idempotencyKey || crypto.randomUUID();
            const transactionId = await Transaction.create({
                user_id: userId,
                type: 'withdrawal',
                flow: 'out',
                amount: numAmount,
                status: 'pending',
                balance_snapshot: newBalance,
                description: `Withdrawal to Bank Account ID: ${bankAccountId}`,
                reference_id: bankAccountId,
                idempotency_key: ik
            }, connection);

            await connection.commit();
            return { success: true, transactionId };
        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * Memproses persetujuan atau penolakan Penarikan Dana (Withdrawal)
     */
    static async approveWithdrawal(transactionId, action, adminId) {
        const connection = await pool.getConnection();
        try {
            await connection.beginTransaction();

            const [txRows] = await connection.execute(
                `SELECT * FROM transactions WHERE id = ? AND type = 'withdrawal' AND status = 'pending' FOR UPDATE`,
                [transactionId]
            );
            if (txRows.length === 0) throw new Error('TRANSACTION_NOT_FOUND');
            const tx = txRows[0];

            // Update Locked Balance berdasarkan Action
            // unlock_out: saldo keluar permanen | unlock_return: saldo balik ke balance utama
            const lockAction = action === 'approve' ? 'unlock_out' : 'unlock_return';
            const { newBalance } = await Wallet.updateLockedBalance(tx.user_id, tx.amount, lockAction, connection);

            // Update Status Transaksi
            const newStatus = action === 'approve' ? 'success' : 'failed';
            await connection.execute(
                `UPDATE transactions SET status = ?, approved_by_admin_id = ?, current_balance_snapshot = ?, updated_at = NOW() WHERE id = ?`,
                [newStatus, adminId, newBalance, transactionId]
            );

            await connection.commit();
            return { success: true, status: newStatus };
        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * Transfer antar User (P2P Transfer)
     */
    static async executeP2PTransfer(senderId, receiverId, amount, note, pin) {
        const connection = await pool.getConnection();
        const kode_transaksi = `P2P-${Date.now()}-${crypto.randomBytes(2).toString('hex').toUpperCase()}`;
        
        try {
            await connection.beginTransaction();

            // 1. Validasi PIN Pengirim
            const isPinValid = await Wallet.MatchWalletPIN(senderId, pin);
            if (!isPinValid) throw new Error('INVALID_PIN');

            // 2. Potong Saldo Pengirim (Out)
            const senderBalance = await Wallet.updateWalletBalance(senderId, amount, 'out', connection);
            await Transaction.create({
                user_id: senderId,
                type: 'transfer_out',
                flow: 'out',
                amount,
                status: 'success',
                balance_snapshot: senderBalance,
                description: `Transfer to User ${receiverId}: ${note}`,
                kode_transaksi
            }, connection);

            // 3. Tambah Saldo Penerima (In)
            const receiverBalance = await Wallet.updateWalletBalance(receiverId, amount, 'in', connection);
            await Transaction.create({
                user_id: receiverId,
                type: 'transfer_in',
                flow: 'in',
                amount,
                status: 'success',
                balance_snapshot: receiverBalance,
                description: `Received from User ${senderId}: ${note}`,
                kode_transaksi
            }, connection);

            await connection.commit();
            return { success: true, kode_transaksi };
        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * Inisialisasi Deposit
     */
    static async initiateDeposit(userId, amount, method) {
        const kode_transaksi = `DEP-${Date.now()}-${crypto.randomBytes(3).toString('hex').toUpperCase()}`;
        
        const transactionId = await Transaction.create({
            user_id: userId,
            type: 'topup_va',
            flow: 'in',
            amount: amount,
            status: 'pending',
            description: `Deposit via ${method}`,
            kode_transaksi: kode_transaksi
        });

        return { transactionId, kode_transaksi };
    }

    /**
     * Finalisasi Deposit (Callback Payment Gateway)
     */
    static async finalizeDeposit(kode_transaksi) {
        const connection = await pool.getConnection();
        try {
            await connection.beginTransaction();

            const [txRows] = await connection.execute(
                `SELECT * FROM transactions WHERE kode_transaksi = ? AND status = 'pending' FOR UPDATE`,
                [kode_transaksi]
            );

            if (txRows.length === 0) return false; 
            const tx = txRows[0];

            const newBalance = await Wallet.updateWalletBalance(tx.user_id, tx.amount, 'in', connection);

            await connection.execute(
                `UPDATE transactions SET status = 'success', current_balance_snapshot = ?, updated_at = NOW() WHERE id = ?`,
                [newBalance, tx.id]
            );

            await connection.commit();
            return true;
        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * Perbarui Status Wallet (Freeze/Unfreeze) oleh Admin
     */
    static async updateWalletStatus(userId, newStatus, reason, adminId) {
        const connection = await pool.getConnection();
        try {
            await connection.beginTransaction();

            const wallet = await Wallet.findByUserId(userId, connection);
            if (!wallet) throw new Error('WALLET_NOT_FOUND');

            const newSignature = Wallet.generateWalletSignature({
                ...wallet,
                status: newStatus
            });

            await connection.execute(
                `UPDATE wallets SET status = ?, signature = ?, frozen_reason = ? WHERE user_id = ?`,
                [newStatus, newSignature, reason, userId]
            );

            await Wallet.logStatusChange(connection, {
                userId,
                oldStatus: wallet.status,
                newStatus,
                reason,
                adminId
            });

            await connection.commit();
            return true;
        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    }

    /**
     * REPAIR & RECONCILE WALLET
     * Menghitung ulang saldo berdasarkan mutasi mutlak di Ledger.
     */
    static async repairWalletIntegrity(userId, adminId = null) {
        try {
            const newBalance = await Wallet.reconcileFromLedger(userId, adminId);
            return { success: true, newBalance };
        } catch (error) {
            logger.error(`[REPAIR_ERR] User ${userId}: ${error.message}`);
            throw error;
        }
    }
}

module.exports = WalletService;