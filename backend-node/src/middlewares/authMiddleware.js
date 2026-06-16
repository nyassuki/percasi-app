/**
 * file: backend-node/src/middlewares/authMiddleware.js
 * updated by : yassuki (Redis Integrated)
 * updated date: 2025-12-26
 * description: Middleware validasi JWT dengan pengecekan Sesi Redis (Single Login & Ban Check).
 */

const jwt = require('jsonwebtoken');
const redis = require('../config/redis'); // [WAJIB] Import koneksi Redis
const UserModel = require('../models/userModel'); 
const logger = require('../utils/logger');

const authMiddleware = async (req, res, next) => {
    try {
        // 1. Ambil token dari Header Authorization
        const authHeader = req.headers['authorization'];
        
        if (!authHeader) {
            return res.status(401).json({ message: 'Akses ditolak. Token tidak ditemukan.' });
        }

        const token = authHeader.split(' ')[1]; // Format: "Bearer <token>"
        
        if (!token) {
            return res.status(401).json({ message: 'Format token salah.' });
        }

        // 2. Verifikasi Tanda Tangan JWT (Stateless Check)
        // Ini memastikan token memang diterbitkan oleh server kita dan belum expired secara waktu
        const decoded = jwt.verify(token, process.env.JWT_SECRET);

        // 3. [REDIS CHECK] Validasi Sesi Aktif (Stateful Check)
        // Kita cek apakah token ini adalah token yang "diakui" oleh server saat ini
        const redisKey = `auth:session:${decoded.id}`;
        const storedToken = await redis.get(redisKey);

        // KONDISI KRITIS:
        // a. Jika storedToken NULL: Berarti sesi sudah habis di Redis atau User sudah Logout.
        // b. Jika storedToken !== token: Berarti User sudah login lagi di perangkat BARU (Token lama invalid).
        if (!storedToken || storedToken !== token) {
            logger.warn(`[AUTH] Token rejected for User ID ${decoded.id}. Reason: Session mismatch or expired.`);
            return res.status(401).json({ 
                status: false,
                code: 'SESSION_EXPIRED', // Frontend bisa baca ini untuk auto-redirect ke login
                message: 'Sesi berakhir atau akun telah login di perangkat lain.' 
            });
        }

        // 4. Ambil Data User Terbaru dari DB (Opsional tapi Direkomendasikan)
        // Penting untuk mengecek status BANNED secara realtime
        const user = await UserModel.findById(decoded.id);

        if (!user) {
            return res.status(404).json({ message: 'User tidak ditemukan.' });
        }

        // 5. Cek Status Banned
        if (user.user_status === 'BND') {
            // Hapus sesi di Redis supaya token ini langsung mati total
            await redis.del(redisKey);
            return res.status(403).json({ 
                status: false, 
                message: 'Akun Anda telah dibekukan. Hubungi support.' 
            });
        }

        // 6. Attach user ke Request Object
        req.user = user;
        next();

    } catch (error) {
        if (error.name === 'TokenExpiredError') {
            return res.status(401).json({ message: 'Token kadaluarsa.' });
        }
        if (error.name === 'JsonWebTokenError') {
            return res.status(401).json({ message: 'Token tidak valid.' });
        }
        
        logger.error('[AUTH MIDDLEWARE] Error:', error);
        return res.status(500).json({ message: 'Terjadi kesalahan pada server auth.' });
    }
};

module.exports = authMiddleware;