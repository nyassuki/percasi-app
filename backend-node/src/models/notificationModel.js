/**
 * file: backend-node/src/models/notificationModel.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Model database untuk Notifikasi user.
 */

const pool = require('../config/database');
const logger = require('../utils/logger');

class NotificationModel {

    /**
     * Membuat notifikasi baru di database.
     * @param {number} userId 
     * @param {string} title 
     * @param {string} message 
     * @param {string} type - 'info', 'success', 'warning', 'error'
     * @param {string} actionUrl - Link tujuan jika diklik
     */
    static async create(userId, title, message, type = 'info', actionUrl = null) {
        const query = `
      INSERT INTO notifications (user_id, title, message, is_read, action_url, created_at)
      VALUES (?, ?, ?, 0, ?, NOW())
    `;
        await pool.execute(query, [userId, title, message, actionUrl]);
    }

    /**
     * Mengambil notifikasi user (Pagination sederhana).
     */
    static async getUserNotifications(userId, limit = 20) {
        const query = `SELECT * FROM notifications WHERE user_id = ${userId} OR user_id = 0 ORDER BY created_at DESC LIMIT ${limit}`;
        try {
            // MySQL limit butuh integer, bukan string
            const [rows] = await pool.execute(query);
            return rows;
        } catch (error) {
            logger.info(error);
        }
    }
    static async getUserNotificationCount(userId) {
         const query = `SELECT count(id) FROM notifications WHERE user_id = ${userId} OR user_id = 0`;
        try {
            // MySQL limit butuh integer, bukan string
            const [rows] = await pool.execute(query);
            return rows;
        } catch (error) {
            logger.info(error);
        }
    }
    /**
     * Tandai sudah dibaca.
     */
    static async markAsRead(notificationId, userId) {
        try {
            await pool.execute(
                `UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?`, [notificationId, userId]
            );

        } catch (err) {
            logger.info(err);
        }
    }
    static async countUnread(userId) {
        const sql = `SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0`;
        const [rows] = await pool.execute(sql, [userId]);
        return rows[0].total;
    }
     static async getUnreadCount(userId) {
        const sql = `SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0`;
        const [rows] = await pool.execute(sql, [userId]);
        return rows[0].total;
    }

}

module.exports = NotificationModel;