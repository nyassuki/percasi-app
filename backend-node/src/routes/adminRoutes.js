/**
 * file: backend-node/src/routes/adminRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Routing khusus Admin Panel.
 */

const express = require('express');
const AdminController = require('../controllers/adminController');
const adminMiddleware = require('../middlewares/adminMiddleware');
const NotificationController = require('../controllers/notificationController');
const UserController = require('../controllers/userController');

const router = express.Router();

// Public Admin Route (Login)
router.post('/login', AdminController.login);

// Protected Admin Routes (Wajib Header Authorization: Bearer <AdminToken>)
router.get('/all-users', adminMiddleware, UserController.getAllUsers);
router.post('/broadcast', adminMiddleware, AdminController.sendBroadcast);
router.post('/notify-user', adminMiddleware, AdminController.sendUserNotif);
router.post('/match-pairing', adminMiddleware, AdminController.triggerMatch);
router.post('/blast-notification', adminMiddleware,NotificationController.blastNotification);
router.post('/withdraw/process', adminMiddleware, AdminController.processWithdraw);
router.get('/audit-logs', adminMiddleware, AdminController.getAuditLogs);
module.exports = router;
