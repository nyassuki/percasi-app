/**
 * file: backend-node/src/routes/userRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Routing untuk User Profile.
 */

const express = require('express');
const UserController = require('../controllers/userController');
const authMiddleware = require('../middlewares/authMiddleware');
const avatarUpload = require('../middlewares/avatarUpload');

const router = express.Router();

// Get Profile
router.get('/profile', authMiddleware, UserController.getMyProfile);
router.get('/public/:id', authMiddleware, UserController.getPublicProfile);
// Update Profile (Support Multipart Form Data untuk avatar)
// Field name untuk gambar adalah "avatar"
router.put('/profile', authMiddleware, avatarUpload.single('avatar'), UserController.updateProfile);

module.exports = router;
