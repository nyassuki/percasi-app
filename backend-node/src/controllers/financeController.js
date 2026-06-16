/**
 * file: backend-node/src/controllers/financeController.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Controller untuk fitur Keuangan, KYC, dan Rekening Bank.
 */

const KycService = require('../services/kycService');
const WalletService = require('../services/walletService');
const UserBankAccount = require('../models/UserBankAccount');
const logger = require('../utils/logger');

class FinanceController {

    /**
     * Upload Dokumen KYC.
     * Method: POST /api/finance/kyc
     */
    static async uploadKyc(req, res) {
        try {
            // Validasi req.user
            if (!req.user || !req.user.id) {
                return res.status(401).json({ status: false, message: 'Unauthorized' });
            }

            const files = req.files;

            if (!files || !files.ktp || !files.selfie) {
                return res.status(400).json({
                    status: false,
                    message: 'File KTP dan Selfie wajib diunggah.'
                });
            }

            const ktpPath = files.ktp[0].path;
            const selfiePath = files.selfie[0].path;

            const result = await KycService.submitKyc(req.user.id, ktpPath, selfiePath);

            return res.status(200).json({
                status: true,
                message: 'Dokumen KYC berhasil diunggah.',
                data: result
            });

        } catch (error) {
            logger.error('[KYC Upload Error]:', error);
            return res.status(500).json({
                status: false,
                message: error.message || 'Terjadi kesalahan server saat upload KYC.'
            });
        }
    }

    /**
     * Mengambil daftar rekening bank milik user.
     * GET /api/finance/bank-accounts
     */
    static async getBankAccount(req, res) {
        try {
            if (!req.user || !req.user.id) {
                return res.status(401).json({ status: false, message: 'Unauthorized' });
            }

            const userId = req.user.id;
            
            // Menggunakan method findByUserId dari Model Raw SQL
            const account = await UserBankAccount.findByUserId(userId);
            logger.info(account);
            return res.status(200).json({
                status: true,
                message: 'Data rekening berhasil diambil.',
                data: account || null // Return null jika belum ada, bukan array kosong
            });

        } catch (error) {
            logger.error('[Get Bank Account Error]:', error);
            return res.status(500).json({
                status: false,
                message: 'Terjadi kesalahan server saat mengambil data rekening.',
                error: error.message
            });
        }
    }

    /**
     * Simpan atau Update Rekening Bank.
     * POST /api/finance/bank-accounts
     */
    static async saveBankAccount(req, res) {
        try {
            if (!req.user || !req.user.id) {
                return res.status(401).json({ status: false, message: 'Unauthorized' });
            }

            const userId = req.user.id;
            const { bank_code, account_number, account_holder_name } = req.body;

            // Validasi input
            if (!bank_code || !account_number || !account_holder_name) {
                return res.status(400).json({
                    status: false,
                    message: 'Bank code, Account number, dan Holder name wajib diisi.'
                });
            }

            // Panggil fungsi UPSERT dari Model (Create or Update logic)
            const result = await UserBankAccount.upsert(userId, {
                bank_code,
                account_number,
                account_holder_name
            });

            return res.status(200).json({
                status: true,
                message: result.action === 'created' ? 'Rekening berhasil ditambahkan.' : 'Rekening berhasil diperbarui.',
                data: result.data
            });

        } catch (error) {
            logger.error('[Save Bank Account Error]:', error);
            return res.status(500).json({
                status: false,
                message: 'Gagal menyimpan data rekening.',
                error: error.message
            });
        }
    }
}

module.exports = FinanceController;