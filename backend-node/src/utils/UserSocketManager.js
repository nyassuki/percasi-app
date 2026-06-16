/**
 * file: backend-node/src/utils/UserSocketManager.js
 */
const redis = require('../config/redis'); 
const logger = require('./logger');

class UserSocketManager {
    
    // Simpan UserID -> SocketID
    static async addUser(userId, socketId) {
        if (!userId || !socketId) return;
        try {
            // Simpan key "user:socket:1" = "abc12345"
            await redis.set(`user:socket:${userId}`, socketId);
            logger.info(`[UserSocketManager] Mapped User ${userId} -> ${socketId}`);
        } catch (err) {
            console.error('[UserSocketManager] Add Error:', err);
        }
    }

    // Ambil SocketID berdasarkan UserID
    static async getSocketId(userId) {
        try {
            const socketId = await redis.get(`user:socket:${userId}`);
            return socketId;
        } catch (err) {
            console.error('[UserSocketManager] Get Error:', err);
            return null;
        }
    }

    // Hapus saat disconnect
    static async removeUser(userId) {
        try {
            await redis.del(`user:socket:${userId}`);
            logger.info(`[UserSocketManager] Removed User ${userId}`);
        } catch (err) {
            console.error('[UserSocketManager] Remove Error:', err);
        }
    }
}

module.exports = UserSocketManager;