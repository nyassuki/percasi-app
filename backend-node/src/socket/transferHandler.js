/**
 * file: backend-node/src/socket/transferHandler.js
 * updated by: yassuki & AI Assistant
 * description: Handler Socket.io untuk fitur Transfer via QR
 */

const TransferService = require('../services/TransferService');
const logger = require('../utils/logger');

module.exports = (io, socket) => {

    // Helper Auth
    const requireAuth = () => {
        if (!socket.user || !socket.user.id) {
            socket.emit('transfer_error', { message: 'Sesi tidak valid. Silakan login ulang.' });
            return false;
        }
        return true;
    };

    // 1. Penerima minta QR baru (Auto refresh)
    socket.on('req_transfer_qr', async () => {
        if (!requireAuth()) return;

        try {
            // Generate token unik via service (disimpan di Redis service)
            const token = await TransferService.generateReceiveToken(socket.user.id);
            
            // [FIX] Format Prefix disamakan dengan scanner
            // Format: "fncc$$<TOKEN>"
            const qrString = `fncc$$${token}`;
            
            logger.info(`[Transfer] QR Generated for User ${socket.user.id}`);

            socket.emit('res_transfer_qr', { 
                qrString, 
                expiresIn: 60 // Detik (Frontend bisa pakai ini untuk countdown refresh)
            });

        } catch (err) {
            logger.error("[Transfer QR] Error:", err);
            socket.emit('transfer_error', { message: 'Gagal membuat QR Transfer.' });
        }
    });

    // 2. Pengirim scan QR
    socket.on('scan_transfer_qr', async ({ qrString }) => {
        if (!requireAuth()) return;

        try {
            if (!qrString) return;

            // [FIX] Validasi Prefix yang sesuai (fncc$$)
            if (!qrString.startsWith('fncc$$')) {
                return socket.emit('transfer_scan_error', { message: 'Format QR Code tidak dikenali.' });
            }

            const token = qrString.split('$$')[1]; // Split berdasarkan separator $$
            
            // Resolve token via Service (Cek Redis)
            const recipient = await TransferService.resolveQrToken(token);

            if (!recipient) {
                return socket.emit('transfer_scan_error', { message: 'QR Code sudah kadaluarsa atau tidak valid.' });
            }

            // Validasi Self-Transfer
            if (recipient.id === socket.user.id) {
                return socket.emit('transfer_scan_error', { message: 'Tidak bisa transfer ke diri sendiri.' });
            }

            logger.info(`[Transfer] Scanned: ${socket.user.username} -> ${recipient.username}`);

            // Sukses! Kirim data penerima ke Scanner untuk redirect ke halaman input nominal
            // Karena scanner adalah socket yang mengirim request, pakai socket.emit aman (local server)
            socket.emit('transfer_scan_success', {
                id: recipient.id,
                username: recipient.username,
                avatar_url: recipient.avatar_url // Pastikan service mengembalikan ini
            });

        } catch (err) {
            logger.error("[Transfer Scan] Error:", err);
            socket.emit('transfer_scan_error', { message: 'Terjadi kesalahan pada server.' });
        }
    });
};