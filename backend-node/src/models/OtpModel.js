const Redis = require('ioredis');

// Menggunakan koneksi Redis yang sama dengan sistem monitor cluster Anda
const redis = new Redis({
    host: process.env.REDIS_HOST || '127.0.0.1',
    port: 6379,
});
const logger = require('../utils/logger');

const OtpModel = {
    /**
     * Simpan OTP ke Redis
     * @param {string} type - 'phone' atau 'email'
     * @param {string} identifier - Nomor HP atau Email
     * @param {string} otp - Kode 6 digit
     * @param {number} ttl - Waktu kadaluarsa dalam detik
     */
    save: async (type, identifier, otp, ttl) => {
        const key = `percasi:otp:${type}:${identifier}`;
        return await redis.set(key, otp, 'EX', ttl);
    },

    /**
     * Ambil OTP dari Redis
     */
    get: async (type, identifier) => {
        const key = `percasi:otp:${type}:${identifier}`;
        return await redis.get(key);
    },

    /**
     * Hapus OTP dari Redis (setelah berhasil diverifikasi)
     */
    delete: async (type, identifier) => {
        const key = `percasi:otp:${type}:${identifier}`;
        return await redis.del(key);
    }
};

module.exports = OtpModel;