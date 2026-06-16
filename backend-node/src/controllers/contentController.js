/**
 * file: backend-node/src/controllers/contentController.js
  * created by : yassuki
 * created date: 2025-12-11
 * description: Controller Content dengan validasi 'undefined' value.
 */

const ContentModel = require('../models/contentModel');
const logger = require('../utils/logger');

class ContentController {

    // ... (Method getBanners, getNews, getNewsDetail BIARKAN SAMA) ...
    // Copy-paste saja bagian GET dari kode sebelumnya, atau biarkan jika sudah ada.

    static async getBanners(req, res) {
        try {
            const banners = await ContentModel.getActiveBanners();
            res.status(200).json({
                status: 'success',
                data: banners
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    static async getNews(req, res) {
        try {
            const {
                limit,
                page
            } = req.query;

            // 1. Parsing dengan validasi NaN
            let l = parseInt(limit);
            let p = parseInt(page);

            // 2. Fallback jika hasil parsing adalah NaN atau <= 0
            if (isNaN(l) || l < 1) l = 10;
            if (isNaN(p) || p < 1) p = 1;

            // 3. Hitung offset
            const offset = (p - 1) * l;

            const news = await ContentModel.getNewsList(l, offset);
            res.status(200).json({
                status: 'success',
                data: news
            });
        } catch (error) {
            logger.error("[GET NEWS ERROR]", error);
            res.status(500).json({
                message: error.message
            });
        }
    }
    static async getNewsDetail(req, res) {
        try {
            const {
                id
            } = req.params;
            const news = await ContentModel.getNewsDetail(id);

            if (!news) return res.status(404).json({
                message: 'Berita tidak ditemukan'
            });

            res.status(200).json({
                status: 'success',
                data: news
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    // --- ADMIN API (FIXED) ---

    static async createBanner(req, res) {
        try {
            if (!req.file) return res.status(400).json({
                message: 'Gambar wajib diupload'
            });

            // FIX: Pastikan undefined jadi null atau default value
            const {
                title,
                target_url,
                start_date,
                expiry_date,
                sort_order
            } = req.body;

            const bannerData = {
                title: title || 'Untitled Banner', // Default title jika kosong
                target_url: target_url || null, // Null jika kosong
                sort_order: sort_order || 0,
                start_date: start_date || null,
                expiry_date: expiry_date || null,
                image_url: req.file.path
            };

            const id = await ContentModel.createBanner(bannerData);
            res.status(201).json({
                status: 'success',
                message: 'Banner dibuat',
                id
            });

        } catch (error) {
            logger.error('[CREATE BANNER ERROR]', error);
            res.status(500).json({
                message: error.message
            });
        }
    }

    static async createNews(req, res) {
        try {
            const {
                title,
                summary,
                content
            } = req.body;

            // Validasi wajib
            if (!title || !content) return res.status(400).json({
                message: 'Title dan Content wajib diisi'
            });

            // FIX: Summary boleh null jika tidak diisi
            const safeSummary = summary || null;

            const id = await ContentModel.createNews(title, safeSummary, content);
            res.status(201).json({
                status: 'success',
                message: 'Berita diterbitkan',
                id
            });

        } catch (error) {
            logger.error('[CREATE NEWS ERROR]', error);
            res.status(500).json({
                message: error.message
            });
        }
    }
}

module.exports = ContentController;