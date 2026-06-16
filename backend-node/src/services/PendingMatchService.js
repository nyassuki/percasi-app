/**
 * file: backend-node/src/services/PendingMatchService.js
 * updated by: yassuki & AI Assistant
 * updated date: 2025-12-26
 * description: Service untuk Admin Match & QR Match dengan support Multi-Server
 */

const pool = require('../config/database');
const MatchModel = require('../models/matchModel');
const UserModel = require('../models/userModel');
const redis = require('../config/redis'); 
const logger = require('../utils/logger');
const { v4: uuidv4 } = require('uuid');

const QUEUE_KEY = 'queue:admin_matches';

class PendingMatchService {

    // --- HELPER METHODS (Static) ---

    static getMatchKey(matchId) {
        return `match:pending:${matchId}`;
    }

    static getUserPendingKey(userId) {
        return `user:invite:${userId}`;
    }

    /**
     * Check if user is Online AND NOT playing
     */
    static async isUserAvailable(userId) {
        try {
            const rawUser = await redis.hget('lobby:users', userId);
            if (!rawUser) return false; // Offline

            const user = JSON.parse(rawUser);
            // Must be 'online' and NOT 'playing'
            if (user.status === 'online' && user.status !== 'playing') {
                return true;
            }
            return false;
        } catch (error) {
            logger.error(`Error checking availability for ${userId}:`, error);
            return false;
        }
    }

    // --- MAIN METHODS ---

    /**
     * 1. CREATE MATCH (Admin Trigger)
     */
    static async createAdminMatch(whiteId, blackId, opponentName="Admin match", timeControlMs = 600000) {
        try {
            // 1. Check Availability
            const isWhiteReady = await PendingMatchService.isUserAvailable(whiteId);
            const isBlackReady = await PendingMatchService.isUserAvailable(blackId);

            // 2. If BOTH Ready -> Execute Immediately
            if (isWhiteReady && isBlackReady) {
                logger.info(`[AdminMatch] Users ready. Executing match creation.`);
                return await PendingMatchService._executeMatchCreation(whiteId, blackId, opponentName, timeControlMs);
            } 
            
            // 3. If BUSY/OFFLINE -> Add to Queue
            else {
                logger.info(`[AdminMatch] Users busy/offline (W:${isWhiteReady}, B:${isBlackReady}). Queuing match...`);
                
                const queueItem = {
                    whiteId,
                    blackId,
                    opponentName,
                    timeControlMs,
                    createdAt: Date.now()
                };

                await redis.rpush(QUEUE_KEY, JSON.stringify(queueItem));
                
                return { status: 'queued', message: 'Match queued because users are unavailable' };
            }

        } catch (error) {
            logger.error("Error creating admin match:", error);
            throw error;
        }
    }

    /**
     * Core Logic: Create DB Record, Redis State & Send Invite
     */
    static async _executeMatchCreation(whiteId, blackId, opponentName, timeControlMs) {
        const socketIoWrapper = require('../socket/io'); 
        const io = socketIoWrapper.getIO();

        try {
            const wId = parseInt(whiteId);
            const bId = parseInt(blackId);

            // A. Simpan ke Database
            const matchId = await MatchModel.create({
                whiteId: wId,
                blackId: bId,
                whiteTime: timeControlMs,
                blackTime: timeControlMs
            });

            logger.info(`[PendingMatch] Created Match ID: ${matchId}`);

            const acceptWindowSeconds = 30;
            const expireAt = Date.now() + (acceptWindowSeconds * 1000);

            // B. Simpan State ke Redis (Hash)
            const redisKey = PendingMatchService.getMatchKey(matchId);
            await redis.hset(redisKey, {
                whiteId: wId,
                blackId: bId,
                whiteAccepted: "0",
                blackAccepted: "0",
                expireAt: expireAt
            });
            await redis.expire(redisKey, acceptWindowSeconds + 5);

            // C. Simpan Pointer User -> Match
            await redis.set(PendingMatchService.getUserPendingKey(wId), matchId, 'EX', acceptWindowSeconds + 5);
            await redis.set(PendingMatchService.getUserPendingKey(bId), matchId, 'EX', acceptWindowSeconds + 5);

            // D. Jalankan Timer Lokal (Backup cleanup)
            setTimeout(() => PendingMatchService.handleTimeout(matchId), acceptWindowSeconds * 1000);

            // E. Kirim Undangan via Socket Room
            const whiteUserDetail = await UserModel.findById(whiteId);
            const BlackUseDetail = await UserModel.findById(blackId);
            const B_opponent = BlackUseDetail ? BlackUseDetail.username : "Unknown";
            const W_opponent = whiteUserDetail ? whiteUserDetail.username : "Unknown";

            const payload = { 
                B_opponent,
                W_opponent,
                matchId, 
                expireAt, 
                opponentName: opponentName
            };

            // [FIX] Broadcast ke Room User (Aman Lintas Server)
            io.to(`user:${wId}`).emit('match_invitation', { ...payload, role: 'white' });
            io.to(`user:${bId}`).emit('match_invitation', { ...payload, role: 'black' });

            return matchId;

        } catch (error) {
            logger.error("Error executing match creation:", error);
            throw error;
        }
    }

    /**
     * CRON JOB WORKER
     */
    static async processMatchQueue() {
        try {
            const queueItems = await redis.lrange(QUEUE_KEY, 0, -1);

            if (queueItems.length === 0) return;

            for (const itemStr of queueItems) {
                const item = JSON.parse(itemStr);
                
                const isWhiteReady = await PendingMatchService.isUserAvailable(item.whiteId);
                const isBlackReady = await PendingMatchService.isUserAvailable(item.blackId);

                if (isWhiteReady && isBlackReady) {
                    logger.info(`[Cron] Users ready for Queued Match (${item.whiteId} vs ${item.blackId}). Executing...`);
                    
                    await PendingMatchService._executeMatchCreation(
                        item.whiteId, 
                        item.blackId, 
                        item.opponentName, 
                        item.timeControlMs
                    );

                    await redis.lrem(QUEUE_KEY, 1, itemStr);
                } else {
                    const oneDay = 24 * 60 * 60 * 1000;
                    if (Date.now() - item.createdAt > oneDay) {
                         logger.info(`[Cron] Dropping expired queue item: ${item.whiteId} vs ${item.blackId}`);
                         await redis.lrem(QUEUE_KEY, 1, itemStr);
                    }
                }
            }
        } catch (error) {
            logger.error("[Cron] Error processing match queue:", error);
        }
    }

    /**
     * 2. CEK UNDANGAN (Dipanggil saat Refresh/Connect)
     */
    static async checkPendingInvitation(userId, socketId) {
        try {
            const socketIoWrapper = require('../socket/io');
            let io;
            try { io = socketIoWrapper.getIO(); } catch (e) { return; }

            const userKey = PendingMatchService.getUserPendingKey(userId);
            const matchId = await redis.get(userKey);
            
            if (!matchId) return; 

            const matchKey = PendingMatchService.getMatchKey(matchId);
            const matchData = await redis.hgetall(matchKey);

            if (!matchData || Object.keys(matchData).length === 0) return;

            let role = null;
            let hasAccepted = false;
            const uId = parseInt(userId);

            if (uId === parseInt(matchData.whiteId)) {
                role = 'white';
                hasAccepted = matchData.whiteAccepted === "1";
            } else if (uId === parseInt(matchData.blackId)) {
                role = 'black';
                hasAccepted = matchData.blackAccepted === "1";
            }

            if (!role) return;

            // [NOTE] Di sini kita pakai socketId karena function ini dipanggil
            // saat socket 'connection', jadi socketId pasti valid di server ini.
            if (hasAccepted) {
                io.to(socketId).emit('match_accepted_status', { status: 'waiting_opponent' });
            } else {
                const payload = {
                    matchId: parseInt(matchId),
                    expireAt: parseInt(matchData.expireAt),
                    opponentName: "Admin Paired Match"
                };
                io.to(socketId).emit('match_invitation', { ...payload, role });
                logger.info(`[PendingMatch] Resending invite to User ${userId} (Refresh Recovery)`);
            }
        } catch (err) {
            logger.error("Error in checkPendingInvitation:", err);
        }
    }

    /**
     * 3. ACCEPT MATCH
     */
    static async acceptMatch(matchId, userId) {
        const socketIoWrapper = require('../socket/io');
        const io = socketIoWrapper.getIO();

        const mId = parseInt(matchId);
        const uId = parseInt(userId);
        const redisKey = PendingMatchService.getMatchKey(mId);

        const matchData = await redis.hgetall(redisKey);
        if (!matchData || Object.keys(matchData).length === 0) return;

        let updatedField = {};
        if (uId == matchData.whiteId) updatedField = { whiteAccepted: "1" };
        else if (uId == matchData.blackId) updatedField = { blackAccepted: "1" };
        
        if (Object.keys(updatedField).length > 0) {
            await redis.hset(redisKey, updatedField);
        }

        // [FIX] Emit via Room
        io.to(`user:${uId}`).emit('match_accepted_status', { status: 'waiting_opponent' });

        const updatedMatch = await redis.hgetall(redisKey);
        if (updatedMatch.whiteAccepted === "1" && updatedMatch.blackAccepted === "1") {
            
            await PendingMatchService.cleanupRedis(mId, updatedMatch.whiteId, updatedMatch.blackId);

            await MatchModel.updateStatus(mId, 'ongoing');
            logger.info(`[Match ${mId}] Started! All Accepted.`);

            // [FIX] Emit Start via Room
            io.to(`user:${updatedMatch.whiteId}`).emit('tmatch_start', { matchId: mId });
            io.to(`user:${updatedMatch.blackId}`).emit('cmatch_start', { matchId: mId });
        }
    }

    /**
     * 4. HANDLE TIMEOUT / WO
     */
    static async handleTimeout(matchId) {
        const socketIoWrapper = require('../socket/io');
        let io;
        try { io = socketIoWrapper.getIO(); } catch(e) { return; }

        const redisKey = PendingMatchService.getMatchKey(matchId);

        const match = await redis.hgetall(redisKey);
        if (!match || Object.keys(match).length === 0) return; 

        await PendingMatchService.cleanupRedis(matchId, match.whiteId, match.blackId);

        let result = 'aborted';
        let winReason = 'agreement';
        
        const wAcc = match.whiteAccepted === "1";
        const bAcc = match.blackAccepted === "1";

        if (wAcc && !bAcc) {
            result = '1-0'; winReason = 'timeout'; 
        } else if (!wAcc && bAcc) {
            result = '0-1'; winReason = 'timeout';
        }

        logger.info(`[Match ${matchId}] Timeout. Result: ${result}`);

        const query = `UPDATE matches SET status = 'completed', result = ?, win_reason = ?, end_time = NOW() WHERE id = ?`;
        await pool.execute(query, [result, winReason, matchId]);

        const msg = {
            matchId,
            result,
            winReason,
            message: result === 'aborted' ? "Match dibatalkan (No Show)" : "Lawan tidak merespon (WO)"
        };

        // [FIX] Emit via Room
        io.to(`user:${match.whiteId}`).emit('match_cancelled', msg);
        io.to(`user:${match.blackId}`).emit('match_cancelled', msg);
    }

    /**
     * [BARU] QR Code Direct Match (Disamakan dengan MatchmakingService)
     */
    static async createDirectMatch(whiteId, blackId, timeControlMs = 600000) {
        const socketIoWrapper = require('../socket/io'); 
        const io = socketIoWrapper.getIO();

        try {
            const wId = parseInt(whiteId);
            const bId = parseInt(blackId);
            const matchUuid = uuidv4(); // UUID untuk Redis Key

            // 1. Simpan ke Database
            const dbMatchId = await MatchModel.create({
                whiteId: wId,
                blackId: bId,
                whiteTime: timeControlMs,
                blackTime: timeControlMs
            });

            if (!dbMatchId) throw new Error("Database match creation failed");

            // Update status ke ongoing manual jika create defaultnya pending
            await MatchModel.updateStatus(dbMatchId, 'ongoing');

            logger.info(`[DirectMatch] Created Match UUID: ${matchUuid} (DB: ${dbMatchId})`);

            // 2. Ambil Data User (Username & Avatar) untuk Redis State
            // Coba ambil dari Redis Lobby dulu agar cepat, fallback ke DB
            let rawW = await redis.hget('lobby:users', wId);
            let rawB = await redis.hget('lobby:users', bId);
            
            let whiteUser = rawW ? JSON.parse(rawW) : await UserModel.findById(wId);
            let blackUser = rawB ? JSON.parse(rawB) : await UserModel.findById(bId);

            // 3. Siapkan Game State Awal (Konsisten dengan MatchmakingService)
            const initialGameState = {
                matchId: matchUuid,
                dbId: dbMatchId,
                white: {
                    id: wId,
                    username: whiteUser.username,
                    avatar: whiteUser.avatar_url || whiteUser.avatar
                },
                black: {
                    id: bId,
                    username: blackUser.username,
                    avatar: blackUser.avatar_url || blackUser.avatar
                },
                fen: 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
                turn: 'white',
                history: [],
                status: 'active',
                timeControl: timeControlMs,
                createdAt: Date.now()
            };
            
            // 4. Update Status & Simpan Game State (Atomic-like)
            if (rawW) { whiteUser.status = 'playing'; await redis.hset('lobby:users', wId, JSON.stringify(whiteUser)); }
            if (rawB) { blackUser.status = 'playing'; await redis.hset('lobby:users', bId, JSON.stringify(blackUser)); }

            await redis.set(`match:${matchUuid}`, JSON.stringify(initialGameState), 'EX', 7200);

            // 5. Broadcast Event Start via Room
            const payload = { 
                match_id: matchUuid, // UUID
                matchId: dbMatchId,  // DB ID
                whiteId: wId, 
                blackId: bId,
                fen: initialGameState.fen 
            };

            io.to(`user:${wId}`).emit('cmatch_start', payload);
            io.to(`user:${bId}`).emit('tmatch_start', payload);

            return matchUuid;

        } catch (error) {
            logger.error("Error creating direct match:", error);
            throw error;
        }
    }

    // --- UTILS ---
    static async cleanupRedis(matchId, whiteId, blackId) {
        const redisKey = PendingMatchService.getMatchKey(matchId);
        await redis.del(redisKey);
        await redis.del(PendingMatchService.getUserPendingKey(whiteId));
        await redis.del(PendingMatchService.getUserPendingKey(blackId));
    }

}

module.exports = PendingMatchService;