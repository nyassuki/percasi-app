/**
 * file: backend-node/src/controllers/userController.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Controller untuk manajemen profil user.
 */

const UserModel = require('../models/userModel');
const logger = require('../utils/logger');

class UserController {

    /**
     * Mengambil profil diri sendiri.
     * GET /api/users/profile
     */
    static async getMyProfile(req, res) {
        try {
            const user = await UserModel.findById(req.user.id);
            if (!user) return res.status(404).json({
                message: 'User tidak ditemukan'
            });

            res.status(200).json({
                status: 'success',
                data: user
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    /**
     * Mengupdate profil (Text & Avatar).
     * PUT /api/users/profile
     */
    static async updateProfile(req, res) {
            try {
                const userId = req.user.id;
                const {
                    full_name,
                    phone_number,
                    address_line,
                    country_id,
                    province_id,
                    regency_id,
                    district_id,
                    subdistrict_id,
                    postal_code,
                    is_single_login,
                    is_2fa_active,
                    bio_login_active,
                    open_match
                } = req.body;

                // Siapkan objek data update
                const updateData = {};

                if (full_name) updateData.full_name = full_name;
                if (phone_number) updateData.phone_number = phone_number;
                if (address_line) updateData.address_line = address_line;
                if (postal_code) updateData.postal_code = postal_code;

                // Handle ID Wilayah (konversi ke int atau null jika string kosong)
                if (country_id) updateData.country_id = parseInt(country_id);
                if (province_id) updateData.province_id = parseInt(province_id);
                if (regency_id) updateData.regency_id = parseInt(regency_id);
                if (district_id) updateData.district_id = parseInt(district_id);
                if (subdistrict_id) updateData.subdistrict_id = parseInt(subdistrict_id);
                

                //SETTING UPDATE
                if (is_single_login) updateData.is_single_login = parseInt(is_single_login);
                if (is_2fa_active) updateData.is_2fa_active = is_2fa_active;
                if (bio_login_active) updateData.bio_login_active = bio_login_active;
                if (open_match) updateData.open_match = open_match;
          

                // Handle Avatar Upload
                if (req.file) {
                    updateData.avatar_url = req.file.path; // Simpan path gambar
                }

                await UserModel.update(userId, updateData);

                // Ambil data terbaru untuk dikembalikan ke frontend
                const updatedUser = await UserModel.findById(userId);

                res.status(200).json({
                    status: 'success',
                    message: 'Profil berhasil diperbarui.',
                    data: updatedUser
                });

            } catch (error) {
                logger.error('[UPDATE PROFILE ERROR]', error);
                res.status(500).json({
                    message: error.message
                });
            }
        }
        // Ambil info singkat user untuk tujuan transfer
    static async getPublicProfile(req, res) {
        try {
            const userId = req.params.id;
            const updatedUser = await UserModel.findById(userId);
            res.status(200).json({
                status: 'success',
                data: updatedUser
            });
        } catch (error) {
            logger.error('[GET PROFILE ERROR]', error);
            res.status(500).json({
                message: error.message
            });
        }
    };
    static async  getAllUsers  (req, res)  {
        try {
            const {
                page = 1,
                limit = 10,
                search = '',
                userStatus = null,
                kycStatus = null,
                sortBy = 'created_at',
                sortOrder = 'DESC'
            } = req.query;
            
            const result = await UserModel.getUsersWithPagination({
                page: parseInt(page),
                limit: parseInt(limit),
                search,
                userStatus,
                kycStatus,
                sortBy,
                sortOrder
            });
            
            res.json({
                success: true,
                message: 'Users retrieved successfully',
                ...result
            });
        } catch (error) {
            logger.error('[UserController Error] getUsers:', error);
            res.status(500).json({
                success: false,
                message: 'Failed to retrieve users',
                error: error.message
            });
        }
    };

}

module.exports = UserController;