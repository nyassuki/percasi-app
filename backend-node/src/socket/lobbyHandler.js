/**
 * file: backend-node/src/socket/lobbyHandler.js
 */
const { v4: uuidv4 } = require('uuid');
const redis = require('../config/redis');
const MatchModel = require('../models/matchModel'); 
const logger = require('../utils/logger');

// Helper: Ambil semua user dari Redis
const getLobbyList = async () => {
    try {
        const rawUsers = await redis.hgetall('lobby:users');
        return Object.values(rawUsers).map(u => JSON.parse(u));
    } catch (error) {
        logger.error("Redis Error (getLobbyList):", error);
        return [];
    }
};

module.exports = async (io, socket) => {
    const userId = socket.user.id;
    // Gunakan username yang konsisten
    const username = socket.user.username || `User-${userId}`;

    // 1. DATA USER UNTUK LOBBY (Status: Online)
    const userData = {
        id: userId,
        socketId: socket.id, // Masih disimpan untuk referensi debug, tapi kirim pesan via room
        username: username,
        rating: socket.user.rating || 1200,
        avatar_url: socket.user.avatar_url || null,
        status: 'online'
    };

    // 2. SIMPAN USER KE REDIS SAAT CONNECT
    try {
        await redis.hset('lobby:users', userId, JSON.stringify(userData));
        const users = await getLobbyList();
        io.emit('lobby_update', users); // Broadcast ke semua
    } catch (err) {
        logger.error("Gagal menyimpan user ke lobby:", err);
    }

    // --- EVENT: REQUEST LIST ---
    socket.on('request_lobby_list', async () => {
        const currentUsers = await getLobbyList();
        socket.emit('lobby_update', currentUsers);
    });

    // --- EVENT: KIRIM TANTANGAN ---
    socket.on('send_challenge', async ({ targetUserId }) => {
        // Cek target di Redis
        const rawTarget = await redis.hget('lobby:users', targetUserId);
        if (!rawTarget) {
            socket.emit('error', { message: 'User tidak ditemukan atau offline.' });
            return;
        }
        const targetUser = JSON.parse(rawTarget);
        if (targetUser.status === 'playing') {
            socket.emit('error', { message: 'User sedang bermain.' });
            return;
        }

        const matchId = uuidv4();
        const TIMEOUT_MS = 30000; // 30 Detik

        // Simpan Challenge
        const challengeData = {
            challengerId: userId,
            targetId: targetUserId,
            matchId: matchId
        };

        // Simpan di Redis (Expire 35s)
        await redis.set(`challenge:${matchId}`, JSON.stringify(challengeData), 'EX', 35);

        logger.info(`[Lobby] Challenge sent: ${username} -> ${targetUser.username}`);

        // Ambil data penantang fresh dari Redis (untuk memastikan data terbaru)
        const rawChallenger = await redis.hget('lobby:users', userId);
        const challenger = rawChallenger ? JSON.parse(rawChallenger) : userData;

        // [PERBAIKAN] Kirim ke ROOM user (lebih aman daripada socketId mentah)
        // Format Room sesuai io.js: `user:${userId}`
        io.to(`user:${targetUserId}`).emit('match_invitation', {
            matchId,
            challengerId: userId,
            W_opponent: username,
            B_opponent: targetUser.username,
            challengerAvatar: challenger.avatar_url,
            challengerRating: challenger.rating,
            role: 'black', 
            expireAt: Date.now() + TIMEOUT_MS,
            opponentName: 'Tantangan duel'
        });

        // Feedback ke Penantang
        socket.emit('match_accepted_status', {
            status: 'waiting',
            expireAt: Date.now() + TIMEOUT_MS
        });

        // Timeout Handler (Local Memory)
        setTimeout(async () => {
            const isPending = await redis.get(`challenge:${matchId}`);
            if (isPending) {
                logger.info(`[Lobby] Challenge ${matchId} expired.`);
                await redis.del(`challenge:${matchId}`);

                // Beritahu Penantang
                socket.emit('match_cancelled', { result: 'timeout' });

                // Beritahu Target (Via Room)
                io.to(`user:${targetUserId}`).emit('match_cancelled', { result: 'timeout' });
            }
        }, TIMEOUT_MS);
    });

    // --- EVENT: TERIMA TANTANGAN ---
    socket.on('player_accept_match', async ({ matchId }) => {
        // 1. Validasi Challenge di Redis
        const rawChallenge = await redis.get(`challenge:${matchId}`);
        if (!rawChallenge) {
            socket.emit('match_cancelled', { result: 'expired' });
            return;
        }

        const challenge = JSON.parse(rawChallenge);
        const { challengerId, targetId, timeControl } = challenge;

        // 2. Ambil Data Pemain Terbaru
        const rawChallenger = await redis.hget('lobby:users', challengerId);
        const rawTarget = await redis.hget('lobby:users', targetId);

        if (rawChallenger && rawTarget) {
            const challenger = JSON.parse(rawChallenger);
            const target = JSON.parse(rawTarget);

            try {
                // 3. Buat Record di DB
                const dbMatchId = await MatchModel.createMatch(
                    challengerId, // White
                    targetId, // Black
                    timeControl || 600,
                    0
                );

                if (!dbMatchId) throw new Error("Gagal membuat ID Match di Database");

                // 4. Update Status User -> 'playing'
                challenger.status = 'playing';
                target.status = 'playing';

                // 5. Siapkan Game State Redis
                const initialGameState = {
                    matchId: matchId,
                    dbId: dbMatchId,
                    white: {
                        id: challenger.id,
                        username: challenger.username,
                        socketId: challenger.socketId, // Tetap simpan, tapi emit pakai room
                        avatar: challenger.avatar_url
                    },
                    black: {
                        id: target.id,
                        username: target.username,
                        socketId: target.socketId,
                        avatar: target.avatar_url
                    },
                    fen: 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
                    turn: 'white',
                    history: [],
                    status: 'active',
                    timeControl: timeControl,
                    createdAt: Date.now()
                };

                // 6. Eksekusi Redis (Atomic-like)
                await Promise.all([
                    redis.hset('lobby:users', challengerId, JSON.stringify(challenger)),
                    redis.hset('lobby:users', targetId, JSON.stringify(target)),
                    redis.del(`challenge:${matchId}`),
                    redis.set(`match:${matchId}`, JSON.stringify(initialGameState), 'EX', 7200)
                ]);

                // 7. Broadcast Update Lobby
                const updatedUsers = await getLobbyList();
                io.emit('lobby_update', updatedUsers);

                // 8. [PERBAIKAN] Mulai Game (Kirim via ROOM)
                const startPayload = {
                    match_id: matchId,
                    matchId: dbMatchId, // ID DB untuk referensi frontend
                    whiteId: challengerId,
                    blackId: targetId,
                    fen: initialGameState.fen
                };

                // Kirim ke Room Challenger
                io.to(`user:${challengerId}`).emit('cmatch_start', startPayload);
                // Kirim ke Room Target
                io.to(`user:${targetId}`).emit('tmatch_start', startPayload);

                logger.info(`[Lobby] Match Started: ${matchId} (DB: ${dbMatchId})`);

            } catch (error) {
                logger.error("[Lobby] Error creating match:", error);
                socket.emit('error', { message: 'Gagal memulai pertandingan.' });
            }

        } else {
            socket.emit('error', { message: 'Pemain tidak ditemukan/offline.' });
        }
    });

    // --- RESET STATUS USER KE ONLINE ---
    socket.on('update_lobby_user_status', async ({ userIds }) => {
        if (!userIds || !Array.isArray(userIds)) return;
        
        try {
            const updatePromises = userIds.map(async (uid) => {
                const rawUser = await redis.hget('lobby:users', uid);
                if (rawUser) {
                    const user = JSON.parse(rawUser);
                    user.status = 'online';
                    return redis.hset('lobby:users', uid, JSON.stringify(user));
                }
            });

            await Promise.all(updatePromises);

            const updatedUsers = await getLobbyList();
            io.emit('lobby_update', updatedUsers);
            
            // Opsional: Beritahu masing-masing user bahwa mereka online
            userIds.forEach(uid => {
                 io.to(`user:${uid}`).emit('user_status_update', { userId: uid, isOnline: true });
            });
            
        } catch (err) {
            logger.error("[Lobby] Error resetting status:", err);
        }
    });

    // --- DISCONNECT ---
    socket.on('disconnect', async () => {
        // Hapus dari lobby:users
        await redis.hdel('lobby:users', userId);
        const users = await getLobbyList();
        io.emit('lobby_update', users);
        logger.info(`[Lobby] User removed: ${username}`);
    });
};