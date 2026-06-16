/**
 * file: backend-node/src/services/chessService.js
 * description: Chess Logic Service - Integrasi Total: Elo, Anti-Cheat, PGN, & Redis.
 */

const { Chess } = require('chess.js');
const MatchModel = require('../models/matchModel');
const TournamentService = require('./tournamentService');
const StockfishService = require('./stockfishService');
const redis = require('../config/redis');
const logger = require('../utils/logger');

class ChessService {

    /**
     * Memproses langkah catur (PvP)
     */
    static async processMove(matchId, userId, rawMove) {
        const start = Date.now();
        logger.info(`[ChessService] 🟢 START Move | Match: ${matchId} | User: ${userId}`);

        // [STEP 1] Sanitasi Input (UCI Format)
        let uciMove = this._sanitizeMoveInput(rawMove);
        logger.info(`[ChessService] 🔹 Sanitized Move: ${uciMove}`);

        // [STEP 2] Load State (Redis -> DB)
        let matchData = await this._getMatchState(matchId);
        if (!matchData) throw new Error('Pertandingan tidak ditemukan.');
        if (matchData.status === 'completed') throw new Error('Pertandingan sudah selesai.');

        // Validasi Peserta
        const isWhite = String(matchData.white.id) === String(userId);
        const isBlack = String(matchData.black.id) === String(userId);
        if (!isWhite && !isBlack) throw new Error('Anda bukan peserta.');

        // [STEP 3] Validasi Logic Catur
        const chess = new Chess(matchData.fen);
        if ((chess.turn() === 'w' && !isWhite) || (chess.turn() === 'b' && !isBlack)) {
            throw new Error('Bukan giliran Anda.');
        }

        // Handle Auto-Queen Promotion
        const from = uciMove.slice(0, 2); 
        const to = uciMove.slice(2, 4); 
        let promotion = uciMove.length === 5 ? uciMove[4] : undefined;
        const piece = chess.get(from);
        if (piece?.type === 'p' && (to[1] === '8' || to[1] === '1') && !promotion) {
            promotion = 'q';
            logger.info(`[ChessService] ℹ️ Auto-Queen Applied`);
        }

        const move = chess.move({ from, to, promotion });
        if (!move) throw new Error(`Langkah ilegal: ${uciMove}`);

        // [STEP 4] Kalkulasi Waktu & Increment
        const now = Date.now();
        const timeSpent = now - (matchData.lastMoveTime || now);
        const increment = (matchData.increment || 0) * 1000;

        let wTime = matchData.whiteTime;
        let bTime = matchData.blackTime;
        if (move.color === 'w') wTime = Math.max(0, wTime - timeSpent + increment);
        else bTime = Math.max(0, bTime - timeSpent + increment);

        // [STEP 5] Cek Game Over & Generate PGN
        let isGameOver = false;
        let resultStr = 'ongoing';
        let winReason = null;

        if (chess.isCheckmate()) {
            isGameOver = true;
            resultStr = (move.color === 'w') ? '1-0' : '0-1';
            winReason = 'checkmate';
        } else if (chess.isDraw()) {
            isGameOver = true;
            resultStr = '1/2-1/2';
            winReason = chess.isStalemate() ? 'stalemate' : 'draw';
        }

        const newFen = chess.fen();
        const currentPgn = chess.pgn();
        const finalUci = from + to + (promotion || '');

        // [STEP 6] Update Redis (Sync)
        const nextState = {
            ...matchData,
            fen: newFen,
            turn: chess.turn(),
            whiteTime: wTime,
            blackTime: bTime,
            lastMoveTime: now,
            status: isGameOver ? 'completed' : 'ongoing',
            history: chess.history()
        };
        await redis.set(`match:${matchId}`, JSON.stringify(nextState), 'EX', 86400);

        // [STEP 7] Handle DB Updates
        if (isGameOver) {
            chess.header('Result', resultStr);
            const finalPgn = chess.pgn();
            await this._handleGameOver(nextState, resultStr, winReason, finalPgn);
        } else {
            // Simpan move secara async agar respon cepat
            MatchModel.saveMove({
                match_id: matchId, move_number: chess.moveNumber(),
                san: move.san, uci: finalUci, fen_snapshot: newFen, time_spent: timeSpent
            }).catch(e => logger.error(`[DB Error] SaveMove: ${e.message}`));

            MatchModel.updateMatchState(matchId, newFen, wTime, bTime).catch(()=>{});
        }

        // [STEP 8] Trigger Per-Move Analysis (Anti-Cheat Worker)
        StockfishService.processMoveAnalysis(matchId, newFen, finalUci).catch(()=>{});

        logger.info(`[ChessService] 🚀 END Move. Duration: ${Date.now() - start}ms`);

        return { success: true, matchId, fen: newFen, isGameOver, result: resultStr, whiteTime: wTime, blackTime: bTime };
    }

    /**
     * Menangani Akhir Pertandingan (Game Over)
     */
    static async _handleGameOver(matchData, resultStr, reason, pgnString) {
        logger.info(`[ChessService] 🏁 Finishing Match ${matchData.matchId} | Reason: ${reason}`);
        
        try {
            // 1. Update Matches, PGN, Elo, & History (Satu Transaksi di MatchModel)
            await MatchModel.finishMatch(matchData.matchId, resultStr, reason, pgnString);

            // 2. Update Tournament Standings
            if (matchData.tournament_id) {
                await TournamentService.updateStandings(
                    matchData.tournament_id,
                    matchData.white.id,
                    matchData.black.id,
                    resultStr
                ).catch(e => logger.error(`[Tournament Error]: ${e.message}`));
            }

            // 3. Trigger Full Anti-Cheat Analysis (Background)
            StockfishService.analyzeFullMatch(matchData.matchId, pgnString)
                .then(() => logger.info(`[Analysis] Full match analysis started.`))
                .catch(e => logger.error(`[Analysis Error]: ${e.message}`));

        } catch (error) {
            logger.error(`[ChessService] ❌ Error in _handleGameOver: ${error.message}`);
        }
    }

    /**
    * Handle Player Timeout
    */
    static async processTimeout(matchId) {
        logger.info(`[Timeout Check] 🔍 Processing Timeout for Match: ${matchId}`);
        
        // 1. Ambil data state terbaru (dari Redis/DB)
        const matchData = await this._getMatchState(matchId);
        if (!matchData || matchData.status === 'completed') return null;

        // 2. Inisialisasi Chess dengan benar agar riwayat tidak hilang
        const chess = new Chess();
        
        // PENTING: Jika matchData memiliki riwayat PGN, muat PGN-nya, bukan FEN-nya
        // agar move history (1. e4 e5...) tetap terbawa sampai akhir.
        if (matchData.pgn) {
            chess.loadPgn(matchData.pgn);
        } else {
            chess.load(matchData.fen);
        }

        const turnColor = chess.turn(); // Siapa yang gilirannya sekarang?
        // Jika putih yang sedang mikir lalu timeout, maka hitam menang (0-1)
        const resultStr = (turnColor === 'w') ? '0-1' : '1-0';

        logger.info(`[Timeout] Winner determined: ${resultStr} due to ${turnColor} timeout.`);

        // 3. Panggil Game Over dengan PGN lengkap
        const finalPgn = chess.pgn(); 
        
        await this._handleGameOver(matchData, resultStr, 'timeout', finalPgn);

        return {
            success: true,
            isGameOver: true,
            result: resultStr,
            winReason: 'timeout',
            fen: chess.fen()
        };
    }

    /**
     * Handle Resignation
     */
    static async processResign(matchId, userId) {
        const matchData = await this._getMatchState(matchId);
        if (!matchData || matchData.status === 'completed') return null;

        const result = (String(matchData.white.id) === String(userId)) ? '0-1' : '1-0';
        const chess = new Chess(matchData.fen);

        await this._handleGameOver(matchData, result, 'resignation', chess.pgn());
        return { isGameOver: true, result, winReason: 'resignation' };
    }

    // --- Internal Helpers ---

    static _sanitizeMoveInput(rawMove) {
        if (typeof rawMove === 'string') return rawMove;
        if (typeof rawMove === 'object' && rawMove !== null) {
            return (rawMove.from + rawMove.to + (rawMove.promotion || '')) || rawMove.uci;
        }
        throw new Error('Format langkah tidak valid.');
    }

    static async _getMatchState(matchId) {
        const matchKey = `match:${matchId}`;
        const cached = await redis.get(matchKey);
        if (cached) return JSON.parse(cached);

        const dbMatch = await MatchModel.getMatchById(matchId);
        if (!dbMatch) return null;

        const state = {
            matchId: dbMatch.id,
            fen: dbMatch.current_fen,
            whiteTime: parseInt(dbMatch.white_time_ms),
            blackTime: parseInt(dbMatch.black_time_ms),
            white: { id: dbMatch.white_player_id },
            black: { id: dbMatch.black_player_id },
            status: dbMatch.status,
            lastMoveTime: dbMatch.last_move_time ? new Date(dbMatch.last_move_time).getTime() : Date.now(),
            increment: dbMatch.time_control_increment || 0,
            tournament_id: dbMatch.tournament_id,
            history: dbMatch.history ? JSON.parse(dbMatch.history) : []
        };

        if (dbMatch.status === 'ongoing') await redis.set(matchKey, JSON.stringify(state), 'EX', 86400);
        return state;
    }
}

module.exports = ChessService;