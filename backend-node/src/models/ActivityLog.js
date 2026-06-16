// File: src/models/ActivityLog.js
'use strict';
const pool = require('../config/database');

class ActivityLog {

    /**
     * Membuat log aktivitas baru
     * @param {Object} data - { user_id, action_type, description, ip_address, user_agent }
     */
    static async create(data) {
        const { user_id, action_type, description, ip_address, user_agent } = data;
        
        const query = `
            INSERT INTO activity_logs 
            (user_id, action_type, description, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        `;

        try {
            const [result] = await pool.execute(query, [
                user_id, 
                action_type, 
                description, 
                ip_address, 
                user_agent
            ]);
            return result.insertId;
        } catch (error) {
            throw error;
        }
    }

    /**
     * Mengambil log berdasarkan User ID
     * @param {number} userId 
     * @param {number} limit 
     */
    static async findByUserId(userId, limit = 20) {
        const query = `
            SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ${Number(limit)}
        `;

        try {
            // Note: Untuk LIMIT di mysql2 terkadang perlu interpolasi langsung atau casting number
            const [rows] = await pool.execute(query, [userId]);
            return rows;
        } catch (error) {
            throw error;
        }
    }

    /**
     * Mengambil semua log (Untuk Admin)
     * Opsional: Join dengan tabel users untuk dapat nama user
     */
    static async findAll(limit = 50) {
        const query = `
            SELECT 
                l.*, 
                u.username, 
                u.email 
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC 
            LIMIT ${Number(limit)}
        `;

        try {
            const [rows] = await pool.execute(query);
            return rows;
        } catch (error) {
            throw error;
        }
    }
}

module.exports = ActivityLog;