const pool = require('../config/database');
const crypto = require('crypto');
const logger = require('../utils/logger');
const Security = require('../utils/security');

// ==========================================
// CONFIGURATION & CACHING LOGIC
// ==========================================
let cachedDEK = null;      // Buffer DEK asli
let cacheExpiry = 0;       // Timestamp kedaluwarsa cache
let dekPromise = null;     // Mencegah Thundering Herd (multiple DB hits)
const CACHE_TTL = 15 * 60 * 1000; // 15 Menit

class Transaction {

    /**
     * Mengambil Data Encryption Key (DEK) aktif dengan Caching & Concurrency Lock.
     * Sesuai standar PCI DSS untuk efisiensi dekripsi jutaan record.
     */
    static async getActiveDEK() {
        const now = Date.now();
        if (cachedDEK && now < cacheExpiry) return cachedDEK;
        if (dekPromise) return dekPromise;

        dekPromise = (async () => {
            try {
                const [rows] = await pool.execute(
                    "SELECT encrypted_dek FROM key_management WHERE is_active = 1 LIMIT 1"
                );
                if (rows.length === 0) throw new Error("No active DEK found in database.");

                const unwrapped = Security.unwrapDataKey(rows[0].encrypted_dek);
                cachedDEK = unwrapped;
                cacheExpiry = Date.now() + CACHE_TTL;
                return cachedDEK;
            } catch (error) {
                logger.error("[SECURITY_ERROR] Failed to fetch DEK:", error);
                throw error;
            } finally {
                dekPromise = null;
            }
        })();

        return dekPromise;
    }

    /**
     * Ambil signature terakhir milik user untuk membentuk rantai (Chaining).
     */
    static async getLatestSignature(userId, connection) {
        const db = connection || pool;
        const [rows] = await db.execute(
            'SELECT signature FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 1',
            [userId]
        );
        return rows.length > 0 ? rows[0].signature : 'GENESIS_BLOCK';
    }

    // ==========================================
    // CORE FUNCTIONS
    // ==========================================

    static async create(data) {
        const {
            user_id, type, amount, status, description, reference_id, flow, balance_snapshot, idempotency_key
        } = data;

        try {
            const dek = await Transaction.getActiveDEK();
            const prevSignature = await Transaction.getLatestSignature(user_id);
            const amountStr = amount.toString();
            const ik = idempotency_key || crypto.randomUUID();

            // 1. Envelope Encryption
            const amountEncrypted = 'env:' + Security.encryptData(amountStr, dek);

            // 2. HMAC Integrity Signature
            const signature = Security.generateSignature({
                user_id, type, amount: amountStr, flow: flow || 'in', prev_signature: prevSignature, idempotency_key: ik
            });

            const sql = `
                INSERT INTO transactions (
                    user_id, type, amount, amount_encrypted, status, description, 
                    user_va_id, flow, current_balance_snapshot, prev_signature, 
                    signature, idempotency_key, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            `;

            const [result] = await pool.execute(sql, [
                user_id, type, amount, amountEncrypted, status || 'pending',
                description || null, reference_id || null, flow || 'in',
                balance_snapshot || 0, prevSignature, signature, ik
            ]);

            // Update kode transaksi (tetap mempertahankan flow orisinal Anda)
            await Transaction.updateTransactionCode(result.insertId);

            return result.insertId;
        } catch (err) {
            logger.error('[Transaction.create]', err);
            throw err;
        }
    }

    static async findByUserId(userId, month, year) {
        try {
            const m = Number(month);
            const y = Number(year);
            if (!userId || !m || !y) throw new Error('Invalid parameter (userId, month, year)');

            const startDate = `${y}-${String(m).padStart(2, '0')}-01`;
            const endDate = m === 12 ? `${y + 1}-01-01` : `${y}-${String(m + 1).padStart(2, '0')}-01`;

            const sql = `
                SELECT * FROM transactions 
                WHERE user_id = ? AND created_at >= ? AND created_at < ? 
                ORDER BY id ASC
            `;

            const [rows] = await pool.execute(sql, [userId, startDate, endDate]);
            const dek = await Transaction.getActiveDEK();

            // Integrity Verification & Decryption
            for (const row of rows) {
                // A. Verifikasi Signature
                const expectedSig = Security.generateSignature({
                    user_id: row.user_id,
                    type: row.type,
                    amount: row.amount.toString(),
                    flow: row.flow,
                    prev_signature: row.prev_signature,
                    idempotency_key: row.idempotency_key
                });

                row.is_tampered = expectedSig !== row.signature;
                if (row.is_tampered) logger.error(`[CRITICAL] Integrity Violation: Transaction ID ${row.id}`);

                // B. Hybrid Decryption (Envelope + Legacy Support)
                if (row.amount_encrypted.startsWith('env:')) {
                    row.decrypted_amount = Security.decryptWithDEK(row.amount_encrypted.replace('env:', ''), dek);
                } else {
                    row.decrypted_amount = Security.decryptOld(row.amount_encrypted);
                }
            }

            return rows;
        } catch (err) {
            logger.error('[findByUserId]', err);
            throw err;
        }
    }

    static async updateStatus(id, status) {
        try {
            // Dalam sistem Ledger, status adalah bagian dari state yang harus diaudit.
            const sql = `UPDATE transactions SET status = ?, updated_at = NOW() WHERE id = ?`;
            const [result] = await pool.execute(sql, [status, id]);
            return result.affectedRows > 0;
        } catch (err) {
            logger.error('[updateStatus]', err);
            throw err;
        }
    }

    static async logP2PTransaction(data) {
        const { userId, kode_transaksi, relatedId, type, flow, amount, balance, note } = data;

        try {
            const dek = await Transaction.getActiveDEK();
            const prevSignature = await Transaction.getLatestSignature(userId);
            const amountStr = amount.toString();
            const ik = crypto.createHash('sha256').update(`${userId}-${kode_transaksi}`).digest('hex');

            const amountEncrypted = 'env:' + Security.encryptData(amountStr, dek);

            const signature = Security.generateSignature({
                user_id: userId, type, amount: amountStr, flow, prev_signature: prevSignature, idempotency_key: ik
            });

            const sql = `
                INSERT INTO transactions (
                    user_id, kode_transaksi, related_user_id, type, flow, amount, amount_encrypted, 
                    current_balance_snapshot, status, description, prev_signature, signature, 
                    idempotency_key, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'success', ?, ?, ?, ?, NOW())
            `;

            const [result] = await pool.execute(sql, [
                userId, kode_transaksi, relatedId || null, type, flow, amount, 
                amountEncrypted, balance, note, prevSignature, signature, ik
            ]);

            return result.insertId;
        } catch (error) {
            logger.error(`[TX_LOG_FATAL] User ${userId}: ${error.message}`);
            throw error;
        }
    }

    static async updateTransactionCode(transactionId) {
        const kode = `${transactionId}${Transaction.generateTransactionCode()}`;
        const sql = `UPDATE transactions SET kode_transaksi = ? WHERE id = ?`;
        await pool.execute(sql, [kode, transactionId]);
    }

    static generateTransactionCode() {
        // Menggunakan Secure Random (Standard PCI DSS)
        return crypto.randomBytes(8).toString('hex').toUpperCase();
    }

    static flushDEKCache() {
        cachedDEK = null;
        cacheExpiry = 0;
        logger.info("[SECURITY] DEK Cache manual flush executed.");
    }
}

module.exports = Transaction;