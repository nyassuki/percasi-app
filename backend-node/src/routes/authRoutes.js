/**
 * file: backend-node/src/routes/authRoutes.js
 * created by : yassuki
 * updated date: 2025-12-24
 * description: Routing endpoint autentikasi & Biometric.
 */

const express = require('express');
const AuthController = require('../controllers/authController');
const authMiddleware = require('../middlewares/authMiddleware');
const BiometricController = require('../controllers/BiometricController');
const router = express.Router();

// --- 1. Public Routes (Tanpa Token JWT) ---

// Standar Auth
router.post('/register', AuthController.register);
router.post('/login', AuthController.login);
router.post('/google', AuthController.googleLogin);

// 2FA Verification
router.post('/2fa/setup', authMiddleware,AuthController.setup2FA); 
router.post('/2fa/verify', AuthController.verify2FA); 
router.post('/2fa/disable', authMiddleware,AuthController.disable2FA); 

// Password Recovery
router.post('/forgot-password', AuthController.forgotPassword);
router.post('/reset-password', AuthController.resetPassword);

// BIOMETRIC LOGIN (Public: User memasukkan email untuk login)
// Endpoint ini yang dipanggil saat tombol "Login Biometric" ditekan
router.post('/biometric/login-options', BiometricController.generateLogin);
router.post('/biometric/verify-login', BiometricController.verifyLogin);


// --- 2. Protected Routes (Wajib Token JWT - authMiddleware) ---

router.post('/logout', authMiddleware, AuthController.logout);
router.post('/change-password', authMiddleware, AuthController.changePassword);

// BIOMETRIC REGISTRATION (Protected: Untuk pendaftaran awal di halaman profil)
router.post('/biometric/register-options', authMiddleware, BiometricController.generateRegistration);
router.post('/biometric/verify-registration', authMiddleware, BiometricController.verifyRegistration);

module.exports = router;