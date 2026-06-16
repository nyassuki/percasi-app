/**
 * file: backend-node/src/controllers/tournamentController.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Controller untuk manajemen turnamen (Pairing, Standings, dll).
 */

const PairingService = require('../services/pairingService');
const TournamentService = require('../services/tournamentService');
const pool = require('../config/database');
const tournamentModel = require ('../models/tournamentModel');
const logger = require('../utils/logger');

class TournamentController {

    /**
     * API untuk memicu pembuatan pairing ronde baru.
     * Method: POST /api/tournaments/:id/pairings
     */
    static async createPairing(req, res) {
            try {
                const tournamentId = req.params.id;

                const result = await PairingService.generatePairings(tournamentId);

                res.status(200).json({
                    status: 'success',
                    message: `Pairing ronde ${result.round} berhasil dibuat.`,
                    data: result
                });

            } catch (error) {
                res.status(500).json({
                    status: 'error',
                    message: error.message
                });
            }
        }
        /**
         * User mendaftar ke turnamen.
         * POST /api/tournaments/:id/join
         */
    static async join(req, res) {
            try {
                const userId = req.user.id; // Dari Auth Middleware
                const tournamentId = req.params.id;
                const result = await TournamentService.joinTournament(userId, tournamentId);
                res.status(200).json(result);

            } catch (error) {
                // Handle error spesifik

                if (error.message.includes('Saldo')) {
                    return res.status(400).json({
                        status: 'error',
                        message: error.message,
                        code: 'INSUFFICIENT_BALANCE'
                    });
                }
                if (error.message.includes('Jadwal')) {
                    return res.status(400).json({
                        status: 'error',
                        message: error.message,
                        code: 'JADWAL_BENTROK'
                    });
                }
                res.status(500).json({
                    status: 'error',
                    message: error.message
                });
            }
        }
        /**
         * GET /api/tournaments/open
         * Mengambil daftar turnamen yang statusnya 'registration' atau 'active'.
         */
    static async getOpenTournaments(req, res) {
        try {
            const userId = req.user.id; // ID user yang sedang login
            const [rows] = await tournamentModel.getOpenTournaments(userId);            
 
            res.status(200).json({
                status: 'success',
                data: rows
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }
    static async getIsParticipant(req, res) {
        try {
            const userId = req.user.id; // Didapat dari authMiddleware
            const { tournament_id } = req.query; //

            const [rows] = await tournamentModel.isParticipant(tournament_id,userId);            
            logger.info(rows);

            res.status(200).json({
                status: 'success',
                data: rows
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }
    static async getLobbyData(req, res) {
        try {
            const tournamentId = req.params.id;
            const userId = req.user.id; // Diambil dari middleware auth

            // 1. Ambil data turnamen
            const tournament = await tournamentModel.findById(tournamentId);
            if (!tournament) {
                return res.status(404).json({ 
                    status: 'error', 
                    message: 'Turnamen tidak ditemukan.' 
                });
            }

            // 2. [Opsional] Cek apakah user sudah terdaftar untuk akses lobby
            const isJoined = await tournamentModel.isParticipant(tournamentId, userId);
            if (!isJoined && tournament.status !== 'active') {
                return res.status(403).json({ 
                    status: 'error', 
                    message: 'Anda belum bergabung ke turnamen ini.' 
                });
            }

            // 3. Ambil data peserta dengan rating sesuai tipe turnamen
            const participants = await tournamentModel.getParticipantsWithRating(
                tournamentId, 
                tournament.time_control_type
            );

            // 4. Kirim respon sukses
            res.status(200).json({
                status: 'success',
                data: {
                    tournament,
                    participants,
                    isJoined // Berikan status ini agar frontend bisa menyesuaikan UI
                }
            });

        } catch (error) {
            logger.error("Lobby Error:", error);
            res.status(500).json({
                status: 'error',
                message: 'Internal Server Error'
            });
        }
    }
}

module.exports = TournamentController;