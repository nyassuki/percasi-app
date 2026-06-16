/**
 * file: backend-node/src/services/stockfishService.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Wrapper untuk menjalankan engine Stockfish via Child Process.
 */

const {
    spawn
} = require('child_process');
const MatchModel = require('../models/matchModel');
const {
    Queue
} = require('bullmq');
const connection = require('../config/redis');

// Inisialisasi Queue
// PENTING: Nama queue harus SAMA PERSIS dengan yang ada di worker ('stockfish-queue')
const analysisQueue = new Queue('stockfish-queue', {
    connection
});
// Path sesuai request Anda
const STOCKFISH_PATH = '/usr/games/stockfish';
const logger = require('../utils/logger');
const { Chess } = require('chess.js'); // Baris ini yang hilang
const pool = require('../config/database');


class StockfishService {

    /**
     * Menganalisis posisi papan (FEN) untuk mendapatkan evaluasi dan langkah terbaik.
     * @param {string} fen - FEN string posisi papan.
     * @param {number} depth - Kedalaman analisis (default 15).
     * @returns {Promise<object>} { bestMove, cpScore, mateScore }
     */
    static analyze(fen, depth = 15) {
        return new Promise((resolve, reject) => {
            const stockfish = spawn(STOCKFISH_PATH);

            let bestMove = null;
            let cpScore = 0.00; // Centipawn (e.g. 1.50)
            let mateScore = null; // Mate in X (e.g. 3)

            let outputData = "";

            // 1. Kirim Perintah ke Stockfish
            stockfish.stdin.write(`uci\n`);
            stockfish.stdin.write(`isready\n`);
            stockfish.stdin.write(`position fen ${fen}\n`);
            stockfish.stdin.write(`go depth ${depth}\n`);

            // 2. Baca Output
            stockfish.stdout.on('data', (data) => {
                const lines = data.toString().split('\n');

                for (const line of lines) {
                    // Parsing baris "info ... score cp 50 ..."
                    if (line.startsWith('info') && line.includes('score')) {
                        // Regex untuk menangkap skor CP (Centipawn)
                        const matchCp = line.match(/score cp (-?\d+)/);
                        if (matchCp) {
                            // Konversi dari centipawn (100) ke pawn unit (1.00)
                            cpScore = (parseInt(matchCp[1]) / 100).toFixed(2);
                            mateScore = null;
                        }

                        // Regex untuk menangkap skor Mate
                        const matchMate = line.match(/score mate (-?\d+)/);
                        if (matchMate) {
                            mateScore = parseInt(matchMate[1]);
                            cpScore = null;
                        }
                    }

                    // Parsing baris akhir "bestmove e2e4 ..."
                    if (line.startsWith('bestmove')) {
                        const parts = line.split(' ');
                        bestMove = parts[1]; // move UCI (e.g. e2e4)

                        // Matikan proses setelah dapat bestmove
                        stockfish.kill();
                        resolve({
                            bestMove,
                            cpScore,
                            mateScore
                        });
                    }
                }
            });

            stockfish.on('error', (err) => {
                reject(new Error(`Gagal menjalankan Stockfish: ${err.message}`));
            });

            // Timeout safety (jika stockfish hang lebih dari 5 detik)
            setTimeout(() => {
                stockfish.kill();
                // Resolve dengan apa adanya agar tidak crash
                resolve({
                    bestMove,
                    cpScore,
                    mateScore
                });
            }, 5000);
        });
    }

    /**
     * Menambahkan tugas analisis ke antrian (Non-blocking).
     * Method ini sangat cepat (return instant) sehingga tidak menghambat gameplay user.
     * * @param {number} moveId - ID dari tabel match_moves (Primary Key).
     * @param {string} fen - Posisi papan dalam format FEN.
     * @param {string} userMoveUci - Langkah user (e.g. "e2e4") untuk perbandingan best move.
     * @returns {Promise<void>}
     */
    static async processMoveAnalysis(moveId, fen, userMoveUci) {
        try {
            // Tambahkan Job ke Redis
            // Job Name: 'analyze-move'
            await analysisQueue.add('analyze-move', {
                moveId,
                fen,
                userMoveUci
            }, {
                // Opsi Manajemen Memori Redis
                removeOnComplete: true, // Hapus job dari Redis jika sukses (hemat RAM)
                removeOnFail: 500 // Simpan 500 job gagal terakhir untuk keperluan debugging
            });

            logger.info(`[STOCKFISH PRODUCER] Move ID ${moveId} berhasil masuk antrian.`);

        } catch (error) {
            // Log error queue, tapi JANGAN throw error ke atas.
            // Agar jika Redis mati, game catur tetap bisa jalan (hanya fitur analisis yang mati).
            logger.error(`[STOCKFISH QUEUE ERROR] Gagal memasukkan Move ID ${moveId} ke antrian:`, error.message);
        }
    }
    /**
     * Menganalisis posisi papan (FEN)
     * @param {string} fen - FEN string posisi papan.
     * @param {number} difficulty - Skill Level (0-20). 20 adalah terkuat.
     * @param {number} depth - Kedalaman analisis (default 15).
     */
    static BotAnalyze(fen, difficulty, depth = 15) {
        return new Promise((resolve, reject) => {
            const stockfish = spawn(STOCKFISH_PATH);

            let bestMove = null;
            let cpScore = 0.00;
            let mateScore = null;

            // 1. Kirim Konfigurasi UCI yang Benar
            stockfish.stdin.write(`uci\n`);
            stockfish.stdin.write(`ucinewgame\n`); // Penting: Reset state engine
            stockfish.stdin.write(`setoption name Hash value 32\n`); // Alokasi memori (MB)
            stockfish.stdin.write(`setoption name Skill Level value ${difficulty}\n`); // 0-20
            
            // Tambahkan sedikit waktu berpikir jika di level tinggi agar tidak instan/ceroboh
            const moveTime = difficulty > 15 ? 1000 : 500; 

            stockfish.stdin.write(`isready\n`);
            stockfish.stdin.write(`position fen ${fen}\n`);
            
            // Gunakan kedalaman (depth) DAN waktu (movetime) untuk akurasi lebih tinggi
            stockfish.stdin.write(`go depth ${depth} movetime ${moveTime}\n`);

            stockfish.stdout.on('data', (data) => {
                const lines = data.toString().split('\n');

                for (const line of lines) {
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

                    if (line.startsWith('bestmove')) {
                        const parts = line.split(' ');
                        bestMove = parts[1];
                        stockfish.kill();
                        resolve({ bestMove, cpScore, mateScore });
                    }
                }
            });

            stockfish.on('error', (err) => {
                reject(new Error(`Gagal menjalankan Stockfish: ${err.message}`));
            });

            // Safety Timeout ditingkatkan ke 10 detik untuk analisis dalam
            setTimeout(() => {
                if (stockfish) stockfish.kill();
                resolve({ bestMove, cpScore, mateScore });
            }, 10000);
        });
    }
    /**
    * [BARU] ANTI-CHEAT: Menganalisis seluruh PGN setelah game selesai
    * Menghitung ACPL (Average Centipawn Loss) dan Akurasi %
    */
    static async analyzeFullMatch(matchId, pgn) {
        logger.info(`[Anti-Cheat] 🔍 Memulai Analisis Penuh Match: ${matchId}`);
        const chess = new Chess();
        
        try {
            // 1. PEMBERSIHAN EKSTRIM (Sanitasi)
            let cleanPgn = pgn;

            // Jika pgn adalah string yang isinya JSON string (berawal dengan "{ atau "[)
            if (typeof cleanPgn === 'string' && cleanPgn.trim().startsWith('"')) {
                try {
                    // Mencoba membuang bungkus kutip ganda ekstra
                    cleanPgn = JSON.parse(cleanPgn);
                } catch (e) {
                    // Jika gagal parse, hapus kutip manual di awal dan akhir
                    cleanPgn = cleanPgn.replace(/^"+|"+$/g, '');
                }
            }

            // Fix literal newline dan spasi
            cleanPgn = String(cleanPgn)
                .replace(/\\n/g, '\n') // Ubah literal \n jadi newline
                .replace(/\\"/g, '"')  // Ubah literal \" jadi kutip asli
                .replace(/\r/g, '')     // Hapus carriage return
                .trim();

            // PENTING: PGN butuh minimal 1 baris kosong antara Tag [] dan Moves
            // Kita paksa tambahkan newline sebelum angka langkah pertama "1." atau "4."
            if (cleanPgn.includes(']') && !cleanPgn.includes('\n\n')) {
                cleanPgn = cleanPgn.replace(/\]\s*(\d+\.)/, ']\n\n$1');
            }

            // 2. LOAD PGN
            let success = false;
            try {
                success = chess.loadPgn(cleanPgn);
            } catch (e) {
                success = false;
            }

            // 3. FALLBACK: Jika loadPgn Gagal, coba ambil FEN dan Move manual
            if (!success) {
                logger.warn(`[Anti-Cheat] LoadPgn Gagal, mencoba teknik Hard-Parse Match ${matchId}`);
                
                const fenMatch = cleanPgn.match(/\[FEN "(.*?)"\]/);
                const moveMatch = cleanPgn.match(/\d+\.\s*([a-zA-Z1-8+#=x-]+)/);

                if (fenMatch && moveMatch) {
                    chess.load(fenMatch[1]);
                    chess.move(moveMatch[1]);
                    success = true;
                }
            }

            if (!success) {
                logger.error(`[Anti-Cheat] Match ${matchId}: PGN tetap tidak valid setelah Hard-Parse.`);
                return;
            }

            // 4. PROSES ANALISIS (Sama seperti sebelumnya)
            const history = chess.history({ verbose: true });
            if (history.length === 0) return;

            let totalCpLoss = 0;
            let engineMatches = 0;

            for (let i = 0; i < history.length; i++) {
                const move = history[i];
                const evalBefore = await this.analyze(move.before, 12);
                if (move.lan === evalBefore.bestMove) engineMatches++;

                const evalAfter = await this.analyze(move.after, 12);
                let scoreBefore = parseFloat(evalBefore.cpScore || 0);
                let scoreAfter = parseFloat(evalAfter.cpScore || 0);

                if (evalBefore.mateScore !== null) scoreBefore = evalBefore.mateScore > 0 ? 10 : -10;
                if (evalAfter.mateScore !== null) scoreAfter = evalAfter.mateScore > 0 ? 10 : -10;

                if (move.color === 'b') {
                    scoreBefore = -scoreBefore;
                    scoreAfter = -scoreAfter;
                }

                totalCpLoss += Math.max(0, (scoreBefore - scoreAfter) * 100);
            }

            // Kalkulasi Statistik
            const moveCount = history.length;
            const acpl = totalCpLoss / moveCount;
            const accuracyScore = Math.max(0, Math.min(100, 100 * Math.exp(-0.003 * acpl)));

            await pool.execute(
                `UPDATE matches SET cheat_probability = ?, accuracy_score = ?, acpl = ?, is_analyzed = 1 WHERE id = ?`,
                [accuracyScore > 95 ? 0.9 : 0.05, accuracyScore.toFixed(2), acpl.toFixed(2), matchId]
            );

            logger.info(`[Anti-Cheat] Match ${matchId} Berhasil Dianalisis. Accuracy: ${accuracyScore.toFixed(2)}%`);

        } catch (error) {
            logger.error(`[Anti-Cheat Error] Match ${matchId}: ${error.message}`);
        }
    }
}

module.exports = StockfishService;