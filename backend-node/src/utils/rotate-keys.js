const pool = require('../config/database');
const Security = require('./security');
const logger = require('./logger');

async function rotateTransactionKeys() {
    const connection = await pool.getConnection();
    try {
        // Ambil semua transaksi yang versi kuncinya bukan versi terbaru
        const currentVersion = process.env.CURRENT_KEY_VERSION;
        const [rows] = await connection.execute(
            `SELECT id, amount, amount_encrypted FROM transactions 
             WHERE amount_encrypted NOT LIKE ?`, [`${currentVersion}:%`]
        );

        logger.info(`Ditemukan ${rows.length} baris untuk dirotasi ke ${currentVersion}`);

        for (const row of rows) {
            try {
                // 1. Dekripsi dengan kunci lama (otomatis terdeteksi di fungsi decrypt)
                const plainAmount = Security.decrypt(row.amount_encrypted);

                // 2. Enkripsi ulang dengan kunci baru
                const newEncryptedAmount = Security.encrypt(plainAmount);

                // 3. Update database
                await connection.execute(
                    'UPDATE transactions SET amount_encrypted = ? WHERE id = ?',
                    [newEncryptedAmount, row.id]
                );
            } catch (err) {
                logger.error(`Gagal merotasi ID ${row.id}: ${err.message}`);
            }
        }
        logger.info('Rotasi kunci selesai successfully.');
    } catch (error) {
        logger.error('Gagal menjalankan script rotasi:', error);
    } finally {
        connection.release();
    }
}

rotateTransactionKeys();