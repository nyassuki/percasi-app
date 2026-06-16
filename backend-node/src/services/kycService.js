/**
 * file: backend-node/src/services/kycService.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Service untuk manajemen KYC (Submission & Verification).
 */

const pool = require('../config/database');
const logger = require('../utils/logger');

class KycService {

    /**
     * Mengajukan data KYC baru (User).
     * @param {number} userId 
     * @param {string} ktpPath 
     * @param {string} selfiePath 
     */
    static async submitKyc(userId, ktpPath, selfiePath) {
        const query = `
      UPDATE users 
      SET kyc_document_url = ?, 
          kyc_selfie_url = ?, 
          kyc_status = 'pending',
          updated_at = NOW()
      WHERE id = ?
    `;

        await pool.execute(query, [ktpPath, selfiePath, userId]);
        return {
            status: 'pending',
            message: 'Dokumen berhasil diunggah. Menunggu verifikasi admin.'
        };
    }

    /**
     * (ADMIN) Menyetujui atau Menolak KYC.
     * @param {number} userId 
     * @param {string} status - 'verified' atau 'rejected'
     * @param {string} reason - Alasan jika ditolak
     */
    static async verifyKyc(userId, status, reason = null) {
        if (!['verified', 'rejected'].includes(status)) {
            throw new Error("Status tidak valid.");
        }

        const query = `
      UPDATE users 
      SET kyc_status = ?, 
          kyc_rejection_reason = ?,
          updated_at = NOW()
      WHERE id = ?
    `;

        await pool.execute(query, [status, reason, userId]);
    }
}

module.exports = KycService;