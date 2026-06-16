/**
 * file: backend-node/src/middlewares/adminMiddleware.js
 * updated by : yassuki & AI Assistant
 * updated date: 2025-12-26
 * description: Middleware Admin dengan Validasi Sesi Redis (Realtime Revocation).
 */

const jwt = require('jsonwebtoken');
const redis = require('../config/redis'); // [WAJIB] Import Redis
const logger = require('../utils/logger');

const adminMiddleware = async (req, res, next) => {
    try {
        // 1. Ambil Token
        const authHeader = req.headers['authorization'];
        const token = authHeader && authHeader.split(' ')[1];

        if (!token) {
            return res.status(401).json({
                message: 'Akses ditolak. Token admin dibutuhkan.'
            });
        }

        // 2. Verifikasi Signature JWT
        const decoded = jwt.verify(token, process.env.JWT_SECRET);

        // 3. [REDIS CHECK] Validasi Sesi Aktif
        // Pastikan token ini benar-benar ada di Redis dan belum di-logout/ditimpa
        const redisKey = `auth:session:${decoded.id}`;
        const storedToken = await redis.get(redisKey);

        if (!storedToken || storedToken !== token) {
            logger.warn(`[ADMIN AUTH] Rejected User ID ${decoded.id}: Session mismatch or expired.`);
            return res.status(401).json({
                message: 'Sesi Admin telah berakhir atau login di perangkat lain.'
            });
        }

        // 4. CEK ROLE
        // Pastikan di payload JWT saat login sudah menyertakan role
        if (decoded.role !== 'admin') {
            logger.warn(`[ADMIN AUTH] Unauthorized access attempt by User ID ${decoded.id} (Role: ${decoded.role})`);
            return res.status(403).json({
                message: 'Akses terlarang! Anda bukan Admin.'
            });
        }

        // 5. Attach ke Request
        req.admin = decoded; 
        next();

    } catch (error) {
        if (error.name === 'TokenExpiredError') {
            return res.status(401).json({ message: 'Token admin kadaluarsa.' });
        }
        if (error.name === 'JsonWebTokenError') {
            return res.status(401).json({ message: 'Token tidak valid.' });
        }
        
        logger.error('[ADMIN MIDDLEWARE] Error:', error);
        return res.status(500).json({ message: 'Terjadi kesalahan server.' });
    }
};

module.exports = adminMiddleware;