/**
 * file: backend-node/src/controllers/walletController.js
 * created by : yassuki
 * created date: 2025-12-11
 */

const Transaction = require('../models/Transaction');

const Wallet = require('../models/Wallet');
const UserModel = require('../models/userModel');
const WalletService = require('../services/walletService');
const bcrypt = require('bcrypt');
const logger = require('../utils/logger');

/**
 * 1. GET TRANSACTION HISTORY
 */
exports.getTransactionHistory = async (req, res) => {
    try {
        const userId = req.user.id;
        let { year, month } = req.query;

        // Validasi input query
        if (!year || !month) {
            const now = new Date();
            year = year || now.getFullYear();
            month = month || (now.getMonth() + 1);
        }

        const transactions = await Transaction.findByUserId(userId, month, year);

        return res.json({
            status: 'success',
            data: transactions
        });
    } catch (error) {
        logger.error(`[HISTORY_ERR] User ${req.user.id}: ${error.message}`);
        return res.status(500).json({
            status: 'error',
            message: "Gagal mengambil riwayat transaksi"
        });
    }
};

/**
 * 2. UPDATE/SET WALLET PIN
 */
exports.updateWalletPin = async (req, res) => {
    try {
        const userId = req.user.id;
        const { pin, old_pin, ispinset } = req.body;

        if (!pin || pin.length !== 6 || isNaN(pin)) {
            return res.status(400).json({
                status: false,
                message: 'PIN harus terdiri dari 6 angka'
            });
        }

        const existingWallet = await Wallet.findByUserId(userId);

        // Keamanan: Cek PIN lama jika dompet sudah memiliki PIN (ispinset)
        if (ispinset === "YES" || (existingWallet && existingWallet.pin_hash)) {
            if (!old_pin) {
                return res.status(400).json({ status: false, message: 'PIN lama wajib diisi' });
            }
            const isMatch = await bcrypt.compare(old_pin, existingWallet.pin_hash);
            if (!isMatch) {
                return res.status(400).json({ status: false, message: 'PIN lama tidak sesuai' });
            }
        }

        const hashedPin = await bcrypt.hash(pin, 10);

        // Update status di User Model
        await UserModel.update(userId, { is_wallet_pinset: "YES" });

        if (existingWallet) {
            await Wallet.UpdatePIN(userId, hashedPin);
        } else {
            // Jika user belum punya wallet sama sekali (fail-safe)
            await Wallet.create(userId, hashedPin);
        }

        return res.status(200).json({
            status: true,
            message: 'PIN Wallet berhasil diperbarui'
        });

    } catch (error) {
        logger.error(`[PIN_UPDATE_ERR] User ${req.user.id}: ${error.message}`);
        return res.status(500).json({
            status: false,
            message: 'Gagal memperbarui PIN',
            error: error.message
        });
    }
};

/**
 * 3. INITIATE DEPOSIT (Digabung dari initiateDeposit & deposit)
 */
exports.initiateDeposit = async (req, res) => {
    try {
        const userId = req.user.id;
        const { amount, payment_method } = req.body;

        if (!amount || isNaN(amount) || amount < 10000) {
            return res.status(400).json({
                status: false,
                message: 'Minimum deposit adalah Rp 10.000'
            });
        }

        const depositReq = await WalletService.initiateDeposit(
            userId, 
            amount, 
            payment_method || 'VA_AUTOMATIC'
        );

        return res.status(201).json({
            status: true,
            message: 'Permintaan deposit berhasil dibuat',
            data: {
                kode_transaksi: depositReq.kode_transaksi,
                amount: amount,
                payment_method: payment_method || 'VA_AUTOMATIC',
                status: 'PENDING'
            }
        });

    } catch (error) {
        logger.error(`[DEPOSIT_INIT_ERR] User ${req.user.id}: ${error.message}`);
        return res.status(500).json({
            status: false,
            message: 'Gagal membuat permintaan deposit',
            error: error.message
        });
    }
};

/**
 * 4. HANDLE PAYMENT CALLBACK (Webhook dari Payment Gateway)
 */
exports.handlePaymentCallback = async (req, res) => {
    try {
        // Keamanan: Validasi Token Callback di sini (Xendit/Midtrans Token)
        
        const { external_id, status } = req.body; 

        if (status === 'PAID' || status === 'SETTLED') {
            const success = await WalletService.finalizeDeposit(external_id);
            if (success) {
                logger.info(`[DEPOSIT_COMPLETE] TX: ${external_id} - Success`);
            }
        }

        return res.status(200).json({ status: true, message: 'OK' });
    } catch (error) {
        logger.error(`[CALLBACK_ERR]: ${error.message}`);
        return res.status(200).json({ status: false, message: 'Error processed' });
    }
};
 exports.requestWithdraw=async (req, res)=> {
    try {
        // 1. Defense-in-depth (Gaya B)
        if (!req.user?.id) return res.status(401).json({ status: false, message: 'Unauthorized' });

        const userId = req.user.id;
        const { amount, bank_account_id, pin, idempotency_key } = req.body;

        // 2. Validasi Input Lengkap
        if (!amount || !bank_account_id || !pin) {
            return res.status(400).json({ status: false, message: 'Data tidak lengkap.' });
        }

        // 3. Panggil Service dengan Idempotency (Gaya A)
        const result = await WalletService.requestWithdrawal(
            userId, amount, bank_account_id, pin, idempotency_key
        );

        return res.status(201).json({
            status: true,
            message: 'Permintaan penarikan berhasil dibuat.',
            data: { transaction_id: result.transactionId }
        });

    } catch (error) {
        logger.error(`[WITHDRAW_ERR] User ${req.user.id}: ${error.message}`);
        const msg = error.message || '';

        // 4. Mapping Error Granular untuk Frontend (Gaya B)
        if (msg.includes('KYC_REQUIRED')) {
            return res.status(403).json({ code: 'KYC_REQUIRED', message: 'Anda belum verifikasi KTP' });
        }
        if (msg.includes('KYC_PENDING')) {
            return res.status(403).json({ code: 'KYC_PENDING', message: 'KYC sedang diproses admin' });
        }
        if (msg.includes('Saldo') || msg.includes('PIN')) {
            return res.status(400).json({ status: false, message: msg });
        }

        return res.status(500).json({ status: false, message: 'Internal Server Error' });
    }
}