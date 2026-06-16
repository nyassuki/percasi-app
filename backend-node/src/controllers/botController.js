/**
 * file: backend-node/src/controllers/botController.js
  * created by : yassuki
 * created date: 2025-12-11
 * description: Controller untuk Logic Game Lawan Bot menggunakan Stockfish Service.
 */

const StockfishService = require('../services/stockfishService');
const { Chess } = require('chess.js');
const logger = require('../utils/logger');

class BotController {

    static async handleBotMove(req, res) {
        try {
            const {
                fen,
                difficulty, // Bisa berupa: 'easy', 'medium', 'hard' atau angka Elo
                depth = 15
            } = req.body;

            // 1. Validasi FEN
            if (!fen) {
                return res.status(400).json({
                    status: 'error',
                    message: 'FEN wajib dikirim.'
                });
            }

            const chess = new Chess(fen);

            // 2. Cek Game Over sebelum Bot berpikir
            if (chess.isGameOver()) {
                return res.json({
                    status: 'success',
                    isGameOver: true,
                    result: chess.isCheckmate() ? (chess.turn() === 'w' ? '0-1' : '1-0') : '1/2-1/2',
                    fen: fen
                });
            }

            // 3. Pemetaan Difficulty ke Stockfish Skill Level (0-20)
            let skillLevel = 10; // Default Medium (~1500 Elo)

            if (difficulty === 'easy' || difficulty <= 800) {
                skillLevel = 0; // Beginner (~800 Elo)
            } else if (difficulty === 'medium' || (difficulty > 800 && difficulty <= 1600)) {
                skillLevel = 11; // Intermediate (~1500 Elo)
            } else if (difficulty === 'hard' || difficulty >= 3000) {
                skillLevel = 20; // Grandmaster (3000+ Elo)
            } else if (!isNaN(difficulty)) {
                // Jika user mengirimkan skill level langsung 0-20
                skillLevel = Math.max(0, Math.min(20, parseInt(difficulty)));
            }

            // 4. PANGGIL SERVICE STOCKFISH (Bukan lagi Random Move)
            logger.info(`[BOT] Thinking with Skill Level: ${difficulty}, Depth: ${depth}, difficulty: ${difficulty}`);
            
            //const analysis = await StockfishService.BotAnalyze(fen, difficulty, depth);
            const analysis = await StockfishService.BotAnalyze(fen, skillLevel, depth);

            if (!analysis.bestMove) {
                throw new Error("Stockfish gagal memberikan langkah terbaik.");
            }

            // 5. Eksekusi langkah Bot di engine Chess.js untuk divalidasi
            // Stockfish memberikan format UCI (e.g. "e2e4"), Chess.js mendukung format ini.
            const moveResult = chess.move(analysis.bestMove);

            if (!moveResult) {
                // Fallback jika karena suatu alasan UCI tidak dikenali (jarang terjadi)
                throw new Error(`Langkah Stockfish invalid menurut Chess.js: ${analysis.bestMove}`);
            }

            // 6. Response Sukses ke Frontend
            res.json({
                status: 'success',
                bestMove: analysis.bestMove, // e.g. "g1f3"
                sanMove: moveResult.san,    // e.g. "Nf3" (Opsional, untuk notasi)
                fen: chess.fen(),           // FEN baru setelah bot jalan
                isGameOver: chess.isGameOver(),
                score: {
                    cp: analysis.cpScore,
                    mate: analysis.mateScore
                },
                result: chess.isGameOver() ? 
                    (chess.isCheckmate() ? (chess.turn() === 'w' ? '0-1' : '1-0') : '1/2-1/2') 
                    : null
            });

        } catch (error) {
            logger.error("Bot Controller Error:", error);
            res.status(500).json({
                status: 'error',
                message: error.message
            });
        }
    }
}

module.exports = BotController;