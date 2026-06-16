/**
 * file: bulkDeleteUsers.js
 * description: Menghapus jutaan user dengan Logger Detail (Speed & ETA)
 */

const mysql = require('mysql2/promise');
const logger = require('./src/utils/logger');

async function startBulkDelete() {
    const dbConfig = {
        host: 'localhost',
        user: 'root',
        password: 'sahabat',
        database: 'percasi',
    };

    let connection;
    
    // KONFIGURASI
    const START_ID = 99000;
    const END_ID = 10000128;
    const BATCH_SIZE = 10000; // Ditingkatkan ke 10rb agar lebih cepat jika server kuat
    
    try {
        connection = await mysql.createConnection(dbConfig);
        const startTime = Date.now();
        
        logger.info(`[Cleanup] 🚀 Memulai penghapusan masif dari ID ${START_ID} ke ${END_ID}`);

        let currentId = START_ID;
        const totalToDelete = END_ID - START_ID + 1;
        let totalDeleted = 0;

        while (currentId <= END_ID) {
            const batchStartTime = Date.now();
            const upperLimit = Math.min(currentId + BATCH_SIZE - 1, END_ID);
            
            // Eksekusi Delete
            const sql = `DELETE FROM users WHERE id BETWEEN ? AND ?`;
            const [result] = await connection.execute(sql, [currentId, upperLimit]);
            
            totalDeleted += result.affectedRows;
            
            // --- LOGIC LOGGER INFO ---
            const now = Date.now();
            const elapsedTotal = (now - startTime) / 1000; // detik sejak awal
            const percentage = ((totalDeleted / totalToDelete) * 100).toFixed(2);
            
            // Kalkulasi Kecepatan & ETA
            const rowsPerSecond = Math.round(totalDeleted / elapsedTotal);
            const remainingRows = totalToDelete - totalDeleted;
            const etaSeconds = rowsPerSecond > 0 ? Math.round(remainingRows / rowsPerSecond) : 0;
            const etaMinutes = (etaSeconds / 60).toFixed(1);

            // Penggunaan Memori Node.js
            const memoryUsage = (process.memoryUsage().heapUsed / 1024 / 1024).toFixed(2);

            logger.info(
                `[Progress] ${percentage}% | ` +
                `Terhapus: ${totalDeleted.toLocaleString()} | ` +
                `Speed: ${rowsPerSecond} rows/sec | ` +
                `ETA: ${etaMinutes} min | ` +
                `RAM: ${memoryUsage} MB`
            );

            // Pindah ke batch berikutnya
            currentId += BATCH_SIZE;

            // Proteksi jika server overload (opsional)
            if (rowsPerSecond > 20000) {
                await new Promise(res => setTimeout(res, 100));
            }
        }

        const finalDuration = ((Date.now() - startTime) / 1000 / 60).toFixed(2);
        logger.info(`✅ SELESAI: Total ${totalDeleted.toLocaleString()} data dihapus dalam ${finalDuration} menit.`);

    } catch (error) {
        logger.error(`❌ CRITICAL FAILED: ${error.message}`);
    } finally {
        if (connection) await connection.end();
    }
}

startBulkDelete();