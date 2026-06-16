/**
 * file: backend-node/src/models/masterModel.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Model tunggal untuk mengambil data referensi (Bank & Wilayah).
 */

const pool = require('../config/database');
const logger = require('../utils/logger');

class MasterModel {

    // --- BANK DATA ---

    static async getBanks() {
        // Mengambil bank yang aktif saja
        const [rows] = await pool.execute(
            `SELECT bank_code, bank_name FROM master_banks WHERE is_active = 1 ORDER BY bank_name ASC`
        );
        return rows;
    }

    // --- REGIONAL DATA (HIERARCHY) ---

    static async getCountries() {
        const [rows] = await pool.execute(
            `SELECT id, countryName, countryCode, telephonePrefix FROM master_countries ORDER BY countryName ASC`
        );
        return rows;
    }

    static async getProvinces(countryId) {
        const [rows] = await pool.execute(
            `SELECT id, name FROM master_provinces WHERE country_id = ? ORDER BY name ASC`, [countryId]
        );
        return rows;
    }

    static async getRegencies(provinceId) {
        const [rows] = await pool.execute(
            `SELECT id, name FROM master_regencies WHERE province_id = ? ORDER BY name ASC`, [provinceId]
        );
        return rows;
    }

    static async getDistricts(regencyId) {
        const [rows] = await pool.execute(
            `SELECT id, name FROM master_districts WHERE regency_id = ? ORDER BY name ASC`, [regencyId]
        );
        return rows;
    }

    static async getSubdistricts(districtId) {
        const [rows] = await pool.execute(
            `SELECT id, name, postal_code FROM master_subdistricts WHERE district_id = ? ORDER BY name ASC`, [districtId]
        );
        return rows;
    }

    // --- SETTINGS DATA ---

    static async getSettings() {
        // Mengambil bank yang aktif saja
        const [rows] = await pool.execute(
            `SELECT *  FROM system_settings WHERE is_active = 1`
        );
     
        return rows;
    }
    static async getBandara() {
        // Mengambil bank yang aktif saja
        const [rows] = await pool.execute(
            `SELECT *  FROM bandara WHERE is_active = 1`
        );
     
        return rows;
    }
    static async getStasiun() {
        // Mengambil bank yang aktif saja
        const [rows] = await pool.execute(
            `SELECT *  FROM stasiun_ka WHERE status = 'Beroperasi'`
        );
     
        return rows;
    }
    static async searchClubs(query) {
        // Mencari klub yang namanya mengandung kata kunci input user
        const sql = `SELECT id, name FROM chess_clubs WHERE name LIKE ? LIMIT 10`;
        const [rows] = await db.query(sql, [`%${query}%`]);
        return rows;
    }

    static async findOrCreateClub(clubName) {
        // Cek apakah sudah ada yang sama persis (case insensitive)
        const [existing] = await db.query("SELECT id FROM chess_clubs WHERE name = ?", [clubName]);
        if (existing.length > 0) return existing[0].id;

        // Jika tidak ada, buat baru
        const [result] = await db.query("INSERT INTO chess_clubs (name) VALUES (?)", [clubName]);
        return result.insertId;
    }
}

module.exports = MasterModel;