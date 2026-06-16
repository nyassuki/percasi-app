/**
 * File: backend-node/src/controllers/cryptoDepositController.js
 * Author: yassuki & AI Assistant
 * Creation Date: 2025-12-26
 * Last Modified: [Current Date]
 * Version: 1.0.0
 * 
 * Description:
 * Crypto Deposit Controller for generating cryptocurrency deposit addresses.
 * This controller handles the creation of temporary crypto deposit addresses
 * for users with secure private key encryption and time-based expiration.
 * 
 * Features:
 * - Secure crypto address generation via dedicated service
 * - Private key encryption using environment variable key
 * - Time-limited deposit addresses (15-minute expiration)
 * - Multi-currency and multi-network support (BTC, ETH, BSC, TRON)
 * - Comprehensive input validation
 * - Database persistence of deposit information
 * 
 * Supported Networks & Currencies:
 * - TRC20: USDT (Tron network)
 * - ERC20: USDT, ETH (Ethereum network)
 * - BEP20: USDT, BNB (Binance Smart Chain)
 * - BTC: Bitcoin (Bitcoin network)
 * 
 * Security Features:
 * - Private key encryption at rest
 * - Environment-based encryption key
 * - Input validation and sanitization
 * - Time-based address expiration
 * 
 * Dependencies:
 * - ../services/GenerateCryptoAddressService: Service for crypto address generation
 * - ../models/CryptoPaymentModel: Database operations for crypto payments
 * - Environment Variables: CRYPTO_ENCRYPTION_KEY for private key encryption
 * 
 * Database Schema Assumptions:
 * - crypto_payments: id, user_id, address, encrypted_private_key, network, currency,
 *                    amount_expected, amount_received, status, expired_at, created_at, updated_at
 * 
 * Important Security Notes:
 * - Never store unencrypted private keys in the database
 * - Use environment variables for encryption keys
 * - Implement rate limiting on deposit address creation
 * - Monitor for suspicious deposit patterns
 */

const GenerateCryptoAddressService = require('../services/GenerateCryptoAddressService');
const CryptoPaymentModel = require('../models/CryptoPaymentModel');
const logger = require('../utils/logger');

// Initialize Service with Encryption Key from Environment Variable
// Fallback to default for development (should be overridden in production)
const cryptoService = new GenerateCryptoAddressService(
    process.env.CRYPTO_ENCRYPTION_KEY || 'RAHASIA_123'
);

/**
 * CryptoDepositController Class
 * 
 * Handles cryptocurrency deposit address creation and management.
 * Implements secure address generation with time-limited validity.
 * 
 * @class CryptoDepositController
 * @singleton
 */
class CryptoDepositController {
    
    /**
     * Create Crypto Deposit Address
     * 
     * Generates a new cryptocurrency deposit address for user deposits.
     * The address is time-limited (15 minutes) and specific to the requested network.
     * 
     * @async
     * @method createDeposit
     * @param {Object} req - Express request object
     * @param {Object} req.body - Request body parameters
     * @param {number|string} req.body.userId - User ID making the deposit
     * @param {number} req.body.amount - Deposit amount in USD (minimum: 10 USD)
     * @param {string} req.body.currency - Cryptocurrency type (USDT, BTC, ETH, BNB)
     * @param {string} req.body.network - Blockchain network (TRC20, ERC20, BEP20, BTC)
     * @param {Object} res - Express response object
     * 
     * @returns {Promise<void>} JSON response with deposit address information
     * 
     * @throws {400} Bad Request - Invalid input parameters or validation failure
     * @throws {500} Internal Server Error - Address generation or database failure
     * 
     * Validation Rules:
     * - amount: Minimum 10 USD (or equivalent)
     * - userId: Required, must be valid user ID
     * - currency: Must be supported cryptocurrency (USDT, BTC, ETH, BNB)
     * - network: Must match currency (e.g., USDT can be TRC20/ERC20/BEP20)
     * 
     * Security Considerations:
     * - Private keys are encrypted before database storage
     * - Addresses expire after 15 minutes to prevent reuse
     * - Minimum deposit amount prevents dust attack vectors
     * 
     * Response Structure:
     * {
     *   success: true,
     *   message: "Alamat deposit berhasil dibuat",
     *   data: {
     *     id: 123,
     *     address: "0x123...abc",
     *     amount: 100,
     *     currency: "USDT",
     *     network: "ERC20",
     *     expiredAt: "2025-01-20T15:30:00.000Z",
     *     instruction: "Kirim tepat 100 USDT melalui jaringan ERC20",
     *     qrCodeUrl: "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=0x123...abc"
     *   }
     * }
     * 
     * Example Request:
     * POST /api/crypto/deposit/create
     * Content-Type: application/json
     * 
     * {
     *   "userId": 123,
     *   "amount": 100,
     *   "currency": "USDT",
     *   "network": "ERC20"
     * }
     * 
     * Example Response:
     * {
     *   "success": true,
     *   "message": "Alamat deposit berhasil dibuat",
     *   "data": {
     *     "id": 456,
     *     "address": "0x742d35Cc6634C0532925a3b844Bc9e99C4Fb1234",
     *     "amount": 100,
     *     "currency": "USDT",
     *     "network": "ERC20",
     *     "expiredAt": "2025-01-20T15:30:00.000Z",
     *     "instruction": "Kirim tepat 100 USDT melalui jaringan ERC20",
     *     "qrCodeUrl": "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=0x742d35Cc6634C0532925a3b844Bc9e99C4Fb1234"
     *   }
     * }
     */
    async createDeposit(req, res) {
        try {
            const { userId, amount, currency, network } = req.body;

            // 1. Comprehensive Input Validation
            const validationResult = this._validateDepositInput(userId, amount, currency, network);
            if (!validationResult.isValid) {
                return res.status(400).json({
                    success: false,
                    message: validationResult.message
                });
            }

            // 2. Log deposit request for monitoring
            logger.info(`[CRYPTO DEPOSIT] Request from User ${userId}: ${amount} ${currency} via ${network}`);

            // 3. Generate Crypto Address based on Network
            // Service returns { address, encryptedPrivateKey, network }
            const depositInfo = await cryptoService.create(network);

            if (!depositInfo || !depositInfo.address) {
                throw new Error("Gagal menghasilkan alamat deposit");
            }

            logger.info(`[CRYPTO DEPOSIT] Generated ${network} address: ${depositInfo.address}`);

            // 4. Determine expiration time (15 minutes from now)
            const EXPIRE_MINUTES = 15;
            const expiredAt = new Date(Date.now() + EXPIRE_MINUTES * 60000);

            // 5. Prepare payment data for database storage
            const paymentData = {
                userId: parseInt(userId),
                address: depositInfo.address,
                encryptedPrivateKey: depositInfo.encryptedPrivateKey,
                network: depositInfo.network, // TRC20, ERC20, BEP20, or BTC
                currency: currency.toUpperCase(), // Standardize to uppercase
                amountExpected: parseFloat(amount),
                expiredAt: expiredAt,
                status: 'pending' // Initial status
            };

            // 6. Save to Database using Pool (Model)
            const paymentId = await CryptoPaymentModel.createPayment(paymentData);

            if (!paymentId) {
                throw new Error("Gagal menyimpan data deposit ke database");
            }

            logger.info(`[CRYPTO DEPOSIT] Payment record created with ID: ${paymentId}`);

            // 7. Generate QR Code URL for address (optional enhancement)
            const qrCodeUrl = this._generateQRCodeUrl(depositInfo.address);

            // 8. Send Response to Frontend
            return res.status(201).json({
                success: true,
                message: "Alamat deposit berhasil dibuat",
                data: {
                    id: paymentId,
                    address: depositInfo.address,
                    amount: parseFloat(amount),
                    currency: currency.toUpperCase(),
                    network: depositInfo.network,
                    expiredAt: expiredAt.toISOString(),
                    instruction: `Kirim tepat ${amount} ${currency} melalui jaringan ${depositInfo.network}`,
                    qrCodeUrl: qrCodeUrl,
                    warnings: [
                        "Pastikan Anda mengirim melalui jaringan yang tepat",
                        "Alamat hanya valid selama 15 menit",
                        "Kirim jumlah yang tepat sesuai instruksi"
                    ]
                }
            });

        } catch (error) {
            console.error("[CRYPTO DEPOSIT ERROR]", error);

            // 9. Error Handling with Appropriate Status Codes
            let statusCode = 500;
            let errorMessage = "Terjadi kesalahan sistem saat membuat alamat deposit.";

            if (error.message.includes("validation") || error.message.includes("valid")) {
                statusCode = 400;
                errorMessage = error.message;
            } else if (error.message.includes("network") || error.message.includes("unsupported")) {
                statusCode = 400;
                errorMessage = "Jaringan atau mata uang yang diminta tidak didukung.";
            }

            return res.status(statusCode).json({
                success: false,
                message: errorMessage,
                error: process.env.NODE_ENV === 'development' ? error.message : undefined
            });
        }
    }

    /**
     * Validate Deposit Input Parameters
     * 
     * @private
     * @method _validateDepositInput
     * @param {number|string} userId - User ID
     * @param {number} amount - Deposit amount
     * @param {string} currency - Cryptocurrency type
     * @param {string} network - Blockchain network
     * @returns {Object} Validation result
     * @property {boolean} isValid - Whether input is valid
     * @property {string} [message] - Error message if invalid
     */
    _validateDepositInput(userId, amount, currency, network) {
        // 1. Check for required fields
        if (!userId || !amount || !currency || !network) {
            return {
                isValid: false,
                message: "Semua field wajib diisi (userId, amount, currency, network)."
            };
        }

        // 2. Validate User ID
        const parsedUserId = parseInt(userId);
        if (isNaN(parsedUserId) || parsedUserId <= 0) {
            return {
                isValid: false,
                message: "User ID tidak valid."
            };
        }

        // 3. Validate Amount (Minimum 10 USD equivalent)
        const parsedAmount = parseFloat(amount);
        if (isNaN(parsedAmount) || parsedAmount < 10) {
            return {
                isValid: false,
                message: "Jumlah deposit minimal adalah 10 USD."
            };
        }

        // 4. Validate Currency
        const validCurrencies = ['USDT', 'BTC', 'ETH', 'BNB'];
        const normalizedCurrency = currency.toUpperCase();
        if (!validCurrencies.includes(normalizedCurrency)) {
            return {
                isValid: false,
                message: `Mata uang tidak didukung. Gunakan: ${validCurrencies.join(', ')}`
            };
        }

        // 5. Validate Network
        const validNetworks = ['TRC20', 'ERC20', 'BEP20', 'BTC'];
        const normalizedNetwork = network.toUpperCase();
        if (!validNetworks.includes(normalizedNetwork)) {
            return {
                isValid: false,
                message: `Jaringan tidak didukung. Gunakan: ${validNetworks.join(', ')}`
            };
        }

        // 6. Validate Currency-Network Compatibility
        const compatibilityMatrix = {
            'USDT': ['TRC20', 'ERC20', 'BEP20'],
            'BTC': ['BTC'],
            'ETH': ['ERC20'],
            'BNB': ['BEP20']
        };

        if (!compatibilityMatrix[normalizedCurrency]?.includes(normalizedNetwork)) {
            return {
                isValid: false,
                message: `${normalizedCurrency} tidak didukung di jaringan ${normalizedNetwork}.`
            };
        }

        return {
            isValid: true,
            message: "Input valid"
        };
    }

    /**
     * Generate QR Code URL for Crypto Address
     * 
     * @private
     * @method _generateQRCodeUrl
     * @param {string} address - Cryptocurrency address
     * @returns {string} QR code image URL
     */
    _generateQRCodeUrl(address) {
        // Using a free QR code generation service
        // In production, consider generating QR codes server-side or using a dedicated service
        const encodedAddress = encodeURIComponent(address);
        return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodedAddress}`;
    }

    /**
     * Get Deposit Status
     * 
     * Retrieves the status of a specific deposit transaction.
     * 
     * @async
     * @method getDepositStatus
     * @param {Object} req - Express request object
     * @param {Object} req.params - Route parameters
     * @param {string} req.params.depositId - Deposit transaction ID
     * @param {Object} req.user - Authenticated user (from middleware)
     * @param {number} req.user.id - User ID for authorization
     * @param {Object} res - Express response object
     * 
     * @returns {Promise<void>} JSON response with deposit status
     * 
     * @throws {401} Unauthorized - User not authorized to view this deposit
     * @throws {404} Not Found - Deposit transaction not found
     * @throws {500} Internal Server Error - Database query failure
     */
    async getDepositStatus(req, res) {
        try {
            const { depositId } = req.params;
            const userId = req.user.id;

            // Validate deposit ID
            const parsedDepositId = parseInt(depositId);
            if (isNaN(parsedDepositId) || parsedDepositId <= 0) {
                return res.status(400).json({
                    success: false,
                    message: "ID deposit tidak valid."
                });
            }

            // Retrieve deposit information
            const deposit = await CryptoPaymentModel.getPaymentById(parsedDepositId);

            if (!deposit) {
                return res.status(404).json({
                    success: false,
                    message: "Transaksi deposit tidak ditemukan."
                });
            }

            // Verify ownership
            if (deposit.userId !== userId) {
                return res.status(401).json({
                    success: false,
                    message: "Anda tidak diizinkan mengakses transaksi ini."
                });
            }

            // Check if deposit is expired
            const now = new Date();
            const isExpired = new Date(deposit.expiredAt) < now;

            // Prepare response
            const response = {
                success: true,
                data: {
                    id: deposit.id,
                    address: deposit.address,
                    amountExpected: deposit.amountExpected,
                    amountReceived: deposit.amountReceived || 0,
                    currency: deposit.currency,
                    network: deposit.network,
                    status: deposit.status,
                    expiredAt: deposit.expiredAt,
                    isExpired: isExpired,
                    createdAt: deposit.createdAt,
                    updatedAt: deposit.updatedAt
                }
            };

            // Add warning if deposit is expired
            if (isExpired && deposit.status === 'pending') {
                response.warning = "Deposit telah kadaluarsa. Silakan buat permintaan deposit baru.";
            }

            return res.status(200).json(response);

        } catch (error) {
            console.error("[CRYPTO DEPOSIT STATUS ERROR]", error);
            return res.status(500).json({
                success: false,
                message: "Gagal mengambil status deposit."
            });
        }
    }

    /**
     * List User's Deposit History
     * 
     * Retrieves deposit history for the authenticated user.
     * 
     * @async
     * @method getDepositHistory
     * @param {Object} req - Express request object
     * @param {Object} req.query - Query parameters
     * @param {number} [req.query.page=1] - Page number for pagination
     * @param {number} [req.query.limit=10] - Items per page
     * @param {string} [req.query.status] - Filter by status (pending, completed, expired, failed)
     * @param {Object} req.user - Authenticated user (from middleware)
     * @param {number} req.user.id - User ID
     * @param {Object} res - Express response object
     * 
     * @returns {Promise<void>} JSON response with paginated deposit history
     * 
     * @throws {500} Internal Server Error - Database query failure
     */
    async getDepositHistory(req, res) {
        try {
            const userId = req.user.id;
            const { page = 1, limit = 10, status } = req.query;

            // Parse pagination parameters
            const parsedPage = Math.max(1, parseInt(page) || 1);
            const parsedLimit = Math.min(100, Math.max(1, parseInt(limit) || 10));
            const offset = (parsedPage - 1) * parsedLimit;

            // Validate status filter if provided
            const validStatuses = ['pending', 'completed', 'expired', 'failed', 'cancelled'];
            if (status && !validStatuses.includes(status.toLowerCase())) {
                return res.status(400).json({
                    success: false,
                    message: `Status tidak valid. Gunakan: ${validStatuses.join(', ')}`
                });
            }

            // Retrieve deposit history
            const { deposits, total } = await CryptoPaymentModel.getUserDeposits(
                userId, 
                parsedLimit, 
                offset, 
                status
            );

            // Calculate pagination metadata
            const totalPages = Math.ceil(total / parsedLimit);

            return res.status(200).json({
                success: true,
                data: deposits,
                pagination: {
                    page: parsedPage,
                    limit: parsedLimit,
                    totalItems: total,
                    totalPages: totalPages,
                    hasNextPage: parsedPage < totalPages,
                    hasPrevPage: parsedPage > 1
                }
            });

        } catch (error) {
            console.error("[CRYPTO DEPOSIT HISTORY ERROR]", error);
            return res.status(500).json({
                success: false,
                message: "Gagal mengambil riwayat deposit."
            });
        }
    }

    /**
     * Cancel Pending Deposit
     * 
     * Allows users to cancel a pending deposit before expiration.
     * 
     * @async
     * @method cancelDeposit
     * @param {Object} req - Express request object
     * @param {Object} req.params - Route parameters
     * @param {string} req.params.depositId - Deposit transaction ID
     * @param {Object} req.user - Authenticated user (from middleware)
     * @param {number} req.user.id - User ID for authorization
     * @param {Object} res - Express response object
     * 
     * @returns {Promise<void>} JSON response with cancellation result
     * 
     * @throws {400} Bad Request - Invalid deposit ID or cannot cancel
     * @throws {401} Unauthorized - User not authorized to cancel this deposit
     * @throws {404} Not Found - Deposit transaction not found
     * @throws {500} Internal Server Error - Database update failure
     */
    async cancelDeposit(req, res) {
        try {
            const { depositId } = req.params;
            const userId = req.user.id;

            // Validate deposit ID
            const parsedDepositId = parseInt(depositId);
            if (isNaN(parsedDepositId) || parsedDepositId <= 0) {
                return res.status(400).json({
                    success: false,
                    message: "ID deposit tidak valid."
                });
            }

            // Check if deposit exists and belongs to user
            const deposit = await CryptoPaymentModel.getPaymentById(parsedDepositId);

            if (!deposit) {
                return res.status(404).json({
                    success: false,
                    message: "Transaksi deposit tidak ditemukan."
                });
            }

            if (deposit.userId !== userId) {
                return res.status(401).json({
                    success: false,
                    message: "Anda tidak diizinkan membatalkan transaksi ini."
                });
            }

            // Check if deposit can be cancelled
            const now = new Date();
            const isExpired = new Date(deposit.expiredAt) < now;

            if (deposit.status !== 'pending') {
                return res.status(400).json({
                    success: false,
                    message: `Deposit dengan status "${deposit.status}" tidak dapat dibatalkan.`
                });
            }

            if (isExpired) {
                return res.status(400).json({
                    success: false,
                    message: "Deposit telah kadaluarsa dan tidak dapat dibatalkan."
                });
            }

            // Cancel the deposit
            const result = await CryptoPaymentModel.cancelPayment(parsedDepositId);

            if (!result) {
                throw new Error("Gagal membatalkan deposit");
            }

            logger.info(`[CRYPTO DEPOSIT] Deposit ${parsedDepositId} cancelled by user ${userId}`);

            return res.status(200).json({
                success: true,
                message: "Deposit berhasil dibatalkan.",
                data: {
                    id: parsedDepositId,
                    status: 'cancelled'
                }
            });

        } catch (error) {
            console.error("[CRYPTO DEPOSIT CANCEL ERROR]", error);
            return res.status(500).json({
                success: false,
                message: "Gagal membatalkan deposit."
            });
        }
    }
}

/**
 * Export CryptoDepositController Instance
 * 
 * Note: Using singleton pattern as the class doesn't require state.
 * For more complex scenarios, consider dependency injection.
 * 
 * @module CryptoDepositController
 * @exports cryptoDepositController
 */
module.exports = new CryptoDepositController();