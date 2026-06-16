const express = require('express');
const router = express.Router();
const walletController = require('../controllers/walletController');
const authMiddleware = require('../middlewares/authMiddleware');
const TransferController = require('../controllers/TransferController');

// ... route lain (topup/withdraw) ...

// GET Riwayat Transaksi
router.get('/history', authMiddleware, walletController.getTransactionHistory);
router.post('/pin', authMiddleware, walletController.updateWalletPin);

router.get('/qr/generate', authMiddleware, TransferController.requestQr);
router.post('/qr/scan', authMiddleware, TransferController.scanQr);
router.post('/transfer', authMiddleware, TransferController.executeTransfer);


router.post('/wallet/withdraw', authMiddleware, walletController.requestWithdraw);
router.post('/deposit', authMiddleware, walletController.initiateDeposit);

router.post('/callback/payment', walletController.handlePaymentCallback);  

module.exports = router;