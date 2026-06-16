/**
 * file: backend-node/src/models/matchModel.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Model Database untuk Match. Menangani query CRUD, Logging Debug, dan Transaksi Undo.
 */

const pool = require('../config/database');
const logger = require('../utils/logger');
const EloService = require('../services/EloRatingService');

class MatchModel {



    /**
     * Menyimpan langkah baru ke tabel partisi 'match_moves'.
     * @param {object} moveData - Data langkah (san, uci, fen, time_spent, dll).
     * @returns {Promise<number>} ID insert.
     */
    static async saveMove(moveData) {
        const query = `
      INSERT INTO match_moves 
      (match_id, move_number, san, uci, fen_snapshot, time_spent, created_at)
      VALUES (?, ?, ?, ?, ?, ?, NOW())
    `;
        const values = [
            moveData.match_id,
            moveData.move_number,
            moveData.san,
            moveData.uci,
            moveData.fen_snapshot,
            moveData.time_spent
        ];

        try {
            const [result] = await pool.execute(query, values);
            return result.insertId;
        } catch (error) {
            logger.error(`[SQL ERROR] saveMove:`, error.message);
            throw error;
        }
    }

    /**
     * Mengupdate state pertandingan (FEN, Waktu, Timestamp).
     * @param {number} matchId - ID Match.
     * @param {string} newFen - FEN terbaru.
     * @param {number} whiteTime - Sisa waktu putih (ms).
     * @param {number} blackTime - Sisa waktu hitam (ms).
     * @returns {Promise<boolean>} True jika berhasil.
     */
    static async updateMatchState(matchId, newFen, whiteTime, blackTime) {
         logger.info(`[SQL DEBUG] updateMatchState -> MatchID: ${matchId} -> newFen : ${newFen} -> whiteTime : ${whiteTime} -> blackTime: ${blackTime}`); // Uncomment jika ingin log verbose

        const query = `
      UPDATE matches 
      SET fen = ?, 
          white_time_ms = ?, 
          black_time_ms = ?, 
          last_move_time = NOW(), 
          updated_at = NOW()
      WHERE id = ?
    `;

        try {
            const [result] = await pool.execute(query, [newFen, whiteTime, blackTime, matchId]);

            if (result.affectedRows === 0) {
                console.warn(`[SQL WARNING] Update match ID ${matchId} gagal. Data mungkin tidak ditemukan.`);
            }
            return result.affectedRows > 0;
        } catch (error) {
            logger.error(`[SQL ERROR] updateMatchState:`, error.message);
            throw error;
        }
    }

    /**
     * Menyelesaikan pertandingan (Game Over) dengan update PGN.
     * @param {number} matchId - ID Match.
     * @param {string} resultStr - Hasil ('1-0', '0-1', '1/2-1/2').
     * @param {string} reason - Alasan ('checkmate', 'timeout', etc).
     * @param {string} pgnString - Data PGN lengkap dari awal sampai akhir match.
     */
    static async finishMatch(matchId, resultStr, reason, pgnString) {
        logger.info(`[SQL DEBUG] finishMatch -> MatchID: ${matchId}, Result: ${resultStr}, Reason: ${reason}`);

        // Ambil koneksi dari pool untuk memulai transaksi
        const connection = await pool.getConnection();

        try {
            await connection.beginTransaction();

            // Update Tabel Matches: Menambahkan pgn_string ke dalam set update
            const query = `
                UPDATE matches 
                SET 
                    status = 'completed', 
                    result = ?, 
                    win_reason = ?, 
                    pgn_string = ?, 
                    end_time = NOW(), 
                    updated_at = NOW() 
                WHERE id = ?
            `;
            
            const [res] = await connection.execute(query, [resultStr, reason, pgnString, matchId]);

            if (res.affectedRows === 0) {
                throw new Error(`Gagal update finishMatch! ID ${matchId} tidak ditemukan.`);
            }

            logger.info(`[SQL INFO] Match metadata & PGN updated for ID: ${matchId}`);

            // Jalankan EloService (Update Rating & History Rating)
            // Koneksi dikirim agar berada dalam satu transaksi (Atomicity)
            await EloService.processUserRatingUpdate(matchId, connection);

            // Jika semua query berhasil tanpa error, commit ke DB
            await connection.commit();
            logger.info(`[SQL SUCCESS] Match ${matchId} fully completed. PGN, Ratings, and History saved.`);

        } catch (error) {
            // Rollback: Jika PGN gagal simpan atau Elo gagal update, 
            // status match akan kembali ke 'ongoing' (mencegah data korup)
            if (connection) await connection.rollback();
            logger.error(`[SQL ROLLBACK] finishMatch Error:`, error.message);
            throw error;
        } finally {
            // Kembalikan koneksi ke pool agar bisa digunakan request lain
            if (connection) connection.release();
        }
    }

    /**
     * Membatalkan langkah terakhir (Undo/Takeback) menggunakan Database Transaction.
     * Menghapus move terakhir, dan mengembalikan FEN matches ke state sebelumnya.
     * @param {number} matchId - ID Match.
     * @returns {Promise<object>} Objek berisi { success: true, newFen: string }.
     */
    static async undoLastMove(matchId) {
            const connection = await pool.getConnection();
            try {
                await connection.beginTransaction();

                // 1. Ambil info langkah terakhir
                const [lastMove] = await connection.execute(
                    `SELECT move_number FROM match_moves WHERE match_id = ? ORDER BY move_number DESC LIMIT 1`, [matchId]
                );

                if (lastMove.length === 0) {
                    throw new Error("Tidak ada langkah untuk di-undo (Board kosong).");
                }

                const currentMoveNum = lastMove[0].move_number;

                // 2. Hapus langkah terakhir dari tabel match_moves
                await connection.execute(
                    `DELETE FROM match_moves WHERE match_id = ? AND move_number = ?`, [matchId, currentMoveNum]
                );

                // 3. Cari FEN dari langkah SEBELUMNYA (Move N-1) untuk restore papan
                let prevFen = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'; // Default Start

                if (currentMoveNum > 1) {
                    const [prevMove] = await connection.execute(
                        `SELECT fen_snapshot FROM match_moves WHERE match_id = ? AND move_number = ?`, [matchId, currentMoveNum - 1]
                    );
                    if (prevMove.length > 0) {
                        prevFen = prevMove[0].fen_snapshot;
                    }
                }

                // 4. Update tabel Matches (Restore FEN)
                // Note: last_move_time di-reset ke NOW() agar timer jalan normal dari titik ini
                await connection.execute(
                    `UPDATE matches SET fen = ?, last_move_time = NOW(), updated_at = NOW() WHERE id = ?`, [prevFen, matchId]
                );

                await connection.commit();
                logger.info(`[SQL UNDO] Sukses undo match ${matchId} ke move ${currentMoveNum - 1}. FEN: ${prevFen}`);

                return {
                    success: true,
                    newFen: prevFen
                };

            } catch (error) {
                await connection.rollback();
                logger.error(`[SQL UNDO ERROR]`, error.message);
                throw error;
            } finally {
                connection.release();
            }
        }
        /**
         * Mengupdate hasil analisis Stockfish ke database.
         * PERBAIKAN: Menghapus kolom 'is_analyzed' yang tidak ada di tabel match_moves.
         * @param {number} moveId - ID dari tabel match_moves.
         * @param {number} evalScore - Skor evaluasi (centipawn).
         * @param {boolean} isBestMove - Apakah ini langkah terbaik?
         * @param {string} category - 'blunder', 'mistake', 'good', 'best'.
         */
    static async updateMoveAnalysis(moveId, evalScore, isBestMove, category) {
            const query = `
      UPDATE match_moves 
      SET stockfish_eval = ?, 
          is_best_move = ?, 
          move_category = ?
      WHERE id = ?
    `;

            // HAPUS BARIS: is_analyzed = 1
            // Keberadaan stockfish_eval sudah cukup menandakan move ini sudah dianalisis.

            try {
                await pool.execute(query, [evalScore, isBestMove, category, moveId]);
            } catch (error) {
                logger.error(`[SQL ERROR] updateMoveAnalysis (ID: ${moveId}):`, error.message);
                // Jangan throw error agar worker tidak crash total, cukup log saja
            }
        }
        /**
         * Membuat record pertandingan baru di database.
         * @param {number} whiteId - ID Pemain Putih.
         * @param {number} blackId - ID Pemain Hitam.
         * @param {number} baseTime - Waktu dasar (detik).
         * @param {number} increment - Increment (detik).
         * @returns {Promise<number>} ID Match baru.
         */
    static async createMatch(whiteId, blackId, baseTime, increment = 0) {
            const startFen = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
            logger.info(whiteId,blackId);
            // Konversi detik ke milidetik untuk timer server
            const timeMs = baseTime * 1000;

            const query = `
      INSERT INTO matches 
      (white_player_id, black_player_id, status, fen, player_timer,white_time_ms, black_time_ms, created_at, last_move_time)
      VALUES (?, ?, 'active', ?, ?,?, ?, NOW(), NOW())
    `;

            // Casual match tidak punya tournament_id (NULL)
            const [res] = await pool.execute(query, [whiteId, blackId, startFen, timeMs, timeMs, timeMs]);
            return res.insertId;
        }
        /**
         * Membuat record pertandingan baru di database.
         * @param {number} whiteId - ID Pemain Putih.
         * @param {number} blackId - ID Pemain Hitam.
         * @param {number} baseTime - Waktu dasar (detik).
         * @param {number} increment - Increment (detik).
         * @returns {Promise<number>} ID Match baru.
         */
    static async create(data) {
            try {
                const {
                    tournamentId,
                    whiteId,
                    blackId,
                    whiteTime,
                    blackTime
                } = data;

                const query = `
            INSERT INTO matches 
            (tournament_id, white_player_id, black_player_id, white_time_ms, black_time_ms, 
             status, fen, start_time, created_at, result)
            VALUES (?, ?, ?, ?, ?, 'pending_start', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', NOW(), NOW(), 'ongoing')
        `;

                const [result] = await pool.execute(query, [
                    tournamentId || null, whiteId, blackId, whiteTime, blackTime
                ]);

                logger.info("Match Created, ID:", result.insertId);

                // PERBAIKAN 1: Return di dalam try, saat data 'result' tersedia
                return result.insertId;

            } catch (err) {
                logger.error("SQL Error:", err);
                // PERBAIKAN 2: Lempar error ke Controller/Service pemanggil
                // Jangan return undefined, biar Controller tau proses gagal
                throw err;
            }
        }
        /**
         * Mengambil data match lengkap dengan NAMA dan RATING pemain.
         * FIX: Join ke tabel user_ratings.
         */
    static async getMatchById(matchId) {
            const query = `
      SELECT 
        m.id, m.tournament_id, m.white_player_id, m.black_player_id, 
        m.fen, m.status, m.result,m.player_timer, m.white_time_ms, m.black_time_ms, 
        m.last_move_time,
        t.time_control_base, t.time_control_increment,
        
        -- Info User Putih
        uw.username AS white_username,
        uw.avatar_url AS white_avatar,
        COALESCE(urw.rapid_rating, 1200) AS white_rating, -- Ambil dari user_ratings, default 1200
        
        -- Info User Hitam
        ub.username AS black_username,
        ub.avatar_url AS black_avatar,
        COALESCE(urb.rapid_rating, 1200) AS black_rating -- Ambil dari user_ratings, default 1200

      FROM matches m
      LEFT JOIN tournaments t ON m.tournament_id = t.id
      
      -- Join ke Users (Untuk Nama & Avatar)
      LEFT JOIN users uw ON m.white_player_id = uw.id
      LEFT JOIN users ub ON m.black_player_id = ub.id
      
      -- Join ke User Ratings (Untuk Rating)
      LEFT JOIN user_ratings urw ON m.white_player_id = urw.user_id
      LEFT JOIN user_ratings urb ON m.black_player_id = urb.user_id
      
      WHERE m.id = ?
    `;

            try {
                const [rows] = await pool.execute(query, [matchId]);
                return rows.length > 0 ? rows[0] : null;
            } catch (error) {
                logger.error(`[SQL ERROR] getMatchById:`, error.message);
                throw error;
            }
        }
        /**
         * Mengambil data match by User.
         * FIX: Join ke tabel user_ratings.
         */

    static async findActiveByUserId(userId) {
        // Query untuk mencari match yang statusnya 'active' 
        // dan user tersebut adalah salah satu pemainnya.
        const query = `
        SELECT m.*, 
               w.username as white_username, w.avatar_url as white_avatar,
               b.username as black_username, b.avatar_url as black_avatar
        FROM matches m
        JOIN users w ON m.white_player_id = w.id
        JOIN users b ON m.black_player_id = b.id
        WHERE (m.white_player_id = ? OR m.black_player_id = ?)
          AND m.status = 'active' OR m.status = 'ongoing'
        LIMIT 1
    `;


        const [rows] = await pool.execute(query, [userId, userId]);
        return rows[0] || null; // Return match pertama atau null
    }
    static async setWinnerWO(matchId, winnerId, reason) {
        // Jika aborted (dua-duanya tidak terima), result aborted
        // Jika ada winner (salah satu terima), result 1-0 atau 0-1
        let resultStr = 'aborted';

        // Logic menentukan string result berdasarkan siapa yang menang
        if (winnerId) {
            // Kita butuh tau winner itu white atau black, anggap logic ini ada di service
            // Disini kita update status saja
            // (Query disederhanakan, logic detail ada di Service)
        }

        const query = `
            UPDATE matches 
            SET status = 'completed', 
                result = ?, 
                win_reason = ?, 
                end_time = NOW() 
            WHERE id = ?
        `;
        // ... execute query
    }

    /**
     * Mengambil riwayat pertandingan yang sudah selesai untuk user tertentu.
     * FIX: Join ke user_ratings untuk ambil rating, hapus kolom rating dari tabel matches.
     */
    static async getHistoryByUserId(userId) {
        const query = `
        SELECT
            match_id AS id,
            result,
            win_reason,
            fen_final,
            created_at,
            player_timer,

            -- USER
            CASE
                WHEN white_player_id = ? THEN 'white'
                ELSE 'black'
            END AS user_role,

            CASE
                WHEN white_player_id = ? THEN white_rating
                ELSE black_rating
            END AS my_rating,

            -- LAWAN
            CASE
                WHEN white_player_id = ? THEN black_username
                ELSE white_username
            END AS opponent_username,

            CASE
                WHEN white_player_id = ? THEN black_avatar
                ELSE white_avatar
            END AS opponent_avatar,

            CASE
                WHEN white_player_id = ? THEN black_rating
                ELSE white_rating
            END AS opponent_rating

        FROM view_match_history
        WHERE
            white_player_id = ?
            OR black_player_id = ?
        ORDER BY match_id DESC
    `;

        try {
            const [rows] = await pool.execute(query, [
                userId,
                userId,
                userId,
                userId,
                userId,
                userId,
                userId
            ]);

            // Mapping SANGAT MINIMAL
            const processedHistory = rows.map(row => {
                const timeInMinutes = row.player_timer ?
                    Math.floor(row.player_timer / 60000) :
                    0;

                return {
                    id: row.id,
                    user_role: row.user_role,
                    result: row.result,
                    win_reason: row.win_reason,
                    fen_final: row.fen_final,
                    date: row.created_at,
                    time_control_label: timeInMinutes > 0 ? `${timeInMinutes} min` : 'Custom',

                    opponent_username: row.opponent_username,
                    opponent_avatar: row.opponent_avatar,
                    opponent_rating: row.opponent_rating,

                    my_rating: row.my_rating
                };
            });

            return processedHistory;
        } catch (err) {
            logger.error('[SQL ERROR] getHistoryByUserId:', err);
            throw err;
        }
    }
    static async getLiveMatches() {
        // Mengambil match yang sedang 'active' atau 'waiting'
        const sql = `
            SELECT 
                m.id, m.status, m.fen as current_fen, m.start_time,
                u1.username AS white_player,
                u2.username AS black_player
            FROM matches m
            JOIN users u1 ON m.white_player_id = u1.id
            JOIN users u2 ON m.black_player_id = u2.id
            WHERE m.status IN ('active', 'waiting')
            ORDER BY m.start_time DESC
        `;
        const [rows] = await pool.execute(sql);
        return rows;
    }
    static async updateStatus(matchId, status) {
        await pool.execute("UPDATE matches SET status = ? WHERE id = ?", [status, matchId]);
    }
}

module.exports = MatchModel;