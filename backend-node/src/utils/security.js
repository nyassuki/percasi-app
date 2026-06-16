const crypto = require('crypto');
const logger = require('./logger');

// Konfigurasi algoritma
const ALGORITHM = 'aes-256-gcm';

// 1. Inisialisasi Master Key dengan proteksi
// Pastikan di .env MASTER_KEY berupa 64 karakter HEX
const MASTER_KEY_RAW = process.env.MASTER_KEY || '';
const MASTER_KEY = Buffer.from(MASTER_KEY_RAW, 'hex');

const HMAC_SECRET = process.env.HMAC_SECRET;

class Security {

    /**
     * Validasi Kunci saat aplikasi dinyalakan
     */
    static validateKeys() {
        if (MASTER_KEY.length !== 32) {
            logger.error(`[SECURITY] MASTER_KEY invalid! Length: ${MASTER_KEY.length} bytes (Expected 32)`);
            throw new Error(`CRITICAL: MASTER_KEY must be 32 bytes (64 hex chars).`);
        }
        logger.info(`[SECURITY] Master Key validated successfully.`);
    }
    
    /**
     * 1. MASTER KEY OPERATIONS
     */
    static generateNewDataKey() {
        const dek = crypto.randomBytes(32); 
        const iv = crypto.randomBytes(12);
        const cipher = crypto.createCipheriv(ALGORITHM, MASTER_KEY, iv);
        
        let encryptedDek = cipher.update(dek, 'binary', 'hex');
        encryptedDek += cipher.final('hex');
        const authTag = cipher.getAuthTag().toString('hex');

        return `${iv.toString('hex')}:${authTag}:${encryptedDek}`;
    }

    static unwrapDataKey(wrappedDek) {
        try {
            const [ivHex, authTagHex, encryptedHex] = wrappedDek.split(':');
            const decipher = crypto.createDecipheriv(
                ALGORITHM, 
                MASTER_KEY, 
                Buffer.from(ivHex, 'hex')
            );
            decipher.setAuthTag(Buffer.from(authTagHex, 'hex'));

            let decrypted = decipher.update(encryptedHex, 'hex');
            // Gunakan Buffer.concat untuk hasil yang lebih aman
            decrypted = Buffer.concat([decrypted, decipher.final()]);
            
            return decrypted; // Mengembalikan Buffer 32 byte
        } catch (err) {
            logger.error("[SECURITY] unwrapDataKey failed:", err.message);
            throw new Error('Gagal membuka bungkus DEK. Pastikan MASTER_KEY benar.');
        }
    }

    /**
     * 2. DATA OPERATIONS
     */
    static encryptData(text, plainDataKey) {
        try {
            // PROTEKSI: Pastikan plainDataKey adalah Buffer 32 byte
            const key = Buffer.isBuffer(plainDataKey) ? plainDataKey : Buffer.from(plainDataKey, 'hex');

            if (key.length !== 32) {
                throw new Error(`Invalid key length: ${key.length}. Expected 32.`);
            }

            const iv = crypto.randomBytes(12);
            const cipher = crypto.createCipheriv(ALGORITHM, key, iv);
            
            let encrypted = cipher.update(text.toString(), 'utf8', 'hex');
            encrypted += cipher.final('hex');
            const authTag = cipher.getAuthTag().toString('hex');

            return `${iv.toString('hex')}:${authTag}:${encrypted}`;
        } catch (err) {
            logger.error("[SECURITY] encryptData failed:", err.message);
            throw err;
        }
    }

    static decryptWithDEK(encryptedData, plainDataKey) {
        try {
            const key = Buffer.isBuffer(plainDataKey) ? plainDataKey : Buffer.from(plainDataKey, 'hex');
            const [ivHex, authTagHex, encryptedHex] = encryptedData.split(':');
            
            const decipher = crypto.createDecipheriv(ALGORITHM, key, Buffer.from(ivHex, 'hex'));
            decipher.setAuthTag(Buffer.from(authTagHex, 'hex'));

            let decrypted = decipher.update(encryptedHex, 'hex', 'utf8');
            decrypted += decipher.final('utf8');
            return decrypted;
        } catch (err) {
            logger.error("[SECURITY] decryptWithDEK failed:", err.message);
            return "[DECRYPTION_ERROR]";
        }
    }

    static generateSignature(payload) {
        return crypto
            .createHmac('sha256', HMAC_SECRET)
            .update(JSON.stringify(payload))
            .digest('hex');
    }

    static decryptOld(encryptedData) {
        try {
            const [ivHex, authTagHex, encryptedHex] = encryptedData.split(':');
            const decipher = crypto.createDecipheriv(ALGORITHM, MASTER_KEY, Buffer.from(ivHex, 'hex'));
            decipher.setAuthTag(Buffer.from(authTagHex, 'hex'));

            let decrypted = decipher.update(encryptedHex, 'hex', 'utf8');
            decrypted += decipher.final('utf8');
            return decrypted;
        } catch (err) {
            return "[LEGACY_DECRYPTION_ERROR]";
        }
    }
}

// Jalankan validasi saat module dimuat
Security.validateKeys();

module.exports = Security;