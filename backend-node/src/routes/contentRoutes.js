/**
 * file: backend-node/src/routes/contentRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Routing untuk Banner dan News.
 */

const express = require('express');
const ContentController = require('../controllers/contentController');
const adminMiddleware = require('../middlewares/adminMiddleware'); // Hanya admin yang boleh create
const bannerUpload = require('../middlewares/bannerUpload');

const router = express.Router();

// --- PUBLIC ROUTES (User App) ---
router.get('/banners', ContentController.getBanners);
router.get('/news', ContentController.getNews);
router.get('/news/:id', ContentController.getNewsDetail);

// --- ADMIN ROUTES (Protected) ---
// Upload Banner: key form-data harus "image"
router.post('/admin/banner', adminMiddleware, bannerUpload.single('image'), ContentController.createBanner);

// Create News
router.post('/admin/news', adminMiddleware, ContentController.createNews);

module.exports = router;
