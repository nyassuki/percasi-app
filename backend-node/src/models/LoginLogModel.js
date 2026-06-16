/**
 * file: backend-node/src/models/LoginLogModel.js
 */
const pool = require('../config/database');
const logger = require('../utils/logger');

class LoginLogModel {
    static async create({ user_id, input_email, ip_address, user_agent, device_type, status }) {
        const sql = `
            INSERT INTO user_login_logs 
            (user_id, input_email, ip_address, user_agent, device_type, status, login_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        `;
        
        const values = [
            user_id || null, // null jika login gagal
            input_email,
            ip_address,
            user_agent,
            device_type,
            status
        ];

        try {
            await pool.execute(sql, values);
        } catch (error) {
            logger.error("[LoginLog] Failed to insert log:", error.message);
            // Jangan throw error agar proses login user tidak terganggu hanya karena log error
        }
    }
}

module.exports = LoginLogModel;
