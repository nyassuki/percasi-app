/**
 * file: backend-node/src/services/matchmakingService.js
 * updated by: yassuki & AI Assistant
 * updated date: 2025-12-26
 * description: Service Matchmaking dengan dukungan Multi-Server (Redis Adapter Compatible)
 */

const { v4: uuidv4 } = require('uuid'); 
const redis = require('../config/redis'); 
const MatchModel = require('../models/matchModel');
const MasterModel = require('../models/masterModel');
const logger = require('../utils/logger');

const QUEUE_KEY = 'queue:casual';

class MatchmakingService {

    static async joinQueue(userId) {
        try {
            // 1. Masukkan user ke Redis Set (Unik)
            await redis.sadd(QUEUE_KEY, userId);
            logger.info(`[MATCHMAKING] User ${userId} joined queue.`);

            // 2. Cek jumlah orang
            const queueSize = await redis.scard(QUEUE_KEY);

            // 3. Jika >= 2, gas match!
            if (queueSize >= 2) {
                await this._createMatchFromQueue();
            }
        } catch (error) {
            logger.error("[MATCHMAKING JOIN ERROR]", error);
        }
    }

    static async leaveQueue(userId) {
        try {
            await redis.srem(QUEUE_KEY, userId);
            logger.info(`[MATCHMAKING] User ${userId} left queue.`);
        } catch (error) {
            logger.error("[MATCHMAKING LEAVE ERROR]", error);
        }
    }

    static async _createMatchFromQueue() {
        // [LAZY LOAD] Hindari Circular Dependency dengan io.js
        const socketManager = require('../socket/io');

        // 1. AMBIL 2 USER SECARA ATOMIC
        const players = await redis.spop(QUEUE_KEY, 2);

        if (!players || players.length < 2) {
            // Rollback jika cuma keambil 1 (race condition di server lain)
            if (players && players.length === 1) {
                await redis.sadd(QUEUE_KEY, players[0]);
            }
            return;
        }

        const p1Id = players[0];
        const p2Id = players[1];

        logger.info(`[MATCHMAKING] Pairing found: ${p1Id} vs ${p2Id}`);

        try {
            // 2. AMBIL DATA USER LENGKAP DARI LOBBY (Redis)
            const rawP1 = await redis.hget('lobby:users', p1Id);
            const rawP2 = await redis.hget('lobby:users', p2Id);

            // Fallback object jika data redis hilang (user mungkin disconnect tapi masih di queue)
            const p1Data = rawP1 ? JSON.parse(rawP1) : { id: p1Id, username: `User-${p1Id}`, rating: 1200 };
            const p2Data = rawP2 ? JSON.parse(rawP2) : { id: p2Id, username: `User-${p2Id}`, rating: 1200 };

            // 3. TENTUKAN WARNA (Random)
            const isP1White = Math.random() < 0.5;
            const whiteUser = isP1White ? p1Data : p2Data;
            const blackUser = isP1White ? p2Data : p1Data;

            // 4. SETTING WAKTU (Default 10 Menit)
            let rapidTime = 600000; 
            try {
                const MasterSettings = await MasterModel.getSettings();
                if (MasterSettings?.[0]?.['rapid_match_time']) {
                    rapidTime = parseInt(MasterSettings[0]['rapid_match_time']);
                }
            } catch (e) {}

            // 5. GENERATE ID
            const matchUuid = uuidv4(); 

            // 6. SIMPAN KE DATABASE (SQL)
            const dbMatchId = await MatchModel.createMatch(whiteUser.id, blackUser.id, rapidTime, 0);

            if (!dbMatchId) throw new Error("Failed to create match in DB");

            // 7. SIAPKAN GAME STATE (REDIS)
            const initialGameState = {
                matchId: matchUuid,
                dbId: dbMatchId,
                white: { 
                    id: whiteUser.id, 
                    username: whiteUser.username, 
                    socketId: whiteUser.socketId, // Tetap simpan, meski broadcast pakai room
                    avatar: whiteUser.avatar_url 
                },
                black: { 
                    id: blackUser.id, 
                    username: blackUser.username, 
                    socketId: blackUser.socketId,
                    avatar: blackUser.avatar_url 
                },
                fen: 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
                turn: 'white',
                history: [],
                status: 'active',
                timeControl: rapidTime,
                createdAt: Date.now()
            };

            // 8. UPDATE STATUS USER JADI 'PLAYING'
            const newWhiteStatus = { ...whiteUser, status: 'playing' };
            const newBlackStatus = { ...blackUser, status: 'playing' };

            // 9. EKSEKUSI REDIS PARALEL
            await Promise.all([
                // Simpan Game State (Expire 2 jam)
                redis.set(`match:${matchUuid}`, JSON.stringify(initialGameState), 'EX', 7200),
                
                // Update Status Lobby
                redis.hset('lobby:users', whiteUser.id, JSON.stringify(newWhiteStatus)),
                redis.hset('lobby:users', blackUser.id, JSON.stringify(newBlackStatus))
            ]);

            // 10. NOTIFIKASI SOCKET (Via Redis Adapter)
            const io = socketManager.getIO();

            // [FIX PENTING] Nama room harus konsisten dengan io.js (`user:${id}`)
            // Menggunakan titik dua (:), bukan underscore (_)
            const whiteRoom = `user:${String(whiteUser.id)}`;
            const blackRoom = `user:${String(blackUser.id)}`;

            const matchPayload = {
                dbMatchId: dbMatchId,
                matchId: matchUuid,
                whiteId: whiteUser.id,
                blackId: blackUser.id,
                fen: 'start',
                timeControl: rapidTime
            };

            // Emit ke Room User (Akan sampai ke user walau beda server)
            io.to(whiteRoom).emit('matchFound', { ...matchPayload, myColor: 'white' });
            io.to(blackRoom).emit('matchFound', { ...matchPayload, myColor: 'black' });

            // Update list lobby agar status playing terlihat user lain
            try {
                const rawUsers = await redis.hgetall('lobby:users');
                const lobbyList = Object.values(rawUsers).map(u => JSON.parse(u));
                io.emit('lobby_update', lobbyList);
            } catch (err) {
                logger.warn("Failed to broadcast lobby update:", err.message);
            }

            logger.info(`[MATCHMAKING] Success: ${matchUuid} (DB: ${dbMatchId})`);

        } catch (error) {
            logger.error("[MATCHMAKING PROCESS ERROR]", error);
            
            // RECOVERY: Masukkan kembali ke antrian jika gagal
            logger.info(`[MATCHMAKING] Restoring users ${p1Id}, ${p2Id} to queue...`);
            await redis.sadd(QUEUE_KEY, p1Id, p2Id);
            
            // Beritahu user (Gunakan format room baru)
            const io = socketManager.getIO();
            io.to(`user:${p1Id}`).emit('matchmaking_error', 'Gagal membuat match. Mencoba lagi...');
            io.to(`user:${p2Id}`).emit('matchmaking_error', 'Gagal membuat match. Mencoba lagi...');
        }
    }
}

module.exports = MatchmakingService;