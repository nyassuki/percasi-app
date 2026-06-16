const pool = require('../config/database');
const logger = require('../utils/logger');

class EloRatingService {
    /**
     * Memproses rating dan statistik ke tabel user_ratings
     * @param {number} matchId - ID Pertandingan
     * @param {object} externalConnection - (Optional) Koneksi transaksi dari fungsi pemanggil
     */
    static async processUserRatingUpdate(matchId, externalConnection = null) {
        const connection = externalConnection || await pool.getConnection();
        const isInternalTransaction = !externalConnection;

        try {
            logger.info(`[EloService] 🚀 Memulai update rating untuk MatchID: ${matchId}`);

            if (isInternalTransaction) {
                await connection.beginTransaction();
                logger.info(`[EloService] 💡 Transaksi internal dimulai`);
            }

            // 1. Ambil data match dan rating kategori saat ini
            const [matchData] = await connection.execute(`
                SELECT m.result, m.white_player_id, m.black_player_id, m.player_timer,
                       ur1.standard_rating as w_std, ur1.rapid_rating as w_rap, ur1.blitz_rating as w_blz, ur1.bullet_rating as w_bul,
                       ur2.standard_rating as b_std, ur2.rapid_rating as b_rap, ur2.blitz_rating as b_blz, ur2.bullet_rating as b_bul
                FROM matches m
                LEFT JOIN user_ratings ur1 ON m.white_player_id = ur1.user_id
                LEFT JOIN user_ratings ur2 ON m.black_player_id = ur2.user_id
                WHERE m.id = ?`, [matchId]);

            if (!matchData.length) {
                throw new Error(`Match data untuk ID ${matchId} tidak ditemukan`);
            }
            
            const m = matchData[0];
            logger.info(`[EloService] 📊 Data Match ditemukan: Putih(${m.white_player_id}), Hitam(${m.black_player_id}), Result(${m.result})`);

            // 2. Tentukan Kategori Rating
            let ratingType;
            if (m.player_timer < 3) ratingType = 'bullet';
            else if (m.player_timer < 10) ratingType = 'blitz';
            else if (m.player_timer < 60) ratingType = 'rapid';
            else ratingType = 'standard';

            const ratingCol = `${ratingType}_rating`;
            const prefix = ratingType.substring(0, 3);
            
            const wOld = m[`w_${prefix}`] || 1200;
            const bOld = m[`b_${prefix}`] || 1200;

            logger.info(`[EloService] 🕒 Kategori: ${ratingType.toUpperCase()} (${m.player_timer}m). Rating Awal -> Putih: ${wOld}, Hitam: ${bOld}`);

            // 3. Hitung Jumlah Match (N) untuk K-Factor
            const [wCountRes] = await connection.execute(
                "SELECT COUNT(*) as count FROM matches WHERE (white_player_id = ? OR black_player_id = ?) AND result IN ('1-0', '0-1', '1/2-1/2') AND status = 'completed'", 
                [m.white_player_id, m.white_player_id]
            );
            const [bCountRes] = await connection.execute(
                "SELECT COUNT(*) as count FROM matches WHERE (white_player_id = ? OR black_player_id = ?) AND result IN ('1-0', '0-1', '1/2-1/2') AND status = 'completed'", 
                [m.black_player_id, m.black_player_id]
            );

            const wCount = wCountRes[0].count;
            const bCount = bCountRes[0].count;

            // 4. Logika K-Factor
            const calculateK = (n, rating) => {
                if (n < 30) return 40;
                if (rating >= 2400) return 10;
                return 20;
            };

            const Kw = calculateK(wCount, wOld);
            const Kb = calculateK(bCount, bOld);
            
            logger.info(`[EloService] 📉 K-Factor Check -> Putih: K=${Kw} (Games:${wCount}), Hitam: K=${Kb} (Games:${bCount})`);

            // 5. Tentukan Skor Aktual (S)
            let Sw, Sb, wStat, bStat; 
            if (m.result === '1-0') { 
                Sw = 1; Sb = 0; wStat = 'wins'; bStat = 'losses';
            } else if (m.result === '0-1') { 
                Sw = 0; Sb = 1; wStat = 'losses'; bStat = 'wins';
            } else { 
                Sw = 0.5; Sb = 0.5; wStat = 'draws'; bStat = 'draws';
            }

            // 6. Hitung Elo Baru
            const Ew = 1 / (1 + Math.pow(10, (bOld - wOld) / 400));
            const Eb = 1 / (1 + Math.pow(10, (wOld - bOld) / 400));

            const wNew = Math.round(wOld + Kw * (Sw - Ew));
            const bNew = Math.round(bOld + Kb * (Sb - Eb));

            logger.info(`[EloService] 🔢 Hasil Kalkulasi -> Putih: ${wNew} (${wNew - wOld >= 0 ? '+' : ''}${wNew - wOld}), Hitam: ${bNew} (${bNew - bOld >= 0 ? '+' : ''}${bNew - bOld})`);

            // 7. Simpan Update Rating (UPSERT)
            const upsertElo = async (userId, newRating, statCol) => {
                const sql = `
                    INSERT INTO user_ratings (user_id, ${ratingCol}, ${statCol})
                    VALUES (?, ?, 1)
                    ON DUPLICATE KEY UPDATE 
                        ${ratingCol} = ?, 
                        ${statCol} = ${statCol} + 1,
                        updated_at = CURRENT_TIMESTAMP
                `;
                return connection.execute(sql, [userId, newRating, newRating]);
            };

            await upsertElo(m.white_player_id, wNew, wStat);
            await upsertElo(m.black_player_id, bNew, bStat);
            logger.info(`[EloService] ✅ Tabel user_ratings berhasil diupdate`);

            // 8. Simpan ke rating_history
            const historyQuery = `
                INSERT INTO rating_history (user_id, match_id, rating_type, old_rating, new_rating, rating_diff)
                VALUES (?, ?, ?, ?, ?, ?)`;

            await connection.execute(historyQuery, [m.white_player_id, matchId, ratingType, wOld, wNew, (wNew - wOld)]);
            await connection.execute(historyQuery, [m.black_player_id, matchId, ratingType, bOld, bNew, (bNew - bOld)]);
            logger.info(`[EloService] ✅ Riwayat rating disimpan ke rating_history`);

            // 9. Finalisasi
            if (isInternalTransaction) {
                await connection.commit();
                logger.info(`[EloService] ✨ Transaksi Berhasil dan Di-commit`);
            }

            return { ratingType, white: { wOld, wNew }, black: { bOld, bNew } };

        } catch (error) {
            if (isInternalTransaction && connection) {
                await connection.rollback();
                logger.error(`[EloService ❌ ROLLBACK] Terjadi kesalahan: ${error.message}`);
            }
            throw error;
        } finally {
            if (isInternalTransaction && connection) {
                connection.release();
                logger.info(`[EloService] 🔌 Koneksi dikembalikan ke pool`);
            }
        }
    }
    /**
     * Mengambil riwayat rating untuk keperluan grafik
     * @param {number} userId - ID User
     * @param {string} type - standard, rapid, blitz, atau bullet
     * @param {number} limit - Jumlah match terakhir (default 50)
     */
    static async getRatingProgress(userId, type, limit = 50) {
        try {
            logger.info(`[EloService] Fetching rating progress for User: ${userId}, Type: ${type}`);
            
            const [rows] = await pool.execute(`
                SELECT 
                    new_rating as rating, 
                    rating_diff as diff,
                    created_at as date 
                FROM rating_history 
                WHERE user_id = ? AND rating_type = ? 
                ORDER BY created_at ASC 
                LIMIT ?
            `, [userId, type, limit]);

            // Format data agar siap digunakan oleh library grafik di frontend
            return rows.map(row => ({
                rating: row.rating,
                diff: row.diff,
                label: row.date // Anda bisa memformat tanggal ini menggunakan library moment atau dayjs
            }));
        } catch (error) {
            logger.error(`[EloService] Error fetching progress: ${error.message}`);
            throw error;
        }
    }
    /**
     * Mendapatkan peringkat pemain di kategori tertentu
     * @param {number} userId
     * @param {string} type - standard, rapid, blitz, bullet
     */
    static async getUserRank(userId, type = 'blitz') {
        const ratingCol = `${type}_rating`;
        
        const query = `
            SELECT global_rank, total_players
            FROM (
                SELECT 
                    user_id,
                    RANK() OVER (ORDER BY ${ratingCol} DESC) as global_rank,
                    COUNT(*) OVER () as total_players
                FROM v_leaderboard
            ) AS ranking_table
            WHERE user_id = ?
        `;

        try {
            const [rows] = await pool.execute(query, [userId]);
            if (rows.length > 0) {
                logger.info(`[Rank] User ${userId} is rank #${rows[0].global_rank} of ${rows[0].total_players} in ${type}`);
                return rows[0];
            }
            return null;
        } catch (error) {
            logger.error(`[Rank Error] ${error.message}`);
            throw error;
        }
    }
    /**
     * Inisialisasi Rating User Baru
     * Mendukung sistem Connection untuk Transaksi Pendaftaran
     */
    static async newUserRatings(newUserId, connection = null) {
        // Gunakan connection transaksi jika ada, jika tidak gunakan pool
        const client = connection || pool;

        try {
            logger.info(`[RATINGS_DEBUG] Initializing ratings for UserID: ${newUserId}`);

            // 1. Ambil Default Rating dari Settings
            const settings = await MasterModel.getSettings();
            
            // Gunakan default 1200 jika setting tidak ditemukan atau error
            const defaultRating = (settings && settings.length > 0) 
                ? settings[0]['new_user_rating'] 
                : 1200;

            // 2. Query Insert
            // PERBAIKAN: Ditambah 1 tanda tanya (?) dan koma sebelum NOW()
            const query = `
                INSERT INTO user_ratings 
                (user_id, standard_rating, rapid_rating, blitz_rating, bullet_rating, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            `;

            const params = [
                newUserId, 
                defaultRating, // standard
                defaultRating, // rapid
                defaultRating, // blitz
                defaultRating  // bullet
            ];

            await client.execute(query, params);
            
            logger.info(`[RATINGS_SUCCESS] Initial ratings set to ${defaultRating} for UserID: ${newUserId}`);
            return true;

        } catch (error) {
            logger.error(`[RATINGS_ERROR] Failed to initialize ratings:`, error.message);
            throw error; // Lempar error agar pendaftaran user bisa di-rollback
        }
    }
}

module.exports = EloRatingService;