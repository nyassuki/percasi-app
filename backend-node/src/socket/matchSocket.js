/**
 * file: backend-node/src/socket/matchSocket.js
 * updated by: yassuki & AI Assistant
 * description: Handler Socket.io ROBUST (Fix ReferenceError & Input Validation)
 */

const ChessService = require('../services/chessService');
const MatchmakingService = require('../services/matchmakingService'); 
const redis = require('../config/redis');
const MatchModel = require('../models/matchModel'); 
const logger = require('../utils/logger');

const SPECTATOR_LIMIT = 50;

module.exports = (io, socket) => {
    
    // ============================================================
    // [FIX] DEFINISI HELPER AUTH (Harus ada di sini)
    // ============================================================
    const requireAuth = () => {
        if (!socket.user || !socket.user.id) {
            socket.emit('error', { message: 'Anda harus login.' });
            return false;
        }
        return true;
    };

    // ============================================================
    // 1. FITUR MATCHMAKING
    // ============================================================
    socket.on('findMatch', async () => {
        if (!requireAuth()) return;
        try {
            const userId = socket.user.id;
            logger.info(`[Socket] User ${userId} requesting match...`);
            await MatchmakingService.joinQueue(userId);
            socket.emit('matchmaking_status', { status: 'searching' });
        } catch (err) {
            logger.error("Find Match Error:", err);
            socket.emit('matchmaking_error', 'Terjadi kesalahan saat mencari lawan.');
        }
    });

    socket.on('cancelFindMatch', async () => {
        if (socket.user && socket.user.id) {
            await MatchmakingService.leaveQueue(socket.user.id);
            socket.emit('matchmaking_status', { status: 'cancelled' });
        }
    });

    // ============================================================
    // 2. FITUR GAMEPLAY & MIRRORING (JOIN ROOM)
    // ============================================================

    socket.on('joinMatch', async (matchId) => {
        if (!matchId) return;

        const roomName = `match_${matchId}`;
        const redisSpectatorKey = `match:${matchId}:spectator_count`;
        const matchRedisKey = `match:${matchId}`;

        try {
            // A. Ambil Data DB
            const dbMatch = await MatchModel.getMatchById(matchId);
            if (!dbMatch) {
                return socket.emit('error', { message: 'Pertandingan tidak ditemukan.' });
            }

            // B. Tentukan Peran
            const userId = socket.user ? socket.user.id : null;
            const isPlayer = userId && (
                userId == dbMatch.white_player_id || 
                userId == dbMatch.black_player_id
            );

            // C. Logic Spectator Limit & Counter
            if (!isPlayer) {
                const currentSpectators = await redis.get(redisSpectatorKey) || 0;
                if (parseInt(currentSpectators) >= SPECTATOR_LIMIT) {
                    return socket.emit('match_error', 'Kapasitas penonton penuh.');
                }
                await redis.incr(redisSpectatorKey);
                
                socket.isSpectating = true;
                socket.spectatingMatchId = matchId; 
            } else {
                socket.isPlayer = true;
                socket.playingMatchId = matchId;
            }

            socket.join(roomName);

            // D. [MIRRORING] Ambil Realtime State (Redis -> DB)
            const redisStateRaw = await redis.get(matchRedisKey);
            let initialState = {};

            if (redisStateRaw) {
                const rState = JSON.parse(redisStateRaw);
                initialState = {
                    fen: rState.fen,
                    turn: rState.turn,
                    whiteTime: rState.whiteTime,
                    blackTime: rState.blackTime,
                    history: rState.history || [],
                    isGameOver: rState.status === 'completed',
                    whitePlayer: { name: dbMatch.white_player_name },
                    blackPlayer: { name: dbMatch.black_player_name }
                };
            } else {
                initialState = {
                    fen: dbMatch.current_fen,
                    turn: dbMatch.current_turn || 'w',
                    whiteTime: dbMatch.time_control,
                    blackTime: dbMatch.time_control,
                    history: dbMatch.history ? JSON.parse(dbMatch.history) : [],
                    isGameOver: dbMatch.status === 'completed',
                    whitePlayer: { name: dbMatch.white_player_name },
                    blackPlayer: { name: dbMatch.black_player_name }
                };
            }

            socket.emit('initialState', initialState);
            
            if (isPlayer) {
                await redis.set(`user:active_match_room:${userId}`, matchId, 'EX', 3600);
            }

        } catch (err) {
            logger.error("Join Match Error:", err);
            socket.emit('error', { message: 'Gagal masuk ke pertandingan.' });
        }
    });

    socket.on('leaveMatch', (matchId) => {
        const roomName = `match_${matchId}`;
        socket.leave(roomName);
        
        if (socket.isSpectating && socket.spectatingMatchId == matchId) {
             const redisSpectatorKey = `match:${matchId}:spectator_count`;
             redis.decr(redisSpectatorKey).catch(() => {});
             socket.isSpectating = false;
             socket.spectatingMatchId = null;
        }
    });

    // ============================================================
    // 3. GAMEPLAY EVENTS (ROBUST & DEBUGGED)
    // ============================================================

    socket.on('playerMove', async (data) => {
        // 1. Cek Auth (Sekarang fungsi ini sudah didefinisikan di atas)
        if (!requireAuth()) return;

        // 2. Log Payload Mentah (Untuk Debugging)
        logger.info(`[Socket] 📩 RAW Move Data: ${JSON.stringify(data)}`);

        if (!data) return;

        const { matchId } = data;
        const roomName = `match_${matchId}`;
        
        // 3. Ambil Move dengan berbagai kemungkinan Key
        let uciMove = data.uciMove || data.move; 

        // 4. Normalisasi Input (Object -> String)
        try {
            if (typeof uciMove === 'object' && uciMove !== null) {
                if (uciMove.from && uciMove.to) {
                    uciMove = uciMove.from + uciMove.to + (uciMove.promotion || '');
                } else if (uciMove.source && uciMove.target) {
                    uciMove = uciMove.source + uciMove.target + (uciMove.promotion || '');
                } else if (uciMove.uci) {
                    uciMove = uciMove.uci;
                }
            }

            // [FIX UTAMA] Pengecekan Akhir
            if (!uciMove || typeof uciMove !== 'string') {
                logger.warn(`[Socket] ⚠️ Move Rejected (Invalid/Empty). Val: ${JSON.stringify(uciMove)}`);
                return; 
            }
        } catch (e) {
            logger.error(`[Socket] ❌ Error Parsing Move: ${e.message}`);
            return;
        }

        // 5. Eksekusi Service
        try {
            const result = await ChessService.processMove(matchId, socket.user.id, uciMove);
            io.to(roomName).emit('moveUpdate', result);
            logger.info(`[Socket] ✅ Move Broadcasted: ${uciMove}`);
        } catch (error) {
            logger.warn(`[Socket] 🚫 Service Error: ${error.message}`);
            socket.emit('moveError', { message: error.message, uciMove });
        }
    });

    // --- CHAT MIRRORING ---
    socket.on('sendMirrorChat', (data) => {
        if (!requireAuth()) return;
        io.to(`match_${data.matchId}`).emit('newMirrorChat', {
            user: socket.user.username,
            message: data.message,
            time: new Date()
        });
    });

    // --- GAME ACTIONS ---

    socket.on('claimTimeout', async (data) => {
        const { matchId } = data;
        try {
            const result = await ChessService.processTimeout(matchId);
            if (result && result.isGameOver) {
                io.to(`match_${matchId}`).emit('moveUpdate', result);
            }
        } catch (e) { logger.error("Timeout Error:", e); }
    });

    socket.on('resign', async ({ matchId }) => {
        if (!requireAuth()) return;
        try {
            const matchData = await ChessService.processResign(matchId, socket.user.id);
            if (matchData) {
                io.to(`match_${matchId}`).emit('resignation', {
                    matchId,
                    userId: socket.user.id,
                    result: matchData.result,
                    winReason: 'resignation',
                    status: 'completed'
                });
                // Sync akhir
                io.to(`match_${matchId}`).emit('moveUpdate', {
                    isGameOver: true,
                    result: matchData.result,
                    winReason: 'resignation',
                    fen: matchData.current_fen
                });
            }
        } catch (error) {
            socket.emit('error', { message: 'Gagal resign.' });
        }
    });

    // --- DRAW HANDLING ---

    socket.on('offerDraw', ({ matchId }) => {
        if (!requireAuth()) return;
        socket.to(`match_${matchId}`).emit('drawOffered', { 
            matchId, 
            fromUsername: socket.user.username 
        });
        socket.emit('drawOfferSent');
    });

    socket.on('declineDraw', ({ matchId }) => {
        socket.to(`match_${matchId}`).emit('drawDeclined', { 
            fromUsername: socket.user.username 
        });
    });

    socket.on('acceptDraw', async ({ matchId }) => {
        if (!requireAuth()) return;
        try {
            const result = await ChessService.processDrawAgreement(matchId);
            if (result) {
                io.to(`match_${matchId}`).emit('DrawAccepted', {
                    isGameOver: true,
                    result: '1/2-1/2',
                    winReason: 'agreement',
                    fen: result.current_fen
                });
            }
        } catch (e) { logger.error(e); }
    });

    // --- INVITATION ACCEPT ---
    socket.on('player_accept_match', async (data) => {
        if (!requireAuth()) return;
        const PendingMatchService = require('../services/PendingMatchService');
        try {
            await PendingMatchService.acceptMatch(data.matchId, data.userId);
        } catch (err) { logger.error('Accept Match Error:', err.message); }
    });

    // ============================================================
    // 4. DISCONNECT HANDLER
    // ============================================================
    socket.on('disconnect', async () => {
        if (socket.isSpectating && socket.spectatingMatchId) {
            const redisKey = `match:${socket.spectatingMatchId}:spectator_count`;
            try { await redis.decr(redisKey); } catch (e) {}
        }
        if (socket.isPlayer && socket.playingMatchId) {
             io.to(`match_${socket.playingMatchId}`).emit('opponent_connection_status', {
                status: 'disconnected',
                userId: socket.user.id
            });
        }
    });
};