/**
 * file: bulkInsertUsers.js
 * description: Bulk insert 1 juta user dengan sistem chunking dan progress logger.
 */

const mysql = require('mysql2/promise');
const logger = require('./src/utils/logger');

async function startBulkInsert() {
    const dbConfig = {
        host: 'localhost',
        user: 'root',
        password: 'sahabat',
        database: 'percasi',
        // Tambahkan ini untuk performa insert masif
        multipleStatements: true 
    };

    let connection;
    
    // CONFIGURATION
    const START_INDEX = 1;
    const TOTAL_USERS = 10000000;
    const BATCH_SIZE = 100000; // Ukuran potongan per insert (optimul di 5rb - 10rb)
    const passwordHash = '$2b$10$nOf7zLantkYA6joW.IyQ2uGzlG.erEiZVLlLSVc6.dctncT9SRB/O';
    
    try {
        connection = await mysql.createConnection(dbConfig);
        logger.info(`[Database] Memulai proses sinkronisasi 1 Juta User...`);

        const sql = `
            INSERT INTO users (
                id, username, email, full_name, user_status, open_match, 
                password_hash, is_phone_verified, is_email_verified, kyc_status, 
                created_at, updated_at, is_2fa_active, is_single_login
            ) VALUES ?`;

        let currentBatch = [];
        let totalInserted = 0;
        const totalToProcess = TOTAL_USERS - START_INDEX + 1;

        for (let i = START_INDEX; i <= TOTAL_USERS; i++) {
            const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
            
            currentBatch.push([
                null,
                `player_percasi_${i}`,
                `player_${i}@live.com`,
                `Pemain Catur ${i}`,
                'ACT',
                'YES',
                passwordHash,
                'NONE',
                'NONE',
                'none',
                now,
                now,
                'NO',
                '0'
            ]);

            // Jika batch sudah mencapai ukuran maksimal atau iterasi terakhir
            if (currentBatch.length === BATCH_SIZE || i === TOTAL_USERS) {
                await connection.query(sql, [currentBatch]);
                
                totalInserted += currentBatch.length;
                
                // Kalkulasi Persentase
                const percentage = ((totalInserted / totalToProcess) * 100).toFixed(2);
                
                logger.info(`[Progress] Berhasil Insert ${totalInserted.toLocaleString()} / ${totalToProcess.toLocaleString()} user (${percentage}%)`);
                
                // Reset batch untuk membebaskan memori
                currentBatch = [];
            }
        }

        logger.info(`✅ OPERASI SELESAI: Total ${totalInserted.toLocaleString()} user baru telah masuk ke database.`);

    } catch (error) {
        logger.error(`❌ CRITICAL FAILED: ${error.message}`);
    } finally {
        if (connection) await connection.end();
    }
}

startBulkInsert();