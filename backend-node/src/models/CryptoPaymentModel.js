const pool = require('../config/database');
const logger = require('../utils/logger');

class CryptoPaymentModel {
    /**
     * Menyimpan data deposit baru
     */
    async createPayment(data) {
        logger.info(`[CRYPTO DEPOSIT DB MODEL] Memulai simpan data deposit crypto baru untuk User ID: ${data.userId}`);
        
        const sql = `
            INSERT INTO crypto_payments 
            (user_id, address, encrypted_private_key, network, currency, amount_expected, expired_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        `;
        const params = [
            data.userId, 
            data.address, 
            data.encryptedPrivateKey, 
            data.network, 
            data.currency, 
            data.amountExpected, 
            data.expiredAt
        ];
        
        try {
            const [result] = await pool.execute(sql, params);
            logger.info(`[CRYPTO DEPOSIT DB MODEL] Berhasil membuat alamat crypto deposit. ID: ${result.insertId}, Address: ${data.address}`);
            return result.insertId;
        } catch (error) {
            logger.error(`[CRYPTO DEPOSIT DB MODEL] Gagal simpan data crypto deposit: ${error.message}`);
            throw error;
        }
    }

    /**
     * Mencari pembayaran berdasarkan alamat (digunakan oleh Block Scanner)
     */
    async findByAddress(address) {
        logger.info(`[CRYPTO DEPOSIT DB MODEL] Mencari data untuk alamat: ${address}`);
        const sql = `SELECT * FROM crypto_payments WHERE address = ? AND status = 'PENDING' LIMIT 1`;
        
        const [rows] = await pool.execute(sql, [address]);
        
        if (rows.length > 0) {
            logger.info(`[CRYPTO DEPOSIT DB MODEL] Data ditemukan untuk alamat ${address}. ID Transaksi: ${rows[0].id}`);
        } else {
            logger.info(`[CRYPTO DEPOSIT DB MODEL] Tidak ada transaksi crypto PENDING untuk alamat: ${address}`);
        }
        
        return rows[0];
    }

    /**
     * Update status saat pembayaran terdeteksi
     */
    async updateAsSuccess(id, amountReceived, txHash) {
        logger.info(`[CRYPTO DEPOSIT DB MODEL] Memperbarui status SUCCESS untuk ID: ${id}`);
        
        const sql = `
            UPDATE crypto_payments 
            SET status = 'SUCCESS', amount_received = ?, tx_hash = ?, confirmed_at = NOW() 
            WHERE id = ?
        `;
        
        try {
            const [result] = await pool.execute(sql, [amountReceived, txHash, id]);
            if (result.affectedRows > 0) {
                logger.info(`[CRYPTO DEPOSIT DB MODEL] Transaksi ID ${id} BERHASIL diperbarui ke SUCCESS. Hash: ${txHash}`);
            }
            return result.affectedRows > 0;
        } catch (error) {
            logger.error(`[CRYPTO DEPOSIT DB MODEL] Gagal update status SUCCESS untuk ID ${id}: ${error.message}`);
            throw error;
        }
    }

    /**
     * Mengambil daftar alamat pending untuk dimonitor
     */
    async getPendingAddresses(network) {
        logger.info(`[CRYPTO DEPOSIT DB MODEL] Mengambil daftar alamat crypto PENDING untuk network: ${network}`);
        const sql = `SELECT address FROM crypto_payments WHERE status = 'PENDING' AND network = ?`;
        
        const [rows] = await pool.execute(sql, [network]);
        logger.info(`[CRYPTO DEPOSIT DB MODEL] Ditemukan ${rows.length} alamat crypto pending di network ${network}`);
        
        return rows.map(r => r.address);
    }

    /**
     * Menandai transaksi yang expired (Cron Job)
     */
    async markExpiredPayments() {
        logger.info(`[CRYPTO DEPOSIT DB MODEL] Menjalankan pembersihan transaksi crypto kadaluarsa (Cleanup)...`);
        const sql = `UPDATE crypto_payments SET status = 'EXPIRED' WHERE status = 'PENDING' AND expired_at < NOW()`;
        
        const [result] = await pool.execute(sql);
        if (result.affectedRows > 0) {
            logger.info(`[CRYPTO DEPOSIT DB MODEL] Berhasil membatalkan ${result.affectedRows} transaksi crypto yang sudah kadaluarsa.`);
        }
        return result.affectedRows;
    }

    /**
     * Mengambil semua pembayaran aktif untuk monitor
     */
    async getActivePayments() {
        logger.info(`[CRYPTO DEPOSIT DB MODEL] Scanning database untuk transaksi crypto PENDING yang belum expired...`);
        const sql = `
            SELECT id, address, network, currency, amount_expected 
            FROM crypto_payments 
            WHERE status = 'PENDING' AND expired_at > NOW()
        `;
        
        const [rows] = await pool.execute(sql);
        logger.info(`[CRYPTO DEPOSIT DB MODEL] Total transaksi crypto aktif yang perlu dimonitor: ${rows.length}`);
        
        return rows;
    }
}

module.exports = new CryptoPaymentModel();