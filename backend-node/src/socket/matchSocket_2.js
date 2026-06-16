/**
* file: backend-node/src/socket/matchSocket.js
* created by : yassuki
* updated date: 2025-12-15
* description: Handler untuk event Socket.io spesifik modul Match/Gameplay.
*/

const ChessService = require('../services/chessService');
// [FIX] Import Matchmaking Service untuk menangani Casual Match
const MatchmakingService = require('../services/matchmakingService');
const redis = require('../config/redis');

module.exports = (io, socket) => {

  // ============================================================
  // 1. FITUR MATCHMAKING (CASUAL MATCH) - [BAGIAN YANG HILANG]
  // ============================================================

  /**
  * Event: findMatch
  * User meminta masuk antrian pencarian lawan.
  */
  socket.on('findMatch', async () => {
    try {
      // Validasi Login
      if (!socket.user || !socket.user.id) {
        socket.emit('matchmaking_error', 'Anda harus login untuk mencari lawan.');
        return;
      }

      const userId = socket.user.id;
      logger.info(`[Socket] User ${userId} requesting casual match...`);

      // Panggil Service untuk masuk antrian
      await MatchmakingService.joinQueue(userId);

    } catch (err) {
      logger.error("Find Match Error:", err);
      socket.emit('matchmaking_error', 'Terjadi kesalahan saat mencari lawan.');
    }
  });

  /**
  * Event: cancelFindMatch
  * User membatalkan pencarian lawan.
  */
  socket.on('cancelFindMatch', async () => {
    if (socket.user && socket.user.id) {
      await MatchmakingService.leaveQueue(socket.user.id);
    }
  });


  // ============================================================
  // 2. FITUR GAMEPLAY (DI DALAM ROOM)
  // ============================================================

  /**
  * Event: joinMatch
  * Client meminta bergabung ke "Room" pertandingan agar bisa menerima update.
  */
  socket.on('joinMatch', async (matchId) => {
    const roomName = `match_${matchId}`;
    socket.join(roomName);
    logger.info(`Socket ${socket.id} joined room ${roomName}`);

    // [OPSIONAL] Simpan status user sedang di match mana via Redis
    if (socket.user && socket.user.id) {
      await redis.set(`user:active_match_room:${socket.user.id}`, matchId, 'EX', 3600);
    }
  });

  /**
  * Event: playerMove
  */
  socket.on('playerMove', async (data) => {
    const { matchId, uciMove } = data;
    const roomName = `match_${matchId}`;

    try {
      // 1. Panggil Service (Validasi + DB)
      const result = await ChessService.processMove(matchId, uciMove);

      // 2. Broadcast ke room
      io.to(roomName).emit('moveUpdate', result);
      logger.info(`Move sukses di room ${roomName}: ${uciMove}`);

    } catch (error) {
      // 3. Error handler
      socket.emit('moveError', {
        message: error.message,
        uciMove: uciMove
      });
      logger.error(`Move error: ${error.message}`);
    }
  });

  /**
  * Event: claimTimeout
  */
  socket.on('claimTimeout', async (data) => {
    const { matchId } = data;
    const roomName = `match_${matchId}`;

    try {
      logger.info(`Checking timeout claim for match ${matchId}...`);
      const result = await ChessService.processTimeout(matchId);

      if (result && result.isGameOver) {
        io.to(roomName).emit('moveUpdate', result);
        logger.info("Timeout confirmed & broadcasted.");
      }
    } catch (error) {
      logger.error("Timeout check error:", error);
    }
  });

  /**
  * Event: requestUndo
  */
  socket.on('requestUndo', async (data) => {
    const { matchId } = data;
    try {
      const result = await ChessService.processUndo(matchId);
      io.to(`match_${matchId}`).emit('undoUpdate', result);
      logger.info(`[UNDO] Match ${matchId} reverted to FEN: ${result.fen}`);
    } catch (error) {
      socket.emit('error', { message: error.message });
    }
  });

  /**
  * Event: resign
  */
  socket.on('resign', async ({ matchId, userId }) => {
    try {
      const result = await ChessService.processResign(matchId, userId);
      if (result) {
        io.to(`match_${matchId}`).emit('resignation', {
          fen: '',
          whiteTime: 0,
          blackTime: 0,
          isGameOver: true,
          result: result.result,
          winReason: 'resignation'
        });
        logger.info(`[GAME OVER] Match ${matchId} ended by resignation.`);
      }
    } catch (err) {
      logger.error(err);
    }
  });

  /**
  * Event: Offer Draw
  */
  socket.on('offerDraw', ({ matchId }) => {
    socket.to(`match_${matchId}`).emit('drawOffered');
  });

  /**
  * Event: Accept Draw
  */
  socket.on('acceptDraw', async ({ matchId }) => {
    try {
      const result = await ChessService.processDrawAgreement(matchId);
      if (result) {
        io.to(`match_${matchId}`).emit('DrawAccepted', {
          fen: '',
          whiteTime: 0,
          blackTime: 0,
          isGameOver: true,
          result: '1/2-1/2',
          winReason: 'agreement'
        });
      }
    } catch (err) {
      logger.error(err);
    }
  });

  /**
  * Event: Decline Draw
  */
  socket.on('declineDraw', ({ matchId }) => {
    socket.broadcast.to(`match_${matchId}`).emit('drawDeclined');
  });

  /**
  * Event: Player Accept Match (Dari Undangan)
  */
  socket.on('player_accept_match', async (data) => {
    // [FIX] LAZY LOAD SERVICE
    // Import di dalam sini untuk menghindari Circular Dependency dengan io.js
    const PendingMatchService = require('../services/PendingMatchService');
   
    try {
      await PendingMatchService.acceptMatch(data.matchId, data.userId);
    } catch (err) {
      logger.error('[Socket] Accept Match Error:', err.message);
    }
  });
};


ini adalah matchsocket  saya,  tambahkan untuk miror