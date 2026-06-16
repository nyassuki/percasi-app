/**
 * file: backend-node/src/controllers/TransferController.js
 * created by : yassuki
 * created date: 2025-12-11
 */

const WalletService = require('../services/walletService');
const logger = require('../utils/logger');
const NotificationService = require('../services/notificationService');
const NotificationModel = require('../models/notificationModel');
const UserModel = require('../models/userModel');
const TransferService = require('../services/TransferService');


// [GET] Generate QR (Penerima)
exports.requestQr = async (req, res) => {
    try {
        const token = await TransferService.generateQrToken(req.user.id);
        res.json({
            status: 'success',
            qr_code: `fncc$$${token}`,
            expires_in: 60
        });
    } catch (err) {
        res.status(500).json({ status: 'error', message: err.message });
    }
};

// [POST] Scan QR (Pengirim)
exports.scanQr = async (req, res) => {
    try {
        const { qr_string } = req.body;
        if (!qr_string || !qr_string.startsWith('fncc$$')) {
            return res.status(400).json({ status: 'error', message: 'Format QR salah' });
        }

        const token = qr_string.split('$$')[1];
        const recipient = await TransferService.resolveQrToken(token);

        if (!recipient) {
            return res.status(400).json({ status: 'error', message: 'QR Code sudah kadaluarsa' });
        }

        if (recipient.id === req.user.id) {
            return res.status(400).json({ status: 'error', message: 'Tidak bisa transfer ke diri sendiri' });
        }

        res.json({ status: 'success', data: recipient });
    } catch (err) {
        res.status(500).json({ status: 'error', message: 'Server error' });
    }
};

// [POST] Eksekusi Transfer (P2P)
exports.executeTransfer = async (req, res) => {
    const senderId = req.user.id;
    const { recipient_id, amount, pin, note } = req.body;

    logger.info(`[TRF-INIT] Attempting P2P Transfer: ${senderId} -> ${recipient_id} | Amt: ${amount}`);

    try {
        // 1. Validasi Input
        if (!recipient_id || !amount || !pin) {
            throw new Error("Penerima, Jumlah, dan PIN wajib diisi");
        }

        if (parseFloat(amount) < 10000) {
            throw new Error("Minimal transfer Rp 10.000");
        }

        // 2. Panggil Service (Logic Tahan Banting)
        // Service ini sudah menghandle: 
        // - DB Transaction
        // - SELECT FOR UPDATE (Locking agar tidak terjadi race condition)
        // - HMAC Signature Update (Anti-Tampering)
        // - Transaction Chaining (Integrity)
        const result = await WalletService.executeP2PTransfer(
            senderId, 
            recipient_id, 
            amount, 
            note || 'Transfer P2P', 
            pin
        );

        // 3. Notifikasi & Log (Async, tidak perlu ditunggu/await jika ingin respon cepat)
        try {
            const senderData = await UserModel.findById(senderId); // Ambil nama pengirim
            const msg_title = "Transfer Masuk Berhasil";
            const msg_body = `Anda menerima Rp ${parseFloat(amount).toLocaleString('id-ID')} dari ${senderData.full_name}`;

            await NotificationService.sendToUser(recipient_id, msg_title, msg_body, 'info');
            await NotificationModel.create(recipient_id, msg_title, msg_body, 'info');
        } catch (notifyErr) {
            logger.error(`[TRF-NOTIFY-ERR] ${notifyErr.message}`);
        }

        return res.json({
            status: 'success',
            message: 'Transfer Berhasil',
            data: { kode_transaksi: result.kode_transaksi }
        });

    } catch (err) {
        logger.error(`[TRF-ERROR] ${err.message}`);
        
        // Mapping error untuk status code
        const isClientError = ['INVALID_PIN', 'INSUFFICIENT_BALANCE', 'Minimal transfer'].some(m => err.message.includes(m));
        
        return res.status(isClientError ? 400 : 500).json({
            status: 'error',
            message: err.message
        });
    }
};