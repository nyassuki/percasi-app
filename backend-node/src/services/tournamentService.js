/**
 * file: backend-node/src/services/tournamentService.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Service untuk menangani logika turnamen: Update Skor, Pairing Swiss, dll.
 */

const pool = require('../config/database');
const TransferService = require('../services/TransferService');
const Wallet = require('../models/Wallet');
const Transaction = require('../models/Transaction');
const logger = require('../utils/logger');

class TournamentService {

    /**
     * Mengupdate skor partisipan setelah pertandingan selesai.
     * @param {number} tournamentId - ID Turnamen.
     * @param {number} whiteId - ID Pemain Putih.
     * @param {number} blackId - ID Pemain Hitam.
     * @param {string} result - Hasil match ('1-0', '0-1', '1/2-1/2').
     */
    static async updateStandings(tournamentId, whiteId, blackId, result) {
            if (!tournamentId) return; // Abaikan jika friendly match (bukan turnamen)

            let whitePoints = 0;
            let blackPoints = 0;

            // Tentukan pembagian poin
            if (result === '1-0') {
                whitePoints = 1;
                blackPoints = 0;
            } else if (result === '0-1') {
                whitePoints = 0;
                blackPoints = 1;
            } else if (result === '1/2-1/2') {
                whitePoints = 0.5;
                blackPoints = 0.5;
            } else {
                return; // Result aborted/unknown, tidak ada poin
            }

            // Query untuk update skor (Gunakan atomic update: score = score + x)
            const updateQuery = `
      UPDATE tournament_participants 
      SET current_score = current_score + ?, 
          updated_at = NOW()
      WHERE tournament_id = ? AND user_id = ?
    `;

            // Jalankan update paralel
            await Promise.all([
                pool.execute(updateQuery, [whitePoints, tournamentId, whiteId]),
                pool.execute(updateQuery, [blackPoints, tournamentId, blackId])
            ]);

            logger.info(`[TOURNAMENT] Score updated for T#${tournamentId}. White:+${whitePoints}, Black:+${blackPoints}`);
        }
        /**
         * Mendaftarkan user ke turnamen (Potong Saldo + Insert Peserta).
         * @param {number} userId 
         * @param {number} tournamentId 
         */
    static async joinTournament(userId, tournamentId) {
        const connection = await pool.getConnection();
        let errorToThrow = null;

        logger.info(`[JOIN_PROCESS] Starting join request for UserID: ${userId} to TournamentID: ${tournamentId}`);

        try {
            await connection.beginTransaction();
            logger.info(`[DB_TX] Transaction started.`);

            // 1. Validasi Turnamen & Locking
            logger.info(`[STEP 1] Validating tournament existence and status...`);
            const [tourneyRows] = await connection.execute(
                `SELECT id, title, status, entry_fee, tournament_identification 
             FROM tournaments WHERE id = ? FOR UPDATE`, [tournamentId]
            );

            if (tourneyRows.length === 0) {
                logger.error(`[STEP 1 ERROR] Tournament ${tournamentId} not found.`);
                throw new Error('Turnamen tidak ditemukan.');
            }

            const tournament = tourneyRows[0];
            logger.info(`[STEP 1 OK] Tournament found: "${tournament.title}". Status: ${tournament.status}`);

            if (tournament.status !== 'registration') {
                console.warn(`[STEP 1 WARN] Join failed. Status is ${tournament.status}, expected "registration".`);
                throw new Error('Pendaftaran turnamen ini sudah ditutup.');
            }

            // 2. Cek Bentrok Jadwal
            logger.info(`[STEP 2] Checking for schedule conflicts (ID: ${tournament.tournament_identification})...`);
            const [conflictCheck] = await connection.execute(`
            SELECT t.title 
            FROM tournament_participants tp
            JOIN tournaments t ON tp.tournament_id = t.id
            WHERE tp.user_id = ? AND t.tournament_identification = ?
        `, [userId, tournament.tournament_identification]);

            if (conflictCheck.length > 0) {
                console.warn(`[STEP 2 CONFLICT] User ${userId} already joined "${conflictCheck[0].title}" in the same slot.`);
                const error = new Error(`Jadwal bentrok dengan turnamen "${conflictCheck[0].title}"`);
                error.type = 'JADWAL_BENTROK';
                throw error;
            }
            logger.info(`[STEP 2 OK] No schedule conflicts found.`);

            // 3. Proses Pembayaran
            const entryFee = parseFloat(tournament.entry_fee);
            if (entryFee > 0) {
                logger.info(`[STEP 3] Processing payment. Entry Fee: ${entryFee}`);

                const [walletRows] = await connection.execute(
                    `SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE`, [userId]
                );

                if (walletRows.length === 0) {
                    logger.error(`[STEP 3 ERROR] Wallet for User ${userId} not found.`);
                    throw new Error('Dompet tidak ditemukan.');
                }

                const currentBalance = parseFloat(walletRows[0].balance);
                logger.info(`[STEP 3] Current Balance: ${currentBalance}`);

                if (currentBalance < entryFee) {
                    console.warn(`[STEP 3 WARN] Insufficient funds. Need: ${entryFee}, Have: ${currentBalance}`);
                    const error = new Error('Saldo Anda tidak mencukupi.');
                    error.type = 'INSUFFICIENT_BALANCE';
                    throw error;
                }

                // Update Saldo
                const newBalance = await Wallet.updateWalletBalance(userId, entryFee,'OUT',connection);
                logger.info(`[STEP 3 OK] Balance updated. New Balance: ${newBalance}`);
                const kode_transaksi = await Transaction.generateTransactionCode();
                // Log Transaksi
                const sender_log = {
                    userId: userId,
                    kode_transaksi:kode_transaksi,
                    type: 'tournament_fee',
                    flow: 'out',
                    amount: entryFee,
                    balance: newBalance,
                    note: `Tournament Fee: ${tournament.title}`
                };
                await Transaction.logP2PTransaction(sender_log);
                logger.info(`[STEP 3 OK] Transaction log created.`);
            } else {
                logger.info(`[STEP 3] Tournament is FREE. Skipping payment.`);
            }

            // 4. Insert Participant
            logger.info(`[STEP 4] Registering user as participant...`);
            await connection.execute(`
            INSERT INTO tournament_participants (tournament_id, user_id, current_score, created_at)
            VALUES (?, ?, 0, NOW())`, [tournamentId, userId]);
            logger.info(`[STEP 4 OK] User registered in tournament_participants.`);

            // Commit
            await connection.commit();
            logger.info(`[DB_TX] COMMIT successful.`);

            // Notifikasi (Lazy load to break circular dependency)
            try {
                const NotificationService = require('./notificationService');
                NotificationService.sendToUser(userId, 'Sukses!', `Terdaftar di ${tournament.title}`, 'success');
                logger.info(`[NOTIFICATION] Success notification sent to user.`);
            } catch (notifErr) {
                logger.error(`[NOTIFICATION ERROR] Failed to send notification: ${notifErr.message}`);
            }

            return {
                status: 'success',
                message: `Berhasil bergabung di ${tournament.title}`,
                data: {
                    entryFee
                }
            };

        } catch (error) {
            errorToThrow = error;
            logger.error(`[FATAL ERROR] Rollback initiated. Reason: ${error}`);
            if (connection) await connection.rollback();
        } finally {
            if (connection) {
                connection.release();
                logger.info(`[DB_CONN] Connection released back to pool.`);
            }
            if (errorToThrow) throw errorToThrow;
        }
    }
    /**
     * Logika utama untuk update status (igunakan oleh cron)
     */
    static async updateTournamentStatuses() {
        try {
            // Query: Ambil turnamen yang statusnya 'registration' 
            // dan registration_close <= waktu sekarang
            const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
            
            // 1. Cari turnamen yang perlu diupdate
            // Logika: status 'registration' AND registration_close <= NOW()
            const sql = `
                UPDATE  tournaments 
                SET status = 'waiting', updated_at = CURRENT_TIMESTAMP
                WHERE status = 'registration' 
                AND registration_close <= ?`;

            // Eksekusi query (Contoh menggunakan mysql2 atau knex)
            const [result] = await pool.execute(sql, [now]);

            if (result.affectedRows > 0) {
                logger.info(`[CRON_SUCCESS] ${result.affectedRows} tournament(s) updated to 'waiting'.`);
            }

        } catch (error) {
            logger.error('[CRON_ERROR] Failed to update tournament statuses:', error.message);
        }
    }
}

module.exports = TournamentService;