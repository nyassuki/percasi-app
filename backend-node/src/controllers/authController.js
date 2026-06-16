/**
 * file: backend-node/src/controllers/authController.js
  * created by : yassuki
 * created date: 2025-12-11
 * description: Controller Autentikasi dengan Redis (Session Shared & 2FA Temp Data)
 */

const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const crypto = require('crypto');
const speakeasy = require('speakeasy');
const qrcode = require('qrcode'); 
const { OAuth2Client } = require('google-auth-library');
const UserModel = require('../models/userModel');
const PaymentService = require('../services/paymentService');
const ActivityLogService = require('../services/ActivityLogService');
const LoginLogModel = require('../models/LoginLogModel');
const UAParser = require('ua-parser-js');
const requestIp = require('request-ip');
const EloService = require('../services/EloRatingService');
const Wallet = require('../models/Wallet');
const logger = require('../utils/logger');
const MasterModel = require('../models/masterModel');

// [BARU] Import Redis Config
// Pastikan file ini ada di src/config/redis.js dan mengekspor instance ioredis/redis
const redis = require('../config/redis'); 

const client = new OAuth2Client(process.env.GOOGLE_CLIENT_ID);

class AuthController {

    // --- HELPER METHODS ---

    static _generateToken(user) {
        return jwt.sign({
                id: user.id,
                email: user.email,
                username: user.username
            },
            process.env.JWT_SECRET, {
                expiresIn: '1d' // Token expire dalam 1 hari
            }
        );
    }

    static getDeviceType = (userAgent) => {
        const parser = new UAParser(userAgent);
        const device = parser.getDevice();
        return device.type || 'desktop';
    };

    /**
     * [REDIS INTEGRATED] Logic Terpusat untuk handle cek 2FA
     * Menyimpan data sementara ke Redis (TTL 5 Menit) alih-alih Database MySQL
     */
    static async _handle2FACheck(user, req, res) {
        // Cek flag di database user
        if (!user.is_2fa_active || user.is_2fa_active === 'NO') {
            logger.info(`[LOGIN] User ${user.email} tidak menggunakan 2FA. Lanjut login normal.`);
            return null; 
        }

        if (user.is_2fa_active === 'YES') {
            const tempToken = crypto.randomBytes(32).toString('hex');
            
            // Struktur data yang akan disimpan di Redis
            const redisData = {
                userId: user.id,
                email: user.email,
                mode: 'verify', // Default
                secret: null // Diisi hanya jika setup mode
            };

            let responseData = {};

            if (user.two_factor_secret) {
                // --- MODE VERIFY (Login Biasa) ---
                redisData.mode = 'verify';
                
                responseData = {
                    status: true,
                    message: 'Verifikasi 2FA Diperlukan',
                    mode: 'verify',
                    data: {
                        temp_token: tempToken,
                        user_id: user.id,
                        mode: 'verify',
                    }
                };

            } else {
                // --- MODE SETUP (Kasus edge case: Flag YES tapi secret hilang/belum ada) ---
                const secret = speakeasy.generateSecret({
                    name: `MyApp (${user.email})`,
                    length: 20
                });
                const qrImage = await qrcode.toDataURL(secret.otpauth_url);

                redisData.mode = 'setup';
                redisData.secret = secret.base32; // Simpan secret sementara di RAM (Redis)

                responseData = {
                    status: true,
                    message: 'Setup 2FA Diperlukan',
                    mode: 'setup',
                    data: {
                        temp_token: tempToken,
                        qr_code: qrImage,
                        mode: 'setup',
                        manual_code: secret.base32
                    }
                };
            }

            // [REDIS ACTION] Simpan State 2FA selama 5 Menit (300 detik)
            // Key: auth:2fa_temp:{tempToken}
            await redis.set(`auth:2fa_temp:${tempToken}`, JSON.stringify(redisData), 'EX', 300);

            return res.status(200).json(responseData);
        }
        return null;
    }

    // --- ENDPOINTS ---

    // --- REGISTER ---
    static async register(req, res) {
        try {
            const { username, email, password, fullName } = req.body;

            if (!username || !email || !password) {
                return res.status(400).json({ status: false, message: 'Semua field wajib diisi.' });
            }

            const existingUserUsername = await UserModel.findByUsername(username);
            if (existingUserUsername) return res.status(409).json({ status: false, message: 'Username sudah terdaftar.' });

            const existingUser = await UserModel.findByEmail(email);
            if (existingUser) return res.status(409).json({ status: false, message: 'Email sudah terdaftar.' });

            const salt = await bcrypt.genSalt(10);
            const passwordHash = await bcrypt.hash(password, salt);

            const userId = await UserModel.createUser({
                username, email, passwordHash, fullName: fullName || username
            });

            await EloService.newUserRatings(userId); 
            await PaymentService.initializeUserPaymentMethods(userId);
            // buat wallet
            await Wallet.create(userId);
            
            return res.status(200).json({
                status: true,
                message: 'Registrasi berhasil. Silakan login.',
                data: { userId }
            });

        } catch (error) {
            logger.error('[AUTH ERROR] Register:', error);
            return res.status(500).json({ status: false, message: 'Terjadi kesalahan server.' });
        }
    }

    // --- LOGIN MANUAL ---
    static async login(req, res) {
        try {
            const userAgent = req.headers['user-agent'] || '';
            const ipAddress = requestIp.getClientIp(req);
            const deviceType = AuthController.getDeviceType(userAgent);
            const { email, password } = req.body;

            // 1. Cari User
            const user = await UserModel.findByEmail(email);
            logger.info(user);
            
            if (!user) return res.status(404).json({ status: false, message: 'Email atau password salah.' });
            if (user.user_status == "BND") return res.status(404).json({ status: false, message: 'Akun anda sedang di banned, hubungi layanan pelanggan' });
            
            if (!user.password_hash) return res.status(404).json({ status: false, message: 'Akun ini login via Google.' });

            // 2. Cek Password
            const isMatch = await bcrypt.compare(password, user.password_hash);
            if (!isMatch) return res.status(404).json({ status: false, message: 'Email atau password salah.' });

            // 3. Handle 2FA Logic (Setup or Verify) via Redis Helper
            const twoFAResponse = await AuthController._handle2FACheck(user, req, res);
            if (twoFAResponse) return twoFAResponse; // Jika butuh 2FA, return langsung

            // 4. [REDIS] Logic Single Session
            if (user.is_single_login) {
                // Cek Redis apakah ada token aktif untuk user ini
                const activeToken = await redis.get(`auth:session:${user.id}`);
                
                if (activeToken) {
                    try {
                        // Verifikasi validitas token di Redis
                        jwt.verify(activeToken, process.env.JWT_SECRET);
                        return res.status(403).json({
                            status: false, code: 'ALREADY_LOGGED_IN',
                            message: "Akun ini sedang login di perangkat lain."
                        });
                    } catch (err) { 
                        /* Token di Redis expired, lanjut login */ 
                    }
                }
            }

            // 5. Generate JWT Final
            const token = AuthController._generateToken(user);
            
            // [REDIS ACTION] Simpan Token Aktif (Expire 1 Hari / 86400 detik)
            await redis.set(`auth:session:${user.id}`, token, 'EX', 86400);
            
            // Opsional: Tetap simpan ke DB untuk log historis, tapi pengecekan utama via Redis
            // await UserModel.updateActiveToken(user.id, token);
            
            await ActivityLogService.log(user.id, 'LOGIN', 'User logged in', ipAddress, userAgent);
            await LoginLogModel.create({
                user_id: user.id, input_email: email, ip_address: ipAddress,
                user_agent: userAgent, device_type: deviceType, status: 'success'
            });

            res.status(200).json({
                status: true,
                message: 'Login berhasil.',
                token,
                user: { id: user.id, username: user.username, email: user.email, balance: user.balance }
            });

        } catch (error) {
            logger.error('[AUTH ERROR] Login:', error);
            res.status(500).json({ status: false, message: 'Server error.' });
        }
    }

    // --- GOOGLE LOGIN ---
    static async googleLogin(req, res) {
        logger.info("\n=== [DEBUG GOOGLE LOGIN: START] ===");
        try {
            const userAgent = req.headers['user-agent'] || '';
            const ipAddress = requestIp.getClientIp(req);
            const { idToken } = req.body;

            logger.info("-> [STEP 1] Memverifikasi ID Token Google...");
            // 1. Verify Google Token
            const ticket = await client.verifyIdToken({
                idToken: idToken, 
                audience: process.env.GOOGLE_CLIENT_ID,
            });
            const payload = ticket.getPayload();
            const { email, sub: googleId, name } = payload;
            
            logger.info("✅ Token Valid. Email:", email, "| Google ID:", googleId);

            // 2. Find or Create User
            logger.info("-> [STEP 2] Mencari user di database...");
            let user = await UserModel.findByEmail(email);
            
            if (user) {
                if (user.user_status == "BND") return res.status(404).json({ status: false, message: 'Akun anda sedang di banned, hubungi layanan pelanggan' });
                logger.info("-> User ditemukan (ID:", user.id, ").");
                if (!user.google_id) {
                    logger.info("-> Menghubungkan Google ID ke akun yang sudah ada...");
                    await UserModel.updateGoogleId(user.id, googleId);
                }
            } else {
                logger.info("-> User tidak ditemukan. Mendaftarkan user baru...");
                const newUsername = email.split('@')[0] + Math.floor(Math.random() * 1000);
                const userId = await UserModel.createUser({
                    username: newUsername, 
                    email, 
                    passwordHash: null, 
                    googleId, 
                    fullName: name
                });
                await PaymentService.initializeUserPaymentMethods(userId);
                // create wallet
                await Wallet.create(userId);
                user = await UserModel.findById(userId);
                logger.info("✅ User baru berhasil dibuat (ID:", userId, ")");
            }

            // 3. Handle 2FA Logic
            logger.info("-> [STEP 3] Memeriksa status 2FA...");
            logger.info(`   Status 2FA user ${user.email}:`, user.is_2fa_active);

            const twoFAResponse = await AuthController._handle2FACheck(user, req, res);
            
            if (twoFAResponse) {
                logger.info("⚠️ 2FA Aktif: Mengirim instruksi SETUP/VERIFY ke frontend.");
                return twoFAResponse; 
            }
            logger.info("-> User tidak menggunakan 2FA. Lanjut login...");

            // 4. [REDIS] Logic Single Session
            logger.info("-> [STEP 4] Mengecek sesi aktif di Redis...");
            if (user.is_single_login) {
                const activeToken = await redis.get(`auth:session:${user.id}`);
                if (activeToken) {
                    try {
                        jwt.verify(activeToken, process.env.JWT_SECRET);
                        console.warn("⚠️ Sesi aktif terdeteksi. Menolak login ganda.");
                        return res.status(403).json({
                            status: false, code: 'ALREADY_LOGGED_IN',
                            message: "Akun ini sedang login di perangkat lain."
                        });
                    } catch (err) {
                        logger.info("-> Sesi lama sudah kadaluarsa.");
                    }
                }
            }

            // 5. Success Login
            logger.info("-> [STEP 5] Finalisasi login & generate JWT...");
            const token = AuthController._generateToken(user);
            
            // [REDIS ACTION] Simpan Sesi
            await redis.set(`auth:session:${user.id}`, token, 'EX', 86400);

            await LoginLogModel.create({
                user_id: user.id, input_email: email, ip_address: ipAddress,
                user_agent: userAgent, device_type: 'google-auth', status: 'success'
            });

            const returnPayload = {
                status: true, 
                mode: 'success',
                message: 'Login Google berhasil', 
                token,
                user: { id: user.id, username: user.username, email: user.email },
                data : {
                        status: true, 
                        mode: 'success',
                        message: 'Login Google berhasil', 
                        token,
                        user: { id: user.id, username: user.username, email: user.email }
                        }
            };

            logger.info("=== [DEBUG GOOGLE LOGIN: SUCCESS] ===\n");
            logger.info('payload : ',returnPayload);
            return res.status(200).json(returnPayload);

        } catch (error) {
            logger.info(error);
            logger.error('\n❌ [DEBUG GOOGLE LOGIN: FATAL ERROR]');
            logger.error('Pesan Error:', error.message);
            logger.error('Stack Trace:', error.stack);
            if (!res.headersSent) {
                res.status(401).json({ 
                    status: false, 
                    message: 'Token Google tidak valid atau sesi berakhir.' 
                });
            }
        }
    }

    // --- VERIFY 2FA (Dimodifikasi menggunakan Redis) ---
    static async verify2FA(req, res) {
    logger.info("\n=== [DEBUG 2FA: STEP 1] Request Masuk ===");
        try {
            const { otp, temp_token } = req.body;
            const userAgent = req.headers['user-agent'] || '';
            const ipAddress = requestIp.getClientIp(req);
             logger.info(req.body);
             
            logger.info("-> Payload:", { otp, temp_token });
            logger.info("-> IP & UA:", { ipAddress, userAgent });

            if (!otp || !temp_token) {
                logger.error("❌ Error: OTP atau Temp Token kosong");
                return res.status(400).json({ status: false, message: "Data tidak lengkap." });
            }

            // 1. [REDIS] Cari Data Temporary Session
            logger.info("\n=== [DEBUG 2FA: STEP 2] Mencari Data di Redis ===");
            const redisKey = `auth:2fa_temp:${temp_token}`;
            const redisDataString = await redis.get(redisKey);

            if (!redisDataString) {
                logger.error("❌ Error: Temp Token tidak ditemukan di Redis (Expired)");
                return res.status(410).json({ status: false, message: "Sesi verifikasi kadaluarsa (Redis Key Expired)." });
            }

            const redisData = JSON.parse(redisDataString);
            logger.info("✅ Redis Data Ditemukan:", redisData);

            // Ambil user fresh dari DB untuk memastikan status terbaru
            const user = await UserModel.findById(redisData.userId);

            if (!user) {
                logger.error("❌ Error: User ID dari Redis tidak ditemukan di DB");
                return res.status(404).json({ status: false, message: "User tidak ditemukan." });
            }

            // 2. Tentukan Secret Key
            logger.info("\n=== [DEBUG 2FA: STEP 3] Menentukan Secret Key ===");
            const isSetupMode = redisData.mode === 'setup';
            
            // Jika setup mode, ambil secret dari Redis. Jika verify, ambil dari DB.
            const secretToVerify = isSetupMode ? redisData.secret : user.two_factor_secret;

            logger.info("-> Mode:", isSetupMode ? "SETUP (Aktivasi Baru)" : "VERIFY (Login Biasa)");
            logger.info("-> Secret Source:", isSetupMode ? "Redis (Temp)" : "DB (Permanent)");

            if (!secretToVerify) {
                logger.error("❌ Error: Secret key NULL/Invalid");
                return res.status(400).json({ status: false, message: "Secret key tidak ditemukan." });
            }

            // 3. Verifikasi OTP
            logger.info("\n=== [DEBUG 2FA: STEP 4] Proses Verifikasi Speakeasy ===");
            
            const cleanOtp = otp.toString().trim();
            
            const verified = speakeasy.totp.verify({
                secret: secretToVerify,
                encoding: 'base32',
                token: cleanOtp,
                window: 0 
            });

            logger.info("-> OTP Input:", cleanOtp);
            logger.info("-> HASIL VERIFIKASI:", verified ? "✅ COCOK (VALID)" : "❌ TIDAK COCOK (INVALID)");

            if (verified) {
                logger.info("\n=== [DEBUG 2FA: STEP 5] Finalisasi (Success Path) ===");
                
                if (isSetupMode) {
                    logger.info("-> Menyimpan secret permanen ke database...");
                    await UserModel.savePermanent2FASecret(user.id, secretToVerify);
                }

                // [REDIS] Cek Single Session Sebelum Finalisasi
                if (user.is_single_login) {
                    const activeToken = await redis.get(`auth:session:${user.id}`);
                    if (activeToken) {
                        try {
                            jwt.verify(activeToken, process.env.JWT_SECRET);
                            console.warn("⚠️ Warning: User sudah punya sesi aktif, menolak login ganda.");
                            return res.status(403).json({
                                status: false, code: 'ALREADY_LOGGED_IN',
                                message: "Akun ini sedang login di perangkat lain."
                            });
                        } catch (err) {
                            logger.info("-> Sesi lama sudah expired, lanjut...");
                        }
                    }
                }

                // Finalisasi: Hapus Temp Data dari Redis
                await redis.del(redisKey);
                logger.info("-> Temp data Redis dihapus.");

                // Generate Token & Simpan Sesi ke Redis
                const token = AuthController._generateToken(user);
                await redis.set(`auth:session:${user.id}`, token, 'EX', 86400); // 1 Hari

                logger.info("✅ Login 2FA Berhasil, Token dihasilkan.");
                
                await ActivityLogService.log(user.id, isSetupMode ? '2FA_SETUP_SUCCESS' : 'LOGIN_2FA', 'Success', ipAddress, userAgent);

                return res.status(200).json({
                    status: true,
                    message: isSetupMode ? "2FA Berhasil Diaktifkan & Login" : "Verifikasi Berhasil",
                    token,
                    user: { id: user.id, email: user.email, username: user.username, is_2fa_active: 'YES' }
                });

            } else {
                logger.error("❌ Verifikasi Gagal: Kode salah.");
                return res.status(400).json({ status: false, message: "Kode OTP salah." });
            }

        } catch (error) {
            logger.error('\n❌ [AUTH ERROR] FATAL ERROR di Verify 2FA:', error);
            res.status(500).json({ status: false, message: "Terjadi kesalahan server." });
        }
    }

    // ==========================================
    // LOGIC SETUP & DISABLE 2FA (SETTING MENU)
    // ==========================================

    /**
     * @description Endpoint untuk generate QR Code baru (via Settings page)
     * Menggunakan Redis untuk menyimpan secret sementara
     */
    static async setup2FA(req, res) {
        logger.info("\n=== [DEBUG SETUP 2FA: START] ===");
        
        try {
            logger.info(req.user);

            const userId = req.user.id; 
            logger.info(`-> Request dari User ID: ${userId}`);

            logger.info("-> [STEP 2] Mencari user di database...");
            const user = await UserModel.findById(userId);

            if (!user) {
                logger.warn(`❌ User ID ${userId} tidak ditemukan di database.`);
                return res.status(404).json({ status: false, message: 'User tidak ditemukan.' });
            }
            logger.info(`✅ User ditemukan: ${user.email}`);

            logger.info("-> [STEP 3] Generating Secret Key...");
            const app_settings = await MasterModel.getSettings();
            logger.info(app_settings);
            const app_name = app_settings[0]['app_name'];
            const secret = speakeasy.generateSecret({
                name: `${app_name} (${user.email})`,
                length: 20
            });
            logger.info(`-> Secret Base32 generated: ${secret.base32.substring(0, 5)}...`);

            logger.info("-> [STEP 4] Generating QR Code Image...");
            const qrImage = await qrcode.toDataURL(secret.otpauth_url);
            logger.info("✅ QR Code generated successfully.");

            logger.info("-> [STEP 5] Preparing Token & Redis Data...");
            const tempToken = crypto.randomBytes(32).toString('hex');
            
            // [REDIS ACTION] Simpan Setup Data Sementara
            const redisData = {
                userId: user.id,
                email: user.email,
                mode: 'setup',
                secret: secret.base32
            };
            
            logger.info(`-> Temp Token: ${tempToken.substring(0, 10)}...`);

            // [REDIS ACTION] Simpan ke Redis (5 Menit)
            logger.info("-> [STEP 6] Saving to Redis...");
            await redis.set(`auth:2fa_temp:${tempToken}`, JSON.stringify(redisData), 'EX', 300);
            logger.info("✅ Redis updated successfully.");

            logger.info("-> [STEP 7] Sending Response 200 OK.");
            logger.info("=== [DEBUG SETUP 2FA: FINISHED] ===\n");

            return res.status(200).json({
                status: true,
                message: 'Silakan scan QR Code untuk mengaktifkan 2FA.',
                qr_code: qrImage,
                manual_code: secret.base32,
                temp_token: tempToken
            });

        } catch (error) {
            logger.error('\n❌ [AUTH ERROR] Setup 2FA Failed!');
            logger.error(`-> Message: ${error.message}`);
            logger.error(`-> Stack: ${error.stack}`);
            
            return res.status(500).json({ status: false, message: 'Terjadi kesalahan server.' });
        }
    }

    /**
     * @description Endpoint untuk menonaktifkan 2FA
     */
    static async disable2FA(req, res) {
        try {
            const userId = req.user.id;
            
            if (UserModel.disable2FA) {
                await UserModel.disable2FA(userId);
            } else {
                // Fallback
                await UserModel.savePermanent2FASecret(userId, null); 
            }

            return res.status(200).json({ status: true, message: '2FA berhasil dinonaktifkan.' });
        } catch (error) {
            logger.error('[AUTH ERROR] Disable 2FA:', error);
            return res.status(500).json({ status: false, message: 'Terjadi kesalahan server.' });
        }
    }

    // --- LOGOUT ---
    static async logout(req, res) {
        try {
            const userId = req.user.id;
            
            // [REDIS ACTION] Hapus Sesi
            await redis.del(`auth:session:${userId}`);
            
            // Opsional: Kosongkan juga di DB jika kolom masih ada, untuk konsistensi log
            await UserModel.updateActiveToken(userId, null);

            res.status(200).json({ status: true, message: "Logout berhasil." });
        } catch (error) {
            logger.error('[AUTH ERROR] Logout:', error);
            res.status(500).json({ status: false, message: "Gagal logout." });
        }
    };

    // --- CHANGE PASSWORD ---
    static async changePassword(req, res) {
        try {
            const userId = req.user.id;
            const { oldPassword, newPassword } = req.body;

            if (!oldPassword || !newPassword) return res.status(400).json({ status: false, message: 'Password wajib diisi.' });

            const user = await UserModel.findByIdWithPassword(userId);
            if (!user || !user.password_hash) return res.status(400).json({ status: false, message: 'User invalid.' });

            const isMatch = await bcrypt.compare(oldPassword, user.password_hash);
            if (!isMatch) return res.status(404).json({ status: false, message: 'Password lama salah.' });

            const salt = await bcrypt.genSalt(10);
            const newHash = await bcrypt.hash(newPassword, salt);
            await UserModel.updatePassword(userId, newHash);

            res.status(200).json({ status: true, message: 'Password berhasil diubah.' });
        } catch (error) {
            res.status(500).json({ status: false, message: 'Server error.' });
        }
    }

    // --- FORGOT PASSWORD ---
    static async forgotPassword(req, res) {
        try {
            const { email } = req.body;
            const user = await UserModel.findByEmail(email);

            if (!user) {
                return res.status(200).json({ status: true, message: 'Jika email terdaftar, link reset akan dikirim.' });
            }

            const resetToken = crypto.randomBytes(32).toString('hex');
            // Format DATE MySQL: 'YYYY-MM-DD HH:mm:ss'
            const expireDate = new Date(Date.now() + 3600000).toISOString().slice(0, 19).replace('T', ' ');

            await UserModel.createPasswordReset(user.id, resetToken, expireDate);

            // Simulasi Email (Idealnya pakai nodemailer/service email)
            logger.info(`\n=== [EMAIL RESET] To: ${email} | Token: ${resetToken} ===\n`);

            res.status(200).json({ status: true, message: 'Jika email terdaftar, link reset akan dikirim.' });
        } catch (error) {
            logger.error(error);
            res.status(500).json({ status: false, message: 'Server error.' });
        }
    }

    // --- RESET PASSWORD ---
    static async resetPassword(req, res) {
        try {
            const { token, newPassword } = req.body;
            const resetData = await UserModel.findValidResetToken(token);

            if (!resetData) return res.status(400).json({ status: false, message: 'Token invalid/expired.' });

            const salt = await bcrypt.genSalt(10);
            const newHash = await bcrypt.hash(newPassword, salt);

            await UserModel.updatePassword(resetData.user_id, newHash);
            await UserModel.markTokenAsUsed(token);

            res.status(200).json({ status: true, message: 'Password berhasil direset.' });
        } catch (error) {
            res.status(500).json({ status: false, message: 'Server error.' });
        }
    }
}

module.exports = AuthController;