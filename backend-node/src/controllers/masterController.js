/**
 * file: backend-node/src/controllers/masterController.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Controller untuk endpoint master data.
 */

const MasterModel = require('../models/masterModel');
const logger = require('../utils/logger');

class MasterController {

    /**
     * GET /api/master/banks
     */
    static async getBanks(req, res) {
        try {
            const data = await MasterModel.getBanks();
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    /**
     * GET /api/master/countries
     */
    static async getCountries(req, res) {
        try {
            const data = await MasterModel.getCountries();
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    /**
     * GET /api/master/provinces/:countryId
     */
    static async getProvinces(req, res) {
        try {
            const {
                countryId
            } = req.params;
            const data = await MasterModel.getProvinces(countryId);
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    /**
     * GET /api/master/regencies/:provinceId
     */
    static async getRegencies(req, res) {
        try {
            const {
                provinceId
            } = req.params;
            const data = await MasterModel.getRegencies(provinceId);
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    /**
     * GET /api/master/districts/:regencyId
     */
    static async getDistricts(req, res) {
        try {
            const {
                regencyId
            } = req.params;
            const data = await MasterModel.getDistricts(regencyId);
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    /**
     * GET /api/master/subdistricts/:districtId
     */
    static async getSubdistricts(req, res) {
        try {
            const {
                districtId
            } = req.params;
            const data = await MasterModel.getSubdistricts(districtId);
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }
     /**
     * GET /api/master/getsetting
     */
    static async getSettings(req, res) {
        try {
            const {
                settings
            } = req.params;
            const data = await MasterModel.getSettings();
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }
     /**
     * GET /api/master/bandara
     */
    static async getBandara(req, res) {
        try {
            const {
                settings
            } = req.params;
            const data = await MasterModel.getBandara();
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }
    /**
     * GET /api/master/stasiun
     */
    static async getStasiun(req, res) {
        try {
            const {
                settings
            } = req.params;
            const data = await MasterModel.getStasiun();
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }
    static async getClubs(req, res) {
        try {
            const { q } = req.query;
            if (!q) return res.json([]);
            const clubs = await MasterModel.searchClubs(q);
            res.json(clubs);
        } catch (error) {
            res.status(500).json({ status: false, message: error.message });
        }
    }

    static async updateProfileClub(req, res) {
        try {
            const { userId, clubId, newClubName } = req.body;
            let finalClubId = clubId;

            // Jika user memilih untuk input manual (clubId null/empty)
            if (!clubId && newClubName) {
                finalClubId = await MasterModel.findOrCreateClub(newClubName);
            }

            // Update di tabel users (kolom chess_club_id)
            await db.query("UPDATE users SET chess_club_id = ? WHERE id = ?", [finalClubId, userId]);

            res.json({ status: true, message: "Klub berhasil diperbarui" });
        } catch (error) {
            res.status(500).json({ status: false, message: error.message });
        }
    }
}

module.exports = MasterController;