/**
 * file: backend-node/src/models/userModel.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Model Database untuk manajemen User (Auth, Profil) - Updated for Single Session.
 */

const pool = require('../config/database');
const bcrypt = require('bcrypt');
const logger = require('../utils/logger');

class UserModel {

    /**
     * Mencari user berdasarkan username.
     */
    static async findByUsername(username) {
        const query = `SELECT * FROM users WHERE username = ? LIMIT 1`;
        const [rows] = await pool.execute(query, [username]);
        return rows.length > 0 ? rows[0] : null;
    }
    /**
     * Mencari user berdasarkan email.
     */
    static async findByEmail(email) {
        const query = `SELECT * FROM users WHERE email = ? LIMIT 1`;
        const [rows] = await pool.execute(query, [email]);
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * Membuat user baru (Register).
     */
    static async createUser(userData) {
        const { username, email, passwordHash, googleId, fullName } = userData;

        const query = `
            INSERT INTO users 
            (username, email, password_hash, google_id, full_name, user_status, created_at)
            VALUES (?, ?, ?, ?, ?, 'ACT', NOW())
        `;

        const [result] = await pool.execute(query, [
            username, email, passwordHash || null, googleId || null, fullName
        ]);
        return result.insertId;
    }

    /**
     * Update Google ID untuk user yang sudah ada.
     */
    static async updateGoogleId(userId, googleId) {
        await pool.execute(
            `UPDATE users SET google_id = ? WHERE id = ?`, [googleId, userId]
        );
    }

    /**
     * Ambil profil user ID lengkap dengan active_token.
     */
    static async findById(id) {
        // Menambahkan active_token ke SELECT agar bisa dicek di Controller
        const query = `
            SELECT 
                id, username, email, full_name,is_phone_verified,is_email_verified, open_match,user_status, avatar_url, 
                kyc_status, kyc_rejection_reason, active_token,is_wallet_pinset,
                country_id, province_id, regency_id, district_id, bio_login_active,subdistrict_id, postal_code, 
                phone_number, address_line, bio_login_active,is_2fa_active,is_single_login, wallets.balance,wallets.status as wallet_status
                 
            FROM users INNER JOIN wallets ON  wallets.user_id = users.id WHERE id = ?
        `;
        const [rows] = await pool.execute(query, [id]);
        return rows[0];
    }

    /**
     * Mengambil data user lengkap termasuk password hash.
     */
    static async findByIdWithPassword(userId) {
        const [rows] = await pool.execute(
            `SELECT id, password_hash FROM users WHERE id = ?`, [userId]
        );
        return rows[0];
    }
    /**
     * Mengambil data user lengkap termasuk password hash.
     */
    static async findByIdWithWalletPIN(userId) {
        const [rows] = await pool.execute(
            `SELECT id, pin_hash FROM users WHERE id = ?`, [userId]
        );
        return rows[0];
    }

    /**
     * Mengupdate password user.
     */
    static async updatePassword(userId, newPasswordHash) {
        await pool.execute(
            `UPDATE users SET password_hash = ? WHERE id = ?`, [newPasswordHash, userId]
        );
    }
    /**
     * Mengupdate password user.
     */
    static async updateOTPstatus(userId, type) {
        try {
        if(type=="PHONE") {
             await pool.execute(`UPDATE users SET is_phone_verified = 'VERIFIED' WHERE id = ?`, [userId]);
        } if(type=="EMAIL") {
            await pool.execute(`UPDATE users SET is_email_verified = 'VERIFIED' WHERE id = ?`, [userId]);
        }
    } catch(err) {
        logger.info(err);
    }
    }

    /**
     * Simpan Active Token (Untuk Single Session / Blokir Login Ganda).
     * @param {number} userId 
     * @param {string|null} token - Token JWT baru atau null (saat logout)
     */
    /**
     * Simpan Active Token (Updated dengan Debugging)
     */
    static async updateActiveToken(userId, token) {
        try {
            logger.info(`[DEBUG DB] Updating token user ${userId}...`);
            const query = `UPDATE users SET active_token = ? WHERE id = ?`;
            const [result] = await pool.execute(query, [token, userId]);

            logger.info(`[DEBUG DB] Success. Rows affected: ${result.affectedRows}`);
            return result.affectedRows > 0;
            
        } catch (error) {
            logger.error("[DEBUG DB ERROR] Gagal update token:", error.message);
            throw error;
        }
    }

    /**
     * Menyimpan token reset password.
     */
    static async createPasswordReset(userId, token, expiresAt) {
        await pool.execute(
            `UPDATE password_resets SET is_used = 1 WHERE user_id = ?`, [userId]
        );

        await pool.execute(
            `INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)`, 
            [userId, token, expiresAt]
        );
    }

    /**
     * Mencari token reset yang valid.
     */
    static async findValidResetToken(token) {
        const query = `
            SELECT * FROM password_resets 
            WHERE token = ? AND is_used = 0 AND expires_at > NOW() 
            LIMIT 1
        `;
        const [rows] = await pool.execute(query, [token]);
        return rows.length > 0 ? rows[0] : null;
    }

    /**
     * Menandai token reset sebagai sudah digunakan.
     */
    static async markTokenAsUsed(token) {
        await pool.execute(
            `UPDATE password_resets SET is_used = 1 WHERE token = ?`, [token]
        );
    }

    /**
     * Mengupdate data profil user secara dinamis.
     */
    static async update(userId, data) {
        const allowedColumns = [
            'full_name', 'phone_number','bio_login_active','is_single_login','is_2fa_active','open_match', 'avatar_url', 'address_line',
            'country_id', 'province_id', 'regency_id', 'district_id', 'subdistrict_id', 'postal_code','is_wallet_pinset'
        ];

        const updates = [];
        const values = [];

        for (const [key, value] of Object.entries(data)) {
            if (allowedColumns.includes(key)) {
                updates.push(`${key} = ?`);
                values.push(value);
            }
        }

        if (updates.length === 0) return false;

        values.push(userId);

        const query = `UPDATE users SET ${updates.join(', ')}, updated_at = NOW() WHERE id = ?`;

        const [result] = await pool.execute(query, values);
        return result.affectedRows > 0;
    }
   // backend-node/src/models/userModel.js

    static async updateTemp2FAToken(userId, tempToken, tempSecret, expiryDate) {
        // expiryDate diterima sudah dalam bentuk string 'YYYY-MM-DD HH:mm:ss'
        // Jadi langsung masukkan ke query saja.
        
        let query = `UPDATE users SET temp_token = ?, temp_token_expiry = ?`;
        let params = [tempToken, expiryDate];

        if (tempSecret) {
            query += `, temp_2fa_secret = ?`;
            params.push(tempSecret);
        }
        
        query += ` WHERE id = ?`;
        params.push(userId);

        return pool.query(query, params);
    }

    /**
     * [BARU] Cari User berdasarkan Temp Token (yang belum expired)
     * Digunakan saat verifikasi
     */
   static async findByTempToken(tempToken) {
        if (!tempToken || typeof tempToken !== 'string') {
            throw new Error('Token tidak valid atau kosong');
        }

        try {
            const sql = `
                SELECT 
                    id, 
                    email, 
                    username, 
                    temp_token,
                    temp_token_expiry,
                    two_factor_secret,
                    temp_2fa_secret,
                    two_factor_secret,
                    user_status,
                    created_at,
                    updated_at
                FROM users 
                WHERE temp_token = ? 
                AND temp_token_expiry > UTC_TIMESTAMP() 
                AND user_status = 'ACT'
                LIMIT 1
            `;

            logger.info('Query findByTempToken:', sql, 'Token:', tempToken);
            
            const [rows] = await pool.execute(sql, [tempToken.trim()]);
            
            if (rows.length === 0) {
                logger.info('Token tidak ditemukan atau sudah kedaluwarsa');
                return null;
            }
            
            logger.info('Token ditemukan untuk user:', rows[0].email);
            return rows[0];
        } catch(err) {
            logger.error('Error dalam findByTempToken:', {
                message: err.message,
                stack: err.stack,
                token: tempToken ? tempToken.substring(0, 10) + '...' : 'null'
            });
            throw new Error(`Gagal mencari user dengan token: ${err.message}`);
        }
    }
    /**
     * Menyimpan secret 2FA secara permanen setelah verifikasi pertama sukses
     * dan mengaktifkan flag is_2fa_active
     */
    static async savePermanent2FASecret(userId, secret) {
        try {
            const query = `
                UPDATE users 
                SET 
                    two_factor_secret = ?, 
                    is_2fa_active = 'YES',
                    temp_2fa_secret = NULL 
                WHERE id = ?
            `;
            // Pastikan Anda menggunakan library db/pool yang sesuai (mysql2/promise)
            const [result] = await pool.query(query, [secret, userId]);
            return result;
        } catch (error) {
            logger.error('[DATABASE ERROR] savePermanent2FASecret:', error);
            throw error;
        }
    }

    static async disable2FA(userId, secret) {
        try {
            const query = `
                UPDATE users 
                SET 
                    two_factor_secret = ?, 
                    is_2fa_active = 'NO',
                    temp_2fa_secret = NULL,
                    temp_token=NULL,
                    temp_token_expiry=NULL,
                    temp_2fa_token=NULL,
                    temp_2fa_expiry=NULL,
                    temp_2fa_secret=NULL,
                    two_factor_secret=NULL 
                WHERE id = ?
            `;
            // Pastikan Anda menggunakan library db/pool yang sesuai (mysql2/promise)
            const [result] = await pool.query(query, [secret, userId]);
            return result;
        } catch (error) {
            logger.error('[DATABASE ERROR] savePermanent2FASecret:', error);
            throw error;
        }
    }

    /**
     * [BARU] Bersihkan Temp Token setelah sukses login
     */
    static async clearTemp2FAToken(userId) {
        const sql = `
            UPDATE users 
            SET temp_token = NULL, 
                temp_token_expiry = NULL 
            WHERE id = ?
        `;
        return pool.execute(sql, [userId]);
    }
    //biometric login
    /**
     * Mengambil daftar kunci biometrik yang terdaftar untuk user tertentu
     */
    static async getCredentials (userId) {
       const [rows] = await pool.query(
            'SELECT id, credential_id, public_key, COALESCE(counter, 0) as counter FROM user_credentials WHERE user_id = ?',
            [userId]
        );
        return rows;
    }

    /**
     * Menyimpan data biometrik baru setelah registrasi berhasil
     */
    static async saveCredential (userId, data) {
        const { credentialID, publicKey, counter } = data;

        // 1. Cek apakah user ini sudah punya data credential?
        const checkQuery = 'SELECT id FROM user_credentials WHERE user_id = ? LIMIT 1';
        const [existing] = await pool.execute(checkQuery, [userId]);

        if (existing.length > 0) {
            // 2. UPDATE: Jika data sudah ada, timpa credential lama dengan yang baru
            const updateQuery = `
                UPDATE user_credentials 
                SET credential_id = ?, public_key = ?, counter = ?, updated_at = NOW() 
                WHERE user_id = ?
            `;
            const [result] = await pool.execute(updateQuery, [credentialID, publicKey, counter, userId]);
            return result;
        } else {
            // 3. INSERT: Jika belum ada, buat baru
            const insertQuery = `
                INSERT INTO user_credentials (user_id, credential_id, public_key, counter) 
                VALUES (?, ?, ?, ?)
            `;
            const [result] = await pool.execute(insertQuery, [userId, credentialID, publicKey, counter]);
            return result;
        }
    }

    /**
     * Update counter untuk mencegah Replay Attack (Standar Keamanan WebAuthn)
     */
    static async getUsersWithPagination({
        page = 1,
        limit = 10,
        search = '',
        userStatus = null,
        kycStatus = null,
        sortBy = 'id', // Default ke ID lebih cepat daripada created_at
        sortOrder = 'DESC'
    } = {}) {
        try {
            const offset = (Math.max(1, page) - 1) * limit;
            const conditions = [];
            const params = [];

            // 1. Optimasi Search: Gunakan Prefix Search (search%) bukan (%search%)
            if (search && search.trim() !== '') {
                const trimmedSearch = search.trim();
                if (!isNaN(trimmedSearch)) {
                    // Jika input angka, langsung tembak ID (Primary Key - Instan)
                    conditions.push('u.id = ?');
                    params.push(trimmedSearch);
                } else {
                    // Gunakan "LIKE 'term%'" agar INDEX tetap terpakai
                    conditions.push('(u.username LIKE ? OR u.email LIKE ?)');
                    const searchParam = `${trimmedSearch}%`; 
                    params.push(searchParam, searchParam);
                }
            }

            if (userStatus && userStatus !== 'all') {
                conditions.push('u.user_status = ?');
                params.push(userStatus);
            }

            if (kycStatus && kycStatus !== 'all') {
                conditions.push('u.kyc_status = ?');
                params.push(kycStatus);
            }

            const whereClause = conditions.length > 0 ? `WHERE ${conditions.join(' AND ')}` : '';

            // 2. Query Total Count Terpisah (Jauh lebih cepat tanpa JOIN)
            const countQuery = `SELECT COUNT(*) as total FROM users u ${whereClause}`;
            const [countResult] = await pool.execute(countQuery, params);
            const total = countResult[0].total;

            if (total === 0) return { data: [], pagination: { total, page, limit, totalPages: 0 } };

            // 3. Main Query: Tanpa SQL_CALC_FOUND_ROWS
            const dataQuery = `
                SELECT 
                    u.id, u.username, u.email, u.full_name, u.avatar_url,
                    u.user_status, u.kyc_status, u.open_match, u.created_at,
                    COALESCE(w.balance, 0) as balance,
                    COALESCE(ur.standard_rating, 1200) as standard_rating,
                    COALESCE(p.name, '') as province_name,
                    COALESCE(r.name, '') as regency_name
                FROM users u
                LEFT JOIN wallets w ON w.user_id = u.id
                LEFT JOIN user_ratings ur ON ur.user_id = u.id
                LEFT JOIN master_provinces p ON u.province_id = p.id
                LEFT JOIN master_regencies r ON u.regency_id = r.id
                ${whereClause}
                ORDER BY u.${sortBy} ${sortOrder}
                LIMIT ? OFFSET ?
            `;

            const [users] = await pool.execute(dataQuery, [...params, String(limit), String(offset)]);

            return {
                data: users,
                pagination: {
                    total,
                    page: Number(page),
                    limit: Number(limit),
                    totalPages: Math.ceil(total / limit)
                }
            };

        } catch (error) {
            logger.error('[UserModel Error] getUsersWithPagination:', error.message);
            throw error;
        }
    }
}

module.exports = UserModel;