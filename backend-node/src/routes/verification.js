const express = require('express');
const router = express.Router();
const VerificationController = require('../controllers/VerificationController');
const authMiddleware = require('../middlewares/authMiddleware');

// Define Routes
router.post('/request/phone', authMiddleware,VerificationController.requestPhoneOtp);
router.post('/match/phone', authMiddleware,VerificationController.matchPhoneOtp);
router.post('/request/email', authMiddleware,VerificationController.requestEmailOtp);

module.exports = router;