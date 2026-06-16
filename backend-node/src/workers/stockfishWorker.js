/**
 * file: backend-node/src/workers/stockfishWorker.js
 * updated by: yassuki & AI Assistant
 * description: Worker BullMQ untuk memproses antrian analisis Stockfish.
 */

const { Worker } = require('bullmq');
const { spawn } = require('child_process');
const redis = require('../config/redis'); 
const MatchModel = require('../models/matchModel');
const logger = require('../utils/logger');

const STOCKFISH_PATH = process.env.STOCKFISH_PATH || '/usr/games/stockfish';

/**
 * Menjalankan proses Stockfish untuk satu posisi FEN
 */
function runStockfishAnalysis(fen, depth = 12) {
  return new Promise((resolve) => {
    const stockfish = spawn(STOCKFISH_PATH);
    let bestMove = null;
    let cpScore = 0.00;
    let mateScore = null;

    const timer = setTimeout(() => {
        stockfish.kill();
        resolve({ bestMove, cpScore, mateScore, note: 'timeout' });
    }, 15000);

    // Kirim perintah UCI
    stockfish.stdin.write(`uci\nisready\nposition fen ${fen}\ngo depth ${depth}\n`);

    stockfish.stdout.on('data', (data) => {
        const lines = data.toString().split('\n');
        for (const line of lines) {
            // Parsing skor (Centipawn atau Mate)
            if (line.startsWith('info') && line.includes('score')) {
                const matchCp = line.match(/score cp (-?\d+)/);
                if (matchCp) {
                    cpScore = (parseInt(matchCp[1]) / 100).toFixed(2);
                    mateScore = null;
                }
                const matchMate = line.match(/score mate (-?\d+)/);
                if (matchMate) {
                    mateScore = parseInt(matchMate[1]);
                    cpScore = null;
                }
            }
            // Parsing Best Move sebagai tanda selesai
            if (line.startsWith('bestmove')) {
                const parts = line.split(' ');
                bestMove = parts[1];
                clearTimeout(timer);
                stockfish.kill();
                resolve({ bestMove, cpScore, mateScore });
            }
        }
    });

    stockfish.on('error', (err) => {
        clearTimeout(timer);
        logger.error(`[Stockfish Spawn Error] ${err.message}`);
        resolve({ bestMove: null, note: 'spawn_error' });
    });
  });
}

// Inisialisasi Worker
const stockfishWorker = new Worker('stockfish-queue', async (job) => {
  const { moveId, fen, userMoveUci } = job.data;
  logger.info(`[WORKER] ⚙️ Job ${job.id} | MoveID: ${moveId}`);

  try {
    // 1. Jalankan analisis (Depth 12 cukup untuk respons cepat)
    const result = await runStockfishAnalysis(fen, 12); 

    if (!result || !result.bestMove) {
        throw new Error("Stockfish failed to return best move");
    }

    // 2. Klasifikasi Langkah (Accuracy Detection)
    const isBestMove = (userMoveUci === result.bestMove);
    
    // Tentukan Skor Evaluasi Final
    let finalEval = result.cpScore;
    if (result.mateScore !== null) {
        // Jika Mate, berikan nilai ekstrem (M10 = 100, M-10 = -100)
        finalEval = result.mateScore > 0 ? 100.00 : -100.00;
    }

    // 3. Tentukan Kategori Berdasarkan CP Loss (Sederhana)
    // Di sini Anda bisa mengembangkan logika Blunder, Mistake, Inaccuracy
    let category = isBestMove ? 'best' : 'good';

    // 4. Update Database
    if (MatchModel?.updateMoveAnalysis) {
        // Pastikan urutan parameter: (moveId, cpScore, isBestMove, category)
        await MatchModel.updateMoveAnalysis(moveId, finalEval, isBestMove ? 1 : 0, category);
        logger.info(`[WORKER] ✅ DB Updated | MoveID: ${moveId} | Eval: ${finalEval}`);
    } else {
        logger.warn("[WORKER] ⚠️ MatchModel.updateMoveAnalysis is missing.");
    }

    return { success: true, bestMove: result.bestMove, eval: finalEval };

  } catch (error) {
    logger.error(`[WORKER ERROR] Job ${job.id}: ${error.message}`);
    throw error;
  }
}, {
  connection: redis.redisConfig, // Pastikan config Redis mendukung BullMQ
  concurrency: 2 // Menjalankan maksimal 2 proses Stockfish sekaligus
});

stockfishWorker.on('failed', (job, err) => {
    logger.error(`[WORKER FAILED] Job ${job.id}: ${err.message}`);
});

module.exports = stockfishWorker;