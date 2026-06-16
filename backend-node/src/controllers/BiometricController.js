
/**
 * file: backend-node/src/controllers/BiometricController.js
  * created by : yassuki
 * created date: 2025-12-11
 * description: Controller untuk login biometric.
 */


const {
    generateRegistrationOptions,
    verifyRegistrationResponse,
    generateAuthenticationOptions,
    verifyAuthenticationResponse,
} = require('@simplewebauthn/server');
const Redis = require('ioredis');
const jwt = require('jsonwebtoken');
const UAParser = require('ua-parser-js');
const requestIp = require('request-ip');

const UserModel = require('../models/userModel');
const ActivityLogService = require('../services/ActivityLogService');
const LoginLogModel = require('../models/LoginLogModel');
const MasterModel = require('../models/masterModel');
const logger = require('../utils/logger');

const redis = new Redis({
    host: process.env.REDIS_HOST,
    port: 6379,
});

// Helper untuk mengambil nama aplikasi
const getAppname = async () => {
    logger.info('[Helper-AppName] 🔍 Fetching App Name from MasterModel...');
    try {
        const app_settings = await MasterModel.getSettings();
        if (app_settings && app_settings.length > 0) {
            const name = app_settings[0]['app_name'];
            logger.info(`[Helper-AppName] ✅ App Name found: ${name}`);
            return name;
        }
        logger.info('[Helper-AppName] ⚠️ No settings found, using default: Percasi App');
        return 'Percasi App';
    } catch (error) {
        logger.error('[Helper-AppName] ❌ Gagal mengambil App Name:', error);
        return 'Percasi App';
    }
};

/**
 * HELPER: Konfigurasi RP (Relying Party) Dinamis
 */
const getRpConfig = (req) => {
    logger.info('[Helper-RpConfig] 🛠️ Detecting RP Configuration from headers...');
    const hostHeader = req.headers.host; 
    const rpID = hostHeader.split(':')[0]; 
    const protocol = rpID === 'localhost' ? 'http' : 'https';
    //const origin = `${protocol}://${hostHeader}`;
    const origin = req.headers.origin || req.headers.referer?.replace(/\/$/, "") || `http://${hostHeader}`;
    logger.info(`[Helper-RpConfig] 📍 Result -> rpID: ${rpID}, origin: ${origin}`);
    return { rpID, origin };
};

const BiometricController = {

    getDeviceType(userAgent) {
        const parser = new UAParser(userAgent);
        const device = parser.getDevice();
        const type = device.type || 'desktop';
        logger.info(`[UA-Parser] 📱 Device detected: ${type}`);
        return type;
    },

    // 1. GENERATE REGISTRATION
    generateRegistration: async (req, res) => {
        try {
            logger.info("--- [REG-GEN] START: Generate Registration ---");
            const user = req.user;
            logger.info(`[REG-GEN] 👤 User identified: ${user.email} (ID: ${user.id})`);

            const { rpID } = getRpConfig(req); 
            const rpName = await getAppname();
            const userID = Buffer.from(user.id.toString());

            logger.info(`[REG-GEN] ⚙️ Preparing options for RP ID: ${rpID}`);

            const options = await generateRegistrationOptions({
                rpName: rpName,
                rpID: rpID,
                userID: userID,
                userName: user.email,
                userDisplayName: user.username || user.email,
                attestationType: 'none',
                authenticatorSelection: {
                    residentKey: 'required',
                    userVerification: 'discouraged',
                    authenticatorAttachment: 'platform',
                },
            });

            logger.info(`[REG-GEN] 🎲 Challenge generated: ${options.challenge}`);

            // Simpan challenge ke Redis
            await redis.set(`challenge:reg:${user.id}`, options.challenge, 'EX', 300);
            logger.info(`[REG-GEN] 💾 Challenge stored in Redis for user ${user.id} (Expiry: 300s)`);
            
            const jsonOptions = {
                ...options,
                user: {
                    ...options.user,
                    id: Buffer.from(options.user.id).toString('base64url')
                }
            };

            logger.info("--- [REG-GEN] SUCCESS: Sending options to frontend ---");
            res.json(jsonOptions);
        } catch (error) {
            logger.error("--- [REG-GEN] ❌ CRITICAL ERROR:", error);
            res.status(500).json({ success: false, message: error.message });
        }
    },

    // 2. VERIFY REGISTRATION
    verifyRegistration: async (req, res) => {
        try {
            logger.info("--- [REG-VERIFY] START: Verify Registration ---");
            const { body } = req;
            const userId = req.user.id;
            logger.info(`[REG-VERIFY] 📩 Received payload for User ID: ${userId}`);

            const { rpID, origin } = getRpConfig(req);
            const expectedChallenge = await redis.get(`challenge:reg:${userId}`);
            
            if (!expectedChallenge) {
                logger.error("[REG-VERIFY] ❌ Challenge not found in Redis (Expired or Never Generated)");
                return res.status(400).json({ success: false, message: "Sesi kadaluarsa, silakan coba lagi." });
            }
            logger.info(`[REG-VERIFY] 🔑 Expected Challenge found: ${expectedChallenge}`);

            logger.info("[REG-VERIFY] 🔍 Running verifyRegistrationResponse...");
            const verification = await verifyRegistrationResponse({
                response: body,
                expectedChallenge,
                expectedOrigin: origin,
                expectedRPID: rpID,
                requireUserVerification: false
            });

            logger.info(`[REG-VERIFY] 📊 Verification status: ${verification.verified}`);

            if (verification.verified && verification.registrationInfo) {
                const { registrationInfo } = verification;
                const { credentialID, credentialPublicKey, counter } = registrationInfo;

                const finalCredentialID = Buffer.from(credentialID).toString('base64url');
                const finalPublicKey = Buffer.from(credentialPublicKey).toString('base64');
                const transports = body.response.transports || [];

                logger.info(`[REG-VERIFY] 📝 Saving New Credential to DB. ID: ${finalCredentialID.substring(0, 10)}...`);

                await UserModel.saveCredential(userId, {
                    credentialID: finalCredentialID,
                    publicKey: finalPublicKey,
                    counter: counter || 0,
                    transports: JSON.stringify(transports)
                });

                logger.info("[REG-VERIFY] ✅ DB Save Successful. Deleting challenge from Redis.");
                await redis.del(`challenge:reg:${userId}`);

                logger.info("--- [REG-VERIFY] SUCCESS: Registration Complete ---");
                return res.json({ success: true, message: 'Registrasi Berhasil' });
            }

            logger.error("[REG-VERIFY] ❌ Verification failed at library level.");
            res.status(400).json({ success: false, message: 'Verifikasi Gagal' });

        } catch (error) {
            logger.error("--- [REG-VERIFY] ❌ CRITICAL ERROR:", error);
            res.status(500).json({ success: false, message: error.message });
        }
    },

    // 3. GENERATE LOGIN OPTIONS
    generateLogin: async (req, res) => {
        try {
            logger.info("--- [AUTH-GEN] START: Generate Login Options ---");
            const { email } = req.body;
            logger.info(`[AUTH-GEN] 📧 Login attempt for email: ${email}`);

            const { rpID } = getRpConfig(req);
            const user = await UserModel.findByEmail(email);
            
            if (!user) {
                logger.error(`[AUTH-GEN] ❌ User not found in DB: ${email}`);
                return res.status(404).json({ message: 'User tidak ditemukan' });
            }

            logger.info(`[AUTH-GEN] 👤 User found (ID: ${user.id}). Fetching credentials...`);
            const credentials = await UserModel.getCredentials(user.id);
            logger.info(`[AUTH-GEN] 🗄️ Found ${credentials.length} credentials for this user.`);

            const options = await generateAuthenticationOptions({
                rpID,
                userVerification: 'discouraged',
            });

            logger.info(`[AUTH-GEN] 🎲 Auth Challenge: ${options.challenge}`);

            // Inject credentials secara manual
            options.allowCredentials = credentials.map(c => {
                const val = c.credential_id || c.credentialID;
                const fixID = String(val).trim(); 
                if (!fixID || fixID === "" || fixID === "null") return null;

                return {
                    id: fixID,
                    type: 'public-key'
                };
            }).filter(item => item !== null);

            logger.info(`[AUTH-GEN] 💉 Injected ${options.allowCredentials.length} valid credentials into options.`);

            await redis.set(`challenge:auth:${email}`, options.challenge, 'EX', 300);
            logger.info(`[AUTH-GEN] 💾 Auth Challenge saved in Redis for ${email}`);
            
            logger.info("--- [AUTH-GEN] SUCCESS: Sending options to frontend ---");
            res.json(options);

        } catch (error) {
            logger.error("--- [AUTH-GEN] ❌ CRITICAL ERROR:", error);
            res.status(500).json({ message: "Gagal menyiapkan login: " + error.message });
        }
    },

    // 4. VERIFY LOGIN
    verifyLogin: async (req, res) => {
        try {
            logger.info("--- [AUTH-VERIFY] START: Verify Login ---");
            const { email, authResponse } = req.body;
            const userAgent = req.headers['user-agent'] || '';
            const ipAddress = requestIp.getClientIp(req);
            
            logger.info(`[AUTH-VERIFY] 📩 Verifying login for ${email}. IP: ${ipAddress}`);

            const { rpID, origin } = getRpConfig(req);
            const expectedChallenge = await redis.get(`challenge:auth:${email}`);
            
            if (!expectedChallenge) {
                logger.error("[AUTH-VERIFY] ❌ Auth Challenge not found in Redis (Expired)");
                return res.status(400).json({ message: 'Sesi login kadaluarsa' });
            }

            const user = await UserModel.findByEmail(email);
            const credentials = await UserModel.getCredentials(user.id);

            logger.info(`[AUTH-VERIFY] 🔍 Searching DB for Credential ID: ${authResponse.id.substring(0, 10)}...`);
            const dbCred = credentials.find(c => (c.credential_id === authResponse.id) || (c.credentialID === authResponse.id));

            if (!dbCred) {
                logger.error("[AUTH-VERIFY] ❌ Credential ID from device does not match any in DB.");
                return res.status(400).json({ message: 'Perangkat tidak dikenal' });
            }
            logger.info(`[AUTH-VERIFY] ✅ Matching DB Credential found. ID: ${dbCred.id}`);

            const authenticatorObj = {
                credentialID: Buffer.from(dbCred.credential_id || dbCred.credentialID, 'base64url'),
                credentialPublicKey: Buffer.isBuffer(dbCred.public_key)
                    ? dbCred.public_key
                    : Buffer.from(dbCred.public_key, 'base64'),
                counter: parseInt(dbCred.counter) || 0,
            };

            logger.info("[AUTH-VERIFY] 🔍 Running verifyAuthenticationResponse...");
            const verification = await verifyAuthenticationResponse({
                response: authResponse,
                expectedChallenge,
                expectedOrigin: origin,
                expectedRPID: rpID,
                authenticator: authenticatorObj,
                requireUserVerification: false
            });

            logger.info(`[AUTH-VERIFY] 📊 Verification result: ${verification.verified}`);

            if (verification.verified) {
                const { newCounter } = verification.authenticationInfo;
                
                logger.info(`[AUTH-VERIFY] 🔢 Updating counter to: ${newCounter}`);
                await UserModel.updateCredentialCounter(dbCred.id, newCounter);
                await redis.del(`challenge:auth:${email}`);

                logger.info("[AUTH-VERIFY] 🎫 Generating JWT Token...");
                const token = jwt.sign(
                    { id: user.id, email: user.email, username: user.username },
                    process.env.JWT_SECRET, { expiresIn: '24h' }
                );

                // Simpan sesi ke redis & database
                await redis.set(`auth:session:${user.id}`, token, 'EX', 86400);
                await UserModel.updateActiveToken(user.id, token);
                
                logger.info("[AUTH-VERIFY] 📜 Logging activity and login success...");
                await ActivityLogService.log(user.id, 'LOGIN', 'Biometric login success', ipAddress, userAgent);
                await LoginLogModel.create({
                    user_id: user.id, input_email: email, ip_address: ipAddress,
                    user_agent: userAgent, device_type: 'biometric-login', status: 'success'
                });

                logger.info(`--- [AUTH-VERIFY] ✅ SUCCESS: User ${user.id} logged in via Biometric ---`);
                return res.json({
                    success: true,
                    token,
                    user: {
                        id: user.id,
                        username: user.username,
                        email: user.email,
                        balance: user.balance
                    }
                });
            }

            logger.error("[AUTH-VERIFY] ❌ verifyAuthenticationResponse returned false.");
            res.status(400).json({ message: 'Gagal verifikasi biometrik' });

        } catch (error) {
            logger.error("--- [AUTH-VERIFY] ❌ CRITICAL ERROR:", error);
            res.status(500).json({ message: error.message });
        }
    }
};

module.exports = BiometricController;
