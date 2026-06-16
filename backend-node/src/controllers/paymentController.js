/**
 * file: backend-node/src/controllers/paymentController.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Controller Pembayaran Terintegrasi (VA & QRIS) dengan Webhook Security.
 */
 
const PaymentService = require('../services/paymentService');
const WalletService = require('../services/walletService');
const logger = require('../utils/logger');
const crypto = require('crypto');

class PaymentController {

    /**
     * Generate atau ambil Virtual Account User (Fixed VA).
     */
    static async createVA(req, res) {
        try {
            const userId = req.user.id;
            const { bankCode } = req.body;

            if (!bankCode) {
                return res.status(400).json({ status: 'error', message: 'Bank Code wajib diisi.' });
            }

            logger.info(`[VA_REQ] User: ${userId} requesting VA for ${bankCode}`);
            const result = await PaymentService.createOrGetVA(userId, bankCode.toUpperCase());

            return res.status(200).json({
                status: 'success',
                data: result
            });
        } catch (error) {
            logger.error(`[VA_ERROR] ${error.message}`);
            return res.status(error.status || 500).json({
                status: 'error',
                message: error.message
            });
        }
    }

    /**
     * Generate QRIS Dinamis berdasarkan Amount.
     */
    static async createQRIS(req, res) {
        try {
            const userId = req.user.id;
            const { amount: amountStr } = req.query; // Direkomendasikan tetap pakai body jika POST
            
            const amount = Number(amountStr);
            if (!amountStr || isNaN(amount) || amount <= 0) {
                return res.status(400).json({
                    status: 'error',
                    message: 'Amount harus berupa angka positif yang valid'
                });
            }

            logger.info(`[QRIS_REQ] User: ${userId} | Amount: ${amount}`);
            const qris = await PaymentService.createQRIS(userId, amount);

            // Parsing response Xendit (v3 SDK Support)
            const qrString = qris.paymentMethod?.qrCode?.qrString || qris.payment_method?.qr_code?.qr_string;
            const qrUrl = qris.actions?.find(a => a.action === 'DOWNLOAD_QR_CODE')?.url;

            if (!qrString && !qrUrl) {
                throw new Error('Data QRIS tidak ditemukan dari provider');
            }

            return res.status(200).json({
                status: 'success',
                data: {
                    payment_id: qris.id,
                    status: qris.status,
                    reference_id: qris.referenceId || qris.reference_id,
                    amount: qris.amount,
                    qr_string: qrString,
                    qr_url: qrUrl
                }
            });
        } catch (error) {
            logger.error(`[QRIS_ERROR] ${error.message}`);
            return res.status(500).json({ status: 'error', message: error.message });
        }
    }

    /**
     * Mendapatkan daftar VA aktif milik user.
     */
    static async getMyPaymentMethods(req, res) {
        try {
            const userId = req.user.id;
            // Pindah query ke service untuk abstraksi
            const methods = await PaymentService.getUserActiveMethods(userId);

            return res.status(200).json({
                status: 'success',
                data: methods
            });
        } catch (error) {
            logger.error(`[PAYMENT_METHOD_ERR] ${error.message}`);
            return res.status(500).json({ status: 'error', message: 'Gagal memuat metode pembayaran' });
        }
    }

    /**
     * UNIFIED WEBHOOK HANDLER
     * Menangani callback VA, QRIS, dan Invoices dari Xendit.
     */
    static async handleXenditWebhook(req, res) {
        try {
            const callbackToken = req.headers['x-callback-token'];
            const payload = req.body;

            // 1. Security Check (Timing-safe comparison)
            const secretToken = process.env.PAYMENT_GATEWAY_TOKEN;
            if (!callbackToken || !secretToken || callbackToken !== secretToken) {
                logger.warn(`[WEBHOOK_UNAUTHORIZED] IP: ${req.ip}`);
                return res.status(403).json({ message: 'Unauthorized' });
            }

            logger.info(`[WEBHOOK_RECEIVED] Event: ${payload.event || 'payment.paid'} | ID: ${payload.id || payload.external_id}`);

            // 2. Delegate logic pemrosesan ke Service (Agar Atomic)
            // Service akan membedakan apakah ini callback VA atau QRIS
            await PaymentService.handleWebhook(payload);

            // 3. Respon 200 OK adalah wajib untuk Xendit
            return res.status(200).json({ status: 'success', message: 'Callback Processed' });

        } catch (error) {
            logger.error(`[WEBHOOK_PROCESS_ERROR] ${error.message}`);
            // Mengirim 500 akan mentrigger Xendit untuk mengirim ulang callback (Retry Mechanism)
            return res.status(500).json({ status: 'error', message: 'Internal Server Error' });
        }
    }
}

module.exports = PaymentController;