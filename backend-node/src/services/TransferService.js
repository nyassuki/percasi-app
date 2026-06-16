const redis = require('../config/redis');
const crypto = require('crypto');
const pool = require('../config/database');
const logger = require('../utils/logger');

class TransferService {
    
    // 1. Generate Token QR (Dipanggil Penerima)
    static async generateQrToken(userId) {
        // Buat random string aman
        const token = crypto.randomBytes(16).toString('hex');
        
        // Simpan di Redis: Key=Token, Value=UserID, Expire=60 detik
        await redis.set(`qr:fncc$$${token}`, userId, 'EX', 60); 
        const recipientId = await redis.get(`qr:fncc$$${token}`);
        logger.info(`[QR Maker ID] ${token}`,recipientId);

        return token;
    }

    // 2. Resolve Token (Dipanggil Pengirim saat Scan)
    static async resolveQrToken(token) {
        // Cek Redis
        const recipientId = await redis.get(`qr:fncc$$${token}`);
        if (!recipientId) return null; // Token expired/invalid
        
        // Ambil info user penerima (hanya info publik)
        const [rows] = await pool.execute(
            'SELECT id, username, avatar_url, kyc_status FROM users WHERE id = ?', 
            [recipientId]
        );
        return rows[0];
    }
}

module.exports = TransferService;