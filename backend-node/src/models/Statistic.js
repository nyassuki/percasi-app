/**
 * file: src/models/Statistic.js
 */
const pool = require('../config/database');
const logger = require('../utils/logger');

class Statistic {

    /**
     * Helper: Menentukan Title Rank berdasarkan ELO Rating
     */
    static getRankTitle(rating) {
        if (rating >= 2500) return 'Grandmaster';
        if (rating >= 2200) return 'International Master';
        if (rating >= 2000) return 'Master';
        if (rating >= 1800) return 'Elite';
        if (rating >= 1600) return 'Expert';
        if (rating >= 1400) return 'Advanced';
        if (rating >= 1200) return 'Intermediate';
        if (rating >= 1000) return 'Novice';
        return 'Beginner';
    }

    /**
     * Mengambil statistik lengkap user berdasarkan ID
     * @param {number} userId 
     */
    static async getUserStats(userId) {
        try {
            // 1. Ambil Rating User Saat Ini
            const [userRows] = await pool.execute('SELECT standard_rating FROM user_ratings WHERE user_id = ?', [userId]);

            // Handle jika user belum punya rating (User baru)
            let currentRating = 1200; // Default rating
            if (userRows.length > 0) {
                currentRating = userRows[0].standard_rating;
            }

            // 2. Query Agregat
            // Kita HAPUS bagian perhitungan 'Losses' di SQL untuk efisiensi parameter.
            // Cukup hitung Total, Win, dan Draw. Sisanya adalah Kalah.
            const sqlMatches = `
            SELECT 
                COUNT(*) as total,
                
                -- Hitung Menang (Wins)
                SUM(CASE 
                    WHEN (white_player_id = ? AND result = '1-0') THEN 1
                    WHEN (black_player_id = ? AND result = '0-1') THEN 1
                    ELSE 0 
                END) as wins,

                -- Hitung Seri (Draws)
                SUM(CASE 
                    WHEN result = '1/2-1/2' THEN 1
                    ELSE 0 
                END) as draws

            FROM matches 
            WHERE 
                (white_player_id = ? OR black_player_id = ?) 
                AND result IN ('1-0', '0-1', '1/2-1/2')
        `;

            // PERBAIKAN: Masukkan userId sebanyak 4 kali sesuai jumlah tanda tanya (?) di query di atas
            const [statsRows] = await pool.execute(sqlMatches, [userId, userId, userId, userId]);
            const row = statsRows[0];

            // 3. Konversi Data
            const wins = parseInt(row.wins) || 0;
            const draws = parseInt(row.draws) || 0;
            const total = parseInt(row.total) || 0;

            // Hitung Losses via Javascript (Lebih hemat resource DB)
            const losses = total - wins - draws;

            // 4. Hitung Rating Trend
            const ratingTrend = (wins * 10) - (losses * 8);

            // 5. Return Data
            return {
                totalGames: total,
                wins: wins,
                losses: losses,
                draws: draws,
                rating: currentRating,
                rankTitle: this.getRankTitle(currentRating),
                ratingTrend: ratingTrend
            };

        } catch (error) {
            logger.error("Error in getUserStats:", error); // Log error untuk debugging
            throw error;
        }
    }
}

module.exports = Statistic;