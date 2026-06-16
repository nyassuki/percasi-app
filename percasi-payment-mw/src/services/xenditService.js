/**
 * created by : yassuki
 * description : Wrapper Xendit API menggunakan AXIOS (Direct API Call) - FIXED expected_amount
 */

const axios = require('axios');
const mysql = require('mysql2/promise');
const dotenv = require('dotenv');

dotenv.config();

// Konfigurasi Database
const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASS,
    database: process.env.DB_NAME
};

// Konfigurasi Header Xendit
const XENDIT_AUTH = Buffer.from(process.env.XENDIT_SECRET_KEY + ':').toString('base64');

const xenditApi = axios.create({
    baseURL: 'https://api.xendit.co',
    headers: {
        'Authorization': `Basic ${XENDIT_AUTH}`,
        'Content-Type': 'application/json'
    }
});

// 1. Buat Closed VA (Nomor Tetap untuk User)
const createFixedVA = async (externalId, bankCode, name) => {
    try {
        console.log(`📡 Requesting VA ${bankCode} to Xendit API...`);
        
        // Endpoint resmi Xendit untuk Create VA
        const response = await xenditApi.post('/callback_virtual_accounts', {
            external_id: String(externalId),
            bank_code: bankCode,
            name: name,
            is_closed: false,
            // --- FIX ERROR INI: TAMBAHKAN NOMINAL HARAPAN DUMMY ---
            expected_amt: 10000, // Nominal minimal (10.000 IDR) agar Xendit menerima request
            // ------------------------------------------------------
            is_single_use: false
        });

        const resp = response.data;

        return {
            owner_id: resp.owner_id,
            external_id: resp.external_id,
            bank_code: resp.bank_code,
            account_number: resp.account_number,
            name: resp.name,
            expiration_date: resp.expiration_date
        };

    } catch (e) {
        const errorMsg = e.response?.data?.message || e.message;
        console.error(`❌ Xendit Error (${bankCode}):`, errorMsg);
        return null;
    }
};

// 2. Buat Bulk VA (Untuk BCA, BRI, Mandiri, BNI)
const SUPPORTED_BANKS = ['BCA', 'BRI', 'MANDIRI', 'BNI'];

const createBulkVA = async (userId, userName) => {
    const connection = await mysql.createConnection(dbConfig);
    const results = [];

    console.log(`🔄 Generating VA for User: ${userName} (${userId})...`);

    for (const bank of SUPPORTED_BANKS) {
        try {
            const result = await createFixedVA(`${userId}-${bank}`, bank, userName);

            if (result) {
                // Simpan ke Database
                await connection.execute(
                    `INSERT INTO user_virtual_accounts (user_id, bank_code, va_number, status, created_at)
                     VALUES (?, ?, ?, 'active', NOW())
                     ON DUPLICATE KEY UPDATE va_number = VALUES(va_number)`,
                    [userId, bank, result.account_number]
                );

                results.push({ bank, va: result.account_number, status: 'created' });
                console.log(`✅ VA ${bank} Saved: ${result.account_number}`);
            } else {
                results.push({ bank, status: 'failed', error: 'Xendit Error' });
            }

        } catch (error) {
            console.error(`❌ DB Error VA ${bank}:`, error.message);
            results.push({ bank, status: 'failed', error: error.message });
        }
    }

    await connection.end();
    return results;
};

module.exports = { createFixedVA, createBulkVA };