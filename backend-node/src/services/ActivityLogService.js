// services/ActivityLogService.js
const ActivityLog  = require('../models/ActivityLog'); // ✅ PERBAIKAN: Import dari index models
const logger = require('../utils/logger');

class ActivityLogService {
    
    /**
     * Catat aktivitas user
     * @param {number} userId - ID User
     * @param {string} actionType - Tipe aksi (LOGIN, JOIN_MATCH, dll)
     * @param {object|string} details - Detail data
     * @param {string} ip - IP Address (opsional)
     * @param {string} userAgent - Info device (opsional)
     */
    static async log(userId, actionType, details = {}, ip = null, userAgent = null) {
        try {
            const description = typeof details === 'object' ? JSON.stringify(details) : details;
            
            // Panggil Model Raw SQL
            await ActivityLog.create({
                user_id: userId,
                action_type: actionType,
                description: description,
                ip_address: ip,
                user_agent: userAgent
            });

            logger.info(`[ACTIVITY] User ${userId}: ${actionType}`);
        } catch (error) {
            logger.error("Gagal mencatat activity log:", error);
        }
    }

    // Ambil log untuk ditampilkan di frontend
    static async getLogsByUser(userId, limit = 20) {
        try {
            return await ActivityLog.findAll({
                where: { user_id: userId },
                order: [['created_at', 'DESC']],
                limit: limit
            });
        } catch (error) {
            logger.error("Gagal mengambil log user:", error.message);
            return [];
        }
    }
}

module.exports = ActivityLogService;