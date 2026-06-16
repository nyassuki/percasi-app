/**
 * file: backend-node/src/routes/financeRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Routing untuk modul Finance & KYC.
 */

const express = require('express');
const FinanceController = require('../controllers/financeController');
const authMiddleware = require('../middlewares/authMiddleware');
const upload = require('../middlewares/uploadMiddleware');

const router = express.Router();

// 1. Upload KYC (Multipart Form Data)
// Field name di frontend/postman harus 'ktp' dan 'selfie'
router.post('/kyc', authMiddleware, upload.fields([
    { name: 'ktp', maxCount: 1 },
    { name: 'selfie', maxCount: 1 }
]), FinanceController.uploadKyc);

// 2. Withdrawal Request
router.get('/bank-accounts', authMiddleware, FinanceController.getBankAccount);
router.post('/bank-accounts', authMiddleware, FinanceController.saveBankAccount);
module.exports = router;
