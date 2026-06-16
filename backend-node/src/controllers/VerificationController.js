/**
 * file: backend-node/src/controllers/VerificationController.js
 * created by : yassuki
 * created date: 2025-12-11
 */

const OtpModel = require('../models/OtpModel');
const crypto = require('crypto');
const UserModel = require('../models/userModel');
const logger = require('../utils/logger');


const VerificationController = {
    // Generate OTP Helper
    _generateOTP: () => crypto.randomInt(100000, 999999).toString(),

    // Request OTP via Phone
    requestPhoneOtp: async (req, res) => {
        logger.info(req.body);

        const { phone } = req.body;
        if (!phone) return res.status(400).json({ success: false, message: 'Nomor HP wajib diisi' });

        const otp = VerificationController._generateOTP();
        
        try {
            // Memanggil Model untuk simpan data
            await OtpModel.save('phone', phone, otp, 300);
            logger.info(`[OTP Percasi] Ke: ${phone} -> ${otp}`); 
            
            return res.json({ success: true, message: 'OTP terkirim', expires_in: 300 });
        } catch (error) {
            return res.status(500).json({ success: false, message: 'Kesalahan Model Data' });
        }
    },

    // Match OTP Phone
    matchPhoneOtp: async (req, res) => {
        const { phone, otp_code } = req.body;
         try {
            const storedOtp = await OtpModel.get('phone', phone,otp_code);
            
 
            if (!storedOtp) return res.status(400).json({ success: false, message: 'Kode kadaluarsa' });

            if (storedOtp === otp_code) {
                await OtpModel.delete('phone', phone); // Hapus via Model
                await UserModel.updateOTPstatus(req.user.id, "PHONE");
                return res.json({ success: true, message: 'Verifikasi sukses' });
            }
            return res.status(400).json({ success: false, message: 'Kode salah' });
        } catch (error) {
            return res.status(500).json({ success: false, message: 'Kesalahan Validasi Data' });
        }
    },

    // Request OTP via Email
    requestEmailOtp: async (req, res) => {
        const { email } = req.body;
        if (!email) return res.status(400).json({ success: false, message: 'Email wajib diisi' });

        const otp = VerificationController._generateOTP();

        try {
            await OtpModel.save('email', email, otp, 600);
            logger.info(`[OTP Percasi] Ke: ${email} -> ${otp}`);
            
            return res.json({ success: true, message: 'OTP terkirim ke email', expires_in: 600 });
        } catch (error) {
            return res.status(500).json({ success: false, message: 'Kesalahan Model Data' });
        }
    }
};

module.exports = VerificationController;