/**
 * file: backend-node/src/models/contentModel.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Model database untuk manajemen Konten (News & Banners).
 */

const pool = require('../config/database');
const logger = require('../utils/logger');

class ContentModel {

    // --- BANNERS ---

    /**
     * Mengambil semua banner aktif untuk user (Homepage).
     * Filter: is_active = 1 DAN belum kadaluarsa.
     */
    static async getActiveBanners() {
        const query = `
      SELECT id, title, image_url, target_url 
      FROM banners 
      WHERE is_active = 1 
      AND (expiry_date IS NULL OR expiry_date > NOW())
      AND (start_date IS NULL OR start_date <= NOW())
      ORDER BY sort_order ASC
    `;
        const [rows] = await pool.execute(query);
        return rows;
    }

    /**
     * (ADMIN) Membuat banner baru.
     */
    static async createBanner(data) {
        const query = `
      INSERT INTO banners (title, image_url, target_url, sort_order, start_date, expiry_date, is_active)
      VALUES (?, ?, ?, ?, ?, ?, 1)
    `;
        const [res] = await pool.execute(query, [
            data.title, data.image_url, data.target_url, data.sort_order || 0, data.start_date, data.expiry_date
        ]);
        return res.insertId;
    }

    /**
     * (ADMIN) Menghapus banner.
     */
    static async deleteBanner(id) {
        await pool.execute('DELETE FROM banners WHERE id = ?', [id]);
    }

    // --- NEWS ---

    /**
     * Mengambil daftar berita aktif (Pagination).
     */
    static async getNewsList(limit = 10, offset = 0) {
        

        // FIX: Pastikan limit & offset dikonversi ke Number (Integer) secara eksplisit
        // MySQL Prepared Statement akan error jika ini string atau NaN
        const safeLimit = Number(limit) || 10;   // Fallback ke 10 jika error/NaN
        const safeOffset = Number(offset) || 0;  // Fallback ke 0 jika error/NaN
        const query = `SELECT id, title,news_image_url, summary, created_at FROM news WHERE is_active = 1 ORDER BY created_at DESC  LIMIT ${safeLimit} OFFSET ${safeOffset}`;
        try {
            const [rows] = await pool.execute(query);
            return rows;
        } catch (err) {
            logger.error("[SQL ERROR getNewsList]", err.message);
            throw err;
        }
    }

    /**
     * Mengambil detail satu berita.
     */
    static async getNewsDetail(id) {
        const [rows] = await pool.execute(
            `SELECT * FROM news WHERE id = ? AND is_active = 1`, [id]
        );
        return rows.length > 0 ? rows[0] : null;
    }
    /**
     * Mengambil total berita.
     */
    static async getNewsCount() {
        const [rows] = await pool.execute(
            `SELECT count(id) FROM news WHERE is_active = 1`
        );
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * (ADMIN) Membuat berita baru.
     */
    static async createNews(title, summary, content) {
        const query = `
      INSERT INTO news (title,news_image_url, summary, content, is_active, created_at)
      VALUES (?, ?, ?, 1, NOW())
    `;
        const [res] = await pool.execute(query, [title, summary, content]);
        return res.insertId;
    }
}

module.exports = ContentModel;