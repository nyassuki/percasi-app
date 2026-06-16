const mysql = require('mysql2/promise');
const dotenv = require('dotenv');

dotenv.config();

const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASS,
    database: process.env.DB_NAME
};

const processXenditWebhook = async (data) => {
    // Data dari Xendit: { external_id, amount, status, ... }
    
    // Validasi dasar
    if (!data.external_id || !data.amount) {
        throw new Error("Invalid Xendit Payload");
    }

    const connection = await mysql.createConnection(dbConfig);

    try {
        await connection.beginTransaction();

        // 1. Parsing Data
        // external_id di Xendit biasanya kita set sebagai User ID kita
        const userId = data.external_id; 
        const amount = data.amount;
        const paymentId = data.payment_id || `XND-${Date.now()}`; // ID Unik dari Xendit

        // 2. Cek Idempotency (Apakah payment_id ini sudah pernah masuk?)
        // Kita cek di tabel transactions apakah ada description yang berisi payment_id ini
        const [existing] = await connection.execute(
            `SELECT id FROM transactions WHERE description LIKE ?`,
            [`%${paymentId}%`]
        );

        if (existing.length > 0) {
            console.log(`⚠️ Payment ${paymentId} sudah diproses sebelumnya.`);
            await connection.rollback();
            return { status: 'already_processed' };
        }

        // 3. Tambah Saldo Wallet
        await connection.execute(
            `UPDATE wallets SET balance = balance + ? WHERE user_id = ?`,
            [amount, userId]
        );

        // 4. Catat Transaksi Baru
        // Type: 'topup_va', Flow: 'in'
        await connection.execute(
            `INSERT INTO transactions (user_id, type, flow, amount, status, description, created_at) 
             VALUES (?, 'topup_va', 'in', ?, 'success', ?, NOW())`,
            [userId, amount, `Topup Xendit Ref: ${paymentId}`]
        );

        // 5. Kirim Notifikasi (Opsional)
        await connection.execute(
            `INSERT INTO notifications (user_id, title, message, created_at) VALUES (?, 'Deposit Berhasil', ?, NOW())`,
            [userId, `Saldo Rp ${parseInt(amount).toLocaleString()} berhasil ditambahkan.`]
        );

        await connection.commit();
        console.log(`✅ Xendit Deposit: User ${userId} +Rp ${amount}`);
        return { status: 'success' };

    } catch (err) {
        await connection.rollback();
        throw err;
    } finally {
        await connection.end();
    }
};

module.exports = { processXenditWebhook };