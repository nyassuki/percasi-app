const cron = require('node-cron');
const redis = require('../config/redis'); // Menggunakan config redis Anda
const pool = require('../config/database'); // Menggunakan pool database Anda
const logger = require('../utils/logger');

/**
 * Fungsi Utama Optimasi Database & Maintenance
 */
async function runMaintenanceAndOptimize() {
    let connection;
    try {
        logger.info("[Worker] 🛠️ Menjalankan jadwal pemeliharaan harian (00:00)...");

        // 1. SET MAINTENANCE MODE DI REDIS
        // Kita simpan dalam format JSON agar bisa menampung pesan dan waktu mulai
        const maintenanceData = {
            is_maintenance: true,
            message: "Sistem sedang dalam optimasi rutin harian. Kami akan segera kembali.",
            started_at: new Date().toISOString()
        };
        
        // Gunakan setex atau set sesuai kebutuhan, di sini kita gunakan set permanen sampai dihapus manual
        await redis.set('system:maintenance', JSON.stringify(maintenanceData));
        logger.info("[Worker] ⚠️ Mode Maintenance: AKTIF (User diblokir sementara)");

        // 2. AMBIL KONEKSI DARI POOL
        connection = await pool.getConnection();

        // 3. IDENTIFIKASI NAMA DATABASE
        const [dbInfo] = await connection.query('SELECT DATABASE() as dbName');
        const dbName = dbInfo[0].dbName;
        
        // Ambil semua daftar tabel dalam database tersebut
        const [tables] = await connection.query('SHOW TABLES');
        const tableKey = `Tables_in_${dbName}`;

        logger.info(`[Worker] Mengoptimasi ${tables.length} tabel pada database: ${dbName}`);

        // 4. PROSES OPTIMASI (DEFRAGMENTASI & INDEX)
        for (const row of tables) {
            const tableName = row[tableKey];
            
            // Filter: Jangan optimasi View (vw_) atau tabel sistem tertentu
            if (tableName.startsWith('vw_')) continue;

            const startTask = Date.now();
            logger.info(`[Worker] Mengoptimasi tabel: ${tableName}...`);
            
            // Perintah ini sangat penting untuk data 10jt+ guna merapikan storage di disk
            await connection.query(`OPTIMIZE TABLE ${tableName}`);
            
            const duration = ((Date.now() - startTask) / 1000).toFixed(2);
            logger.info(`[Worker] ✅ ${tableName} selesai (${duration} detik).`);
        }

        logger.info("[Worker] 🏁 Seluruh tabel berhasil di-optimize.");

    } catch (error) {
        logger.error(`[Worker] ❌ Terjadi kesalahan saat maintenance: ${error.message}`);
    } finally {
        // 5. MATIKAN MODE MAINTENANCE (Sistem Normal Kembali)
        await redis.del('system:maintenance');
        logger.info("[Worker] ✅ Mode Maintenance: NON-AKTIF (User bisa login kembali)");
        
        // Kembalikan koneksi ke pool agar bisa digunakan aplikasi lain
        if (connection) connection.release();
    }
}

/**
 * PENJADWALAN CRON
 * Berjalan tepat setiap jam 00:00 (WIB)
 */
 