/**
 * file: backend-node/src/services/paymentService.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Service Xendit menggunakan AXIOS (Direct API) untuk kestabilan.
 */

const axios = require('axios');
const PaymentModel = require('../models/paymentModel');
const UserModel = require('../models/userModel');
const XenditPaymentRequest = require('./payment/xendit/XenditPaymentRequest');
 const logger = require('../utils/logger');


// Konfigurasi Dasar Axios untuk Xendit
// Auth menggunakan Basic Auth: Username = Secret Key, Password = Kosong
const xenditApi = axios.create({
    baseURL: 'https://api.xendit.co',
    auth: {
        username: process.env.XENDIT_SECRET_KEY,
        password: ''
    },
    headers: {
        'Content-Type': 'application/json'
    }
});

class PaymentService {

    /**
     * Membuat Fixed VA via Endpoint POST /callback_virtual_accounts
     */
    static async createOrGetVA(userId, bankCode) {
        // 1. Cek Database lokal dulu
        const existingVA = await PaymentModel.findVAByUserIdAndBank(userId, bankCode);
        if (existingVA) {
            return {
                isNew: false,
                bank_code: existingVA.bank_code,
                account_number: existingVA.va_number
            };
        }

        // 2. Ambil data user
        const user = await UserModel.findById(userId);
        if (!user) throw new Error(`User ID ${userId} tidak ditemukan`);

        // Format Data
        const rawName = user.full_name || user.username || 'User';
        const vaName = `PERCASI - ${rawName}`.substring(0, 50);
        const externalId = `VA_FIXED_${userId}_${bankCode}`;

        try {
            // 3. Request Langsung ke API Xendit (Tanpa SDK)
            // Doc: https://developers.xendit.co/api-reference/#create-fixed-virtual-accounts
            const response = await xenditApi.post('/callback_virtual_accounts', {
                external_id: externalId,
                bank_code: bankCode,
                name: vaName,
                is_closed: false,
                expected_amt: 200000000,
                is_single_use: false
            });

            const data = response.data; // Response asli dari Xendit

            // 4. Simpan ke Database Lokal
            await PaymentModel.createUserVA(userId, bankCode, data.account_number, externalId);

            logger.info(`[VA CREATED] ${bankCode} User ${userId}: ${data.account_number}`);

            return {
                isNew: true,
                bank_code: bankCode,
                account_number: data.account_number,
                name: data.name
            };

        } catch (error) {
            // Handle Axios Error
            const errMsg = error.response ? JSON.stringify(error.response.data) : error.message;
            logger.error(`[XENDIT API ERROR] ${bankCode} User ${userId}: ${errMsg}`);
            // Return null agar Promise.all tidak stop
            return null;
        }
    }

    /**
     * Method Background: Membuat semua VA dan QRIS
     */
    static async initializeUserPaymentMethods(userId) {
        const banks = ['BCA', 'BNI', 'MANDIRI', 'PERMATA', 'BRI', 'BSI', 'CIMB'];
        logger.info(`[PAYMENT INIT] Start User ${userId}...`);

        try {
            // 1. Siapkan Promise VA (dengan penanganan error per item)
            // Kita pasang .catch() di setiap request bank. 
            // Jadi kalau BCA sukses tapi BNI gagal, Promise.all TIDAK akan crash.
            const vaPromises = banks.map(bank =>
                this.createOrGetVA(userId, bank)
                .catch(err => {
                    logger.error(`[PAYMENT WARNING] Gagal init VA ${bank} User ${userId}:`, err.message);
                    return null; // Return null agar Promise.all tetap lanjut
                })
            );

            // 2. Siapkan Promise QRIS (dengan penanganan error sendiri)
            const qrisPromise = this.createOrGetQRIS(userId)
                .catch(err => {
                    logger.error(`[PAYMENT WARNING] Gagal init QRIS User ${userId}:`, err.message);
                    return null;
                });

            // 3. Jalankan Paralel
            // Karena kita sudah pasang .catch di atas, Promise.all ini aman dari crash satu bank
            await Promise.all([...vaPromises, qrisPromise]);

            logger.info(`[PAYMENT INIT] Selesai User ${userId}.`);

            // Return true menandakan proses selesai (walaupun ada yang error parsial)
            return true;

        } catch (error) {
            // Catch error tak terduga (misal syntax error atau variable undefined)
            logger.error(`[PAYMENT FATAL ERROR] User ${userId}:`, error);

            // Tetap return true/false agar Controller Register tidak hang/loading terus
            return false;
        }
    }

    /**
     * Membuat QRIS via Endpoint POST /qr_codes
     */
    static async createOrGetQRIS(userId) {
        const existing = await PaymentModel.findVAByUserIdAndBank(userId, 'QRIS');
        if (existing) return;

        const externalId = `QR_STATIC_${userId}`;
        // Ganti dengan URL ngrok/domain asli Anda agar valid
        const callbackUrl = 'https://percasi-api.yourdomain.com/api/payment/webhook/xendit';

        try {
            // Doc: https://developers.xendit.co/api-reference/#create-qr-code
            const response = await xenditApi.post('/qr_codes', {
                external_id: externalId,
                type: 'STATIC',
                callback_url: callbackUrl,
                amount: 10000, // Minimal amount dummy (untuk static bisa diabaikan user nanti) atau kosongkan jika API izinkan
                currency: 'IDR'
            });

            const data = response.data;

            // Simpan qr_string ke DB
            await PaymentModel.createUserVA(userId, 'QRIS', data.qr_string, externalId);
            logger.info(`[QRIS CREATED] User ${userId}`);

        } catch (error) {
            const errMsg = error.response ? JSON.stringify(error.response.data) : error.message;
            logger.error(`[XENDIT QR ERROR] ${errMsg}`);
        }
    }

    /**
     * Handle Webhook
     */
    static async handleWebhook(payload, token) {
        // 1. [BARU] LOG DULUAN (Audit Trail)
        // Simpan semua yang masuk, bahkan yang tokennya salah (untuk deteksi serangan)
        // Tapi hati-hati flooding, idealnya log yang tokennya valid saja. 
        // Di sini kita log semua untuk debug development.
        await PaymentModel.logCallback(payload);

        // 2. Verifikasi Token Xendit
        if (token !== process.env.XENDIT_CALLBACK_TOKEN) {
            console.warn('[WEBHOOK] Token Invalid. Logged but rejected.');
            throw new Error('Invalid Callback Token!');
        }

        const {
            external_id,
            amount,
            bank_code,
            id: transactionId
        } = payload;

        // ... Logic parsing external_id dan update saldo (sama seperti sebelumnya) ...
        if (!external_id) return;

        let userId = null;
        let method = '';

        if (external_id.startsWith('VA_FIXED_')) {
            const parts = external_id.split('_');
            userId = parts[2];
            method = bank_code || 'VA';
        } else if (external_id.startsWith('QR_STATIC_')) {
            const parts = external_id.split('_');
            userId = parts[2];
            method = 'QRIS';
        } else {
            return;
        }

        logger.info(`[WEBHOOK] Payment Received: User ${userId}, Amount ${amount}`);

        // Panggil model untuk update saldo
        await PaymentModel.processTopupSuccess(userId, amount, method, transactionId);

        return {
            status: 'ok'
        };
    }
    static async createQRIS(userId, amount) {
        const referenceId = `INV-QRIS-${Date.now()}`;
        const xendit = new XenditPaymentRequest(process.env.XENDIT_SECRET_KEY);


        try {
            const result = await xendit.createQris(referenceId, amount, {
                user_id: userId,
                description: "Deposit wallet"
            });

            // Cara menampilkan QR ke user:
            // 1. qr_string: Digunakan untuk generate gambar QR sendiri di frontend
            const qrString = result.paymentMethod.qrCode.qrString;
            
            // 2. qr_url: Link gambar QR yang di-host oleh Xendit (dari array actions)
            //const qrImageUrl = result.actions.find(a => a.action === 'DOWNLOAD_QR_CODE').url;

            return {
                status: 'success',
                payment_id: result.id,
                qr_data: qrString
            };
        } catch (err) {
            return { status: 'error', message: err };
        }
    }
}

module.exports = PaymentService;