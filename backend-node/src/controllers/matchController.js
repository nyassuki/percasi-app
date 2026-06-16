/**
 * file: backend-node/src/controllers/matchController.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Controller untuk menangani request HTTP terkait pertandingan.
 */

const ChessService = require('../services/chessService');
const EloService = require('../services/EloRatingService');
const MatchmakingService = require('../services/matchmakingService');
const MatchModel = require('../models/matchModel');
const logger = require('../utils/logger');

class MatchController {

    /**
     * Menangani langkah catur.
     * POST /api/matches/move
     */
    static async makeMove(req, res) {
        try {
            const {
                matchId,
                uciMove
            } = req.body;

            if (!matchId || !uciMove) {
                return res.status(400).json({
                    status: 'error',
                    message: 'matchId dan uciMove wajib diisi.'
                });
            }

            const result = await ChessService.processMove(matchId, uciMove);

            req.io.to(`match_${matchId}`).emit('updateMatch', {
                matchId,
                fen: newFen,
                lastMove: move,
                turn: chess.turn()
            });
                    
            return res.status(200).json({
                status: 'success',
                data: result
            });

        } catch (error) {
            logger.error('Error in makeMove:', error.message);
            return res.status(400).json({
                status: 'error',
                message: error.message
            });
        }
    }

    /**
     * User klik "Play Now" / "Find Match"
     * POST /api/matches/casual/join
     */
    static async joinCasualQueue(req, res) {
        try {
            const userId = req.user.id;

            await MatchmakingService.joinQueue(userId);
            logger.info('Sedang mencari lawan');
            return res.status(200).json({
                status: 'success',
                message: 'Mencari lawan...'
            });
        } catch (error) {
            logger.error('joinCasualQueue:', error);
            return res.status(500).json({
                status: 'error',
                message: error.message
            });
        }
    }

    /**
     * User klik "Cancel".
     * POST /api/matches/casual/leave
     */
    static async leaveCasualQueue(req, res) {
        try {
            const userId = req.user.id;

            await MatchmakingService.leaveQueue(userId);

            return res.status(200).json({
                status: 'success',
                message: 'Batal mencari lawan.'
            });
        } catch (error) {
            logger.error('leaveCasualQueue:', error);
            return res.status(500).json({
                status: 'error',
                message: error.message
            });
        }
    }

    /**
     * Mengambil detail match untuk Game Arena.
     * GET /api/matches/:id
     */
    static async getMatchDetail(req, res) {
        try {
            const matchId = req.params.id;
            const userId = req.user.id;

            const match = await MatchModel.getMatchById(matchId);

            if (!match) {
                return res.status(404).json({
                    status: 'error',
                    message: 'Match tidak ditemukan.'
                });
            }

            // Tentukan role user
            let role = 'spectator';
            if (match.white_player_id === userId) role = 'white';
            else if (match.black_player_id === userId) role = 'black';

            return res.status(200).json({
                status: 'success',
                data: {
                    ...match,
                    user_role: role
                }
            });

        } catch (error) {
            logger.error('getMatchDetail:', error);
            return res.status(500).json({
                status: 'error',
                message: 'Server Error'
            });
        }
    }

    /**
     * Mencari match aktif user.
     * GET /api/matches/active
     */
    static async getActiveMatch(req, res) {
        try {
            const userId = req.user.id;

            const match = await MatchModel.findActiveByUserId(userId);

            return res.status(200).json({
                status: 'success',
                data: match || null
            });

        } catch (error) {
            logger.error('Get Active Match Error:', error);
            return res.status(500).json({
                status: 'error',
                message: 'Server Error'
            });
        }
    }
    static async getMatchHistory (req, res) {
        try {
            // Asumsi: Middleware auth sudah menaruh data user di req.user
            const currentUserId = req.user.id; 

            // 1. Ambil data mentah dari Database
            const rawMatches = await MatchModel.getHistoryByUserId(currentUserId);
            logger.info(rawMatches);

            // 2. Format data agar mudah dikonsumsi Frontend
            // Kita perlu menentukan 'user_role' (apakah user login sebagai white/black)
             

            // 3. Kirim Response
            return res.status(200).json({
                success: true,
                message: "Riwayat pertandingan berhasil diambil.",
                data: rawMatches
            });

        } catch (error) {
            logger.error("Error fetching match history:", error);
            return res.status(500).json({
                success: false,
                message: "Terjadi kesalahan server saat mengambil riwayat."
            });
        }
    }
    static async listLiveMatches(req, res) {
        try {
            const matches = await MatchModel.getLiveMatches();
            res.status(200).json({
                status: 'success',
                data: matches
            });
        } catch (error) {
            res.status(500).json({ status: 'error', message: error.message });
        }
    }
    static async getEloHistory (req, res) {
        try {
            const userId = req.user.id; // Diambil dari JWT token
            const ratingType = req.params.type; // e.g. 'rapid'
            
            const history = await EloService.getRatingProgress(userId, ratingType);
            
            res.json({
                status: 'success',
                data: history
            });
        } catch (error) {
            res.status(500).json({ status: 'error', message: error.message });
        }
    };
}

module.exports = MatchController;