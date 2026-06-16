/**
 * file: backend-node/src/routes/paymentRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Routing Payment.
 */

const express = require('express');
const PaymentController = require('../controllers/paymentController');
const authMiddleware = require('../middlewares/authMiddleware');

const router = express.Router();

// 1. Generate VA (Wajib Login)
router.post('/va', authMiddleware, PaymentController.createVA);

// 2. Webhook Xendit (PUBLIC - Jangan pasang authMiddleware user!)
//router.post('/webhook/xendit', PaymentController.xenditWebhook);
router.get('/my-methods', authMiddleware, PaymentController.getMyPaymentMethods);
router.get('/qris', authMiddleware, PaymentController.createQRIS);

module.exports = router;