/**
 * file: backend-node/src/routes/notificationRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Route untuk User melihat dan mengelola notifikasi mereka.
 */

const express = require('express');
const NotificationController = require('../controllers/notificationController');
const authMiddleware = require('../middlewares/authMiddleware');

const router = express.Router();

// 1. Ambil semua notifikasi (Inbox)
// Method: GET /api/notifications
router.get('/', authMiddleware, NotificationController.getMyNotifs);
router.get('/unread-count', authMiddleware, NotificationController.getUnreadCount);
// 2. Tandai notifikasi sudah dibaca
// Method: PATCH /api/notifications/:id/read
router.get('/read/:id', authMiddleware, NotificationController.markRead);

module.exports = router;
