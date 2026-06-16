// scripts/init-envelope.js
const pool = require('../config/database');
const Security = require('../utils/security');

async function init() {
    // Generate wrapped DEK menggunakan Master Key dari .env
    const wrappedDek = Security.generateNewDataKey(); 
    
    await pool.execute(
        "INSERT INTO key_management (version, encrypted_dek, is_active) VALUES ('v1', ?, 1)",
        [wrappedDek]
    );
    console.log("✅ Envelope Encryption berhasil diinisialisasi.");
    process.exit();
}
init();