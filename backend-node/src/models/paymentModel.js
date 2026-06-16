/**
 * file: backend-node/src/models/paymentModel.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Model database untuk manajemen Payment, VA, Wallet, dan Transaksi.
 */

const pool = require('../config/database');
const logger = require('../utils/logger');

class PaymentModel {

    /**
     * Mencari VA user berdasarkan bank.
     */
    static async findVAByUserIdAndBank(userId, bankCode) {
        const query = `
      SELECT * FROM user_virtual_accounts 
      WHERE user_id = ? AND bank_code = ? AND status = 'active'
    `;
        const [rows] = await pool.execute(query, [userId, bankCode]);
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * Menyimpan data VA baru dari Xendit ke database.
     */
    static async createUserVA(userId, bankCode, vaNumber, externalId) {
        const query = `
      INSERT INTO user_virtual_accounts (user_id, bank_code, va_number, status, created_at)
      VALUES (?, ?, ?, 'active', NOW())
    `;
        // externalId dari Xendit bisa disimpan jika perlu mapping lanjutan
        await pool.execute(query, [userId, bankCode, vaNumber]);
    }

    /**
     * Menambah saldo wallet dan mencatat riwayat transaksi (ATOMIC).
     * Digunakan saat Webhook sukses diterima.
     */
    static async processTopupSuccess(userId, amount, bankCode, transactionRef) {
            const connection = await pool.getConnection();
            try {
                await connection.beginTransaction();

                // 1. Cek apakah user punya wallet? Jika belum, buat.
                const [walletCheck] = await connection.execute(
                    `SELECT user_id FROM wallets WHERE user_id = ?`, [userId]
                );
                if (walletCheck.length === 0) {
                    await connection.execute(`INSERT INTO wallets (user_id, balance) VALUES (?, 0)`, [userId]);
                }

                // 2. Update Saldo Wallet (Lock Row)
                await connection.execute(
                    `UPDATE wallets SET balance = balance + ?, updated_at = NOW() WHERE user_id = ?`, [amount, userId]
                );

                // 3. Catat Transaksi
                await connection.execute(`
        INSERT INTO transactions 
        (user_id, type, flow, amount, status, description, created_at)
        VALUES (?, 'topup_va', 'in', ?, 'success', ?, NOW())
      `, [userId, amount, `Topup via ${bankCode} VA (Ref: ${transactionRef})`]);

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
         * [BARU] Menyimpan log mentah callback Xendit.
         * @param {object} payload - Data JSON dari Xendit.
         */
    static async logCallback(payload) {
        const query = `
      INSERT INTO xendit_callback_logs 
      (external_id, transaction_status, amount, payload, created_at)
      VALUES (?, ?, ?, ?, NOW())
    `;

        // Ekstrak data penting untuk kolom pencarian (indexing)
        // Payload Xendit beda-beda, kita ambil yang umum ada
        const extId = payload.external_id || payload.id || null;
        const status = payload.status || 'UNKNOWN';
        const amt = payload.amount || 0;

        // Stringify payload agar aman masuk kolom JSON MySQL
        const payloadString = JSON.stringify(payload);

        try {
            await pool.execute(query, [extId, status, amt, payloadString]);
        } catch (error) {
            logger.error('[DB LOG ERROR] Gagal menyimpan log callback:', error.message);
            // Jangan throw error, agar flow utama (topup) tidak gagal cuma gara-gara log
        }
    }
}

module.exports = PaymentModel;