/**
 * file: backend-node/src/models/tournamentModel.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Model database untuk manajemen Turnamen dan Peserta.
 */

const pool = require('../config/database');
const logger = require('../utils/logger');

class TournamentModel {

  /**
   * Mengambil detail turnamen (Status & Biaya).
   * @param {number} id 
   */
 static async findById(id) {
      const query = `
        SELECT 
          id, 
          title, 
          status, 
          entry_fee, 
          format,
          time_control_type,
          time_control_base,
          time_control_increment,
          (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = tournaments.id) as current_participants_count
        FROM tournaments 
        WHERE id = ?
      `;

      try {
          const [rows] = await pool.execute(query, [id]);
          
          // Mengembalikan objek tunggal jika ditemukan, jika tidak null
          return rows.length > 0 ? rows[0] : null;
      } catch (error) {
          logger.error("Error in TournamentModel.findById:", error);
          throw error;
      }
  }

  /**
   * Cek apakah user sudah terdaftar di turnamen ini.
   */
  static async isParticipant(tournamentId, userId) {
    const [rows] = await pool.execute(
      `SELECT id FROM tournament_participants WHERE tournament_id = ? AND user_id = ?`,
      [tournamentId, userId]
    );
    return rows.length > 0;
  }

  /**
   * Menghitung jumlah peserta saat ini.
   */
  static async countParticipants(tournamentId) {
    const [rows] = await pool.execute(
      `SELECT COUNT(*) as total FROM tournament_participants WHERE tournament_id = ?`,
      [tournamentId]
    );
    return rows[0].total;
  }
  static async getOpenTournaments1() {
    return  await pool.execute(
      `SELECT id, title, description, start_time, format, 
               time_control_base, time_control_increment, entry_fee, status
        FROM tournaments 
        WHERE status IN ('registration', 'active','waiting', 'active')
        ORDER BY start_time DESC`
    );
     
  }
  static async getOpenTournaments(userId) {
    try {
       return  await pool.execute(`
            SELECT 
                t.*,
                (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id) as total_participants,
                IF(tp.id IS NOT NULL, 1, 0) as isJoined
            FROM tournaments t
            LEFT JOIN tournament_participants tp ON tp.tournament_id = t.id AND tp.user_id = ?
            WHERE t.status IN ('registration', 'active','waiting', 'active')
            ORDER BY t.start_time ASC
        `, [userId]);

        
    } catch (error) {
        logger.info(error);
    }
  }
  /**
     * Mengambil daftar peserta dengan rating dinamis berdasarkan tipe kontrol waktu
     */
    static async getParticipantsWithRating(tournamentId, ratingType) {
        // Tentukan kolom rating (standard_rating, rapid_rating, blitz_rating, bullet_rating)
        const ratingCol = `${ratingType}_rating`;

        const [rows] = await pool.execute(`
            SELECT 
                tp.user_id as id,
                u.username,
                u.full_name,
                u.avatar_url,
                'ok' as join_status,
                COALESCE(ur.${ratingCol}, 1200) as current_rating
                FROM tournament_participants tp
                JOIN users u ON u.id = tp.user_id
                LEFT JOIN user_ratings ur ON ur.user_id = tp.user_id
                WHERE tp.tournament_id = ?
                ORDER BY current_rating DESC
            `, [tournamentId]);
            return rows;
    }
    /**
     * Menghitung jumlah turnamen yang sedang terbuka/aktif.
     */
    static async getOpenTournamentsCount() {
      console.log(`[DB_MODEL] Memulai perhitungan jumlah turnamen terbuka...`);

      const query = `
        SELECT COUNT(*) as total 
        FROM tournaments 
        WHERE status IN ('registration', 'active', 'waiting', 'active')
      `;

      try {
        const [rows] = await pool.execute(query);
        const count = rows[0].total;
        
        console.log(`[DB_MODEL] Berhasil menghitung. Total turnamen terbuka: ${count}`);
        return count;
      } catch (error) {
        console.error(`[DB_MODEL] Error pada getOpenTournamentsCount: ${error.message}`);
        throw error;
      }
    }
}

module.exports = TournamentModel;
