// scripts/migrate-to-envelope.js
const pool = require('../config/database');
const Security = require('../utils/security');

async function startMigration() {
    const BATCH_SIZE = 1000;  // Jumlah record per tarikan
    const SLEEP_MS = 500;     // Jeda antar batch (0.5 detik)
    
    // 1. Ambil DEK aktif
    const [keys] = await pool.execute("SELECT encrypted_dek FROM key_management WHERE is_active = 1 LIMIT 1");
    const activeDEK = Security.unwrapDataKey(keys[0].encrypted_dek);

    let totalMigrated = 0;

    while (true) {
        // 2. Cari data yang BELUM menggunakan format envelope (misal format lama tidak diawali 'v1:')
        // Sesuaikan kriteria WHERE dengan format enkripsi lama Anda
        const [rows] = await pool.execute(
            `SELECT id, amount_encrypted FROM transactions 
             WHERE amount_encrypted NOT LIKE 'env:%' 
             LIMIT ${BATCH_SIZE}`
        );

        if (rows.length === 0) break;

        console.log(`Migrating batch of ${rows.length} records...`);

        for (const row of rows) {
            try {
                // 3. Dekripsi menggunakan sistem lama (Master Key .env langsung)
                const plainAmount = Security.decryptOld(row.amount_encrypted);

                // 4. Enkripsi menggunakan DEK baru (Envelope)
                // Kita beri prefix 'env:' untuk menandai data ini sudah migrasi
                const newEncrypted = 'env:' + Security.encryptData(plainAmount, activeDEK);

                // 5. Update per baris (Row-level lock, bukan table lock)
                await pool.execute(
                    "UPDATE transactions SET amount_encrypted = ? WHERE id = ?",
                    [newEncrypted, row.id]
                );
                totalMigrated++;
            } catch (err) {
                console.error(`❌ Gagal di ID ${row.id}: ${err.message}`);
            }
        }

        console.log(`Progress: ${totalMigrated} records migrated.`);
        
        // 6. Throttling: Beri nafas pada I/O Database
        await new Promise(res => setTimeout(res, SLEEP_MS));
    }

    console.log("✅ Migrasi selesai!");
    process.exit();
}
module.exports = { startMigration };
//startMigration();