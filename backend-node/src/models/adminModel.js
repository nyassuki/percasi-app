/**
 * file: backend-node/src/models/adminModel.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Model Database untuk tabel 'admins'.
 */

const pool = require('../config/database');
const logger = require('../utils/logger');

class AdminModel {

    /**
     * Mencari admin berdasarkan email.
     * @param {string} email 
     * @returns {Promise<object|null>} Data admin.
     */
    static async findByEmail(email) {
        const query = `SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1`;
        const [rows] = await pool.execute(query, [email]);
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * Mencari admin berdasarkan ID (untuk middleware).
     * @param {number} id 
     */
    static async findById(id) {
        const query = `SELECT id, full_name, email FROM admins WHERE id = ? AND is_active = 1`;
        const [rows] = await pool.execute(query, [id]);
        return rows.length > 0 ? rows[0] : null;
    }
}

module.exports = AdminModel;