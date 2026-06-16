/**
 * file: src/controllers/DashboardController.js
 * created by : yassuki
 * created date: 2025-12-11
 */
const Statistic = require('../models/Statistic'); // Import Model yang baru dibuat
const logger = require('../utils/logger');

exports.getGameStats = async (req, res) => {
    try {
        const userId = req.user.id; // Dari middleware auth

        // Panggil Model
        const stats = await Statistic.getUserStats(userId);
        
        // Kirim Response
        res.json({
            status: 'success',
            data: stats
        });

    } catch (err) {
        logger.error("Error getting stats:", err);
        
        // Handle error user not found khusus
        if (err.message === 'User not found') {
            return res.status(404).json({ status: 'error', message: 'User tidak ditemukan' });
        }

        res.status(500).json({ status: 'error', message: 'Gagal mengambil data statistik' });
    }
};