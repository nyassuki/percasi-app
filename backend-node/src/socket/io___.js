/**
 * file: backend-node/src/socket/io.js
 */
const socketIo = require('socket.io');
const jwt = require('jsonwebtoken');
require('dotenv').config();

// IMPORT UTILS & HANDLERS
const UserSocketManager = require('../utils/UserSocketManager'); 
const qrHandler = require('./qrHandler');
const matchSocketHandler = require('./matchSocket');
const lobbyHandler = require('./lobbyHandler');
const transferHandler = require('./transferHandler');

let io;

module.exports = {
    init: (httpServer) => {
        // [PERBAIKAN CORS] 
        // origin: "*" tidak diperbolehkan jika menggunakan credentials: true
        // Gunakan origin spesifik atau fungsi untuk fleksibilitas
        io = socketIo(httpServer, {
            cors: { 
                origin: process.env.FRONTEND_URL || true, // true berarti mengizinkan origin mana saja yang merequest
                methods: ["GET", "POST"],
                credentials: true // Diperlukan agar cookie/header auth bisa lewat
            },
            transports: ['websocket', 'polling'] // Prioritaskan websocket
        });

        // 1. MIDDLEWARE AUTHENTICATION
        io.use((socket, next) => {
            // Support token via auth object (rekomendasi) atau query string
            const token = socket.handshake.auth.token || socket.handshake.query.token;

            if (!token) {
                logger.error("[Socket Auth] Error: Token missing");
                return next(new Error('Authentication error: Token missing'));
            }

            try {
                const decoded = jwt.verify(token, process.env.JWT_SECRET);
                
                // [PENTING] Pastikan user sudah melewati 2FA jika akunnya aktif 2FA
                // Jika token JWT Anda mengandung flag is_2fa_pending, tolak koneksi di sini
                if (decoded.is_2fa_pending) {
                    return next(new Error('Authentication error: 2FA Verification Required'));
                }

                socket.user = decoded; 
                next();
            } catch (err) {
                logger.error("[Socket Auth] Error: Invalid Token", err.message);
                return next(new Error('Authentication error: Invalid Token'));
            }
        });

        // 2. CONNECTION HANDLER
        io.on('connection', async (socket) => {
            const userId = socket.user.id;
            const username = socket.user.username;

            logger.info(`\n[Socket] 🟢 Connected: ${username} (ID: ${userId}) | ID: ${socket.id}`);

            // A. JOIN PERSONAL ROOM
            const userRoom = `user_${userId}`;
            socket.join(userRoom);
            
            // B. MANAGE USER STATUS (REDIS)
            const userData = {
                id: userId,
                username: username,
                socketId: socket.id,
                rating: socket.user.rating || 1200,
                avatar_url: socket.user.avatar_url || null,
                status: 'online',
                last_seen: new Date()
            };
            
            try {
                // Simpan ke Redis via Manager
                await UserSocketManager.addUser(userId, socket.id); 
                
                // Broadcast ke semua user bahwa user ini online
                io.emit('user_status_update', { 
                    userId: userId, 
                    isOnline: true,
                    username: username 
                });
            } catch (error) {
                logger.error("[Socket] Redis Error:", error);
            }

            // C. LOAD HANDLERS (Modular)
            qrHandler(io, socket);
            matchSocketHandler(io, socket);
            lobbyHandler(io, socket);
            transferHandler(io, socket);

            // D. PENDING INVITATIONS CHECK
            // Memberikan sedikit jeda agar frontend siap menerima emit
            setTimeout(async () => {
                try {
                    const PendingMatchService = require('../services/PendingMatchService');
                    await PendingMatchService.checkPendingInvitation(userId, socket.id);
                } catch (e) {
                    // logger.error("Pending service not ready");
                }
            }, 1500);

            // E. DISCONNECT HANDLER
            socket.on('disconnect', async (reason) => {
                logger.info(`[Socket] 🔴 Disconnected: ${username} (ID: ${userId}) | Reason: ${reason}`);
                
                try {
                    // Hapus dari Redis
                    await UserSocketManager.removeUser(userId);
                    
                    // Broadcast status offline
                    io.emit('user_status_update', { 
                        userId: userId, 
                        isOnline: false 
                    });
                } catch (error) {
                    logger.error("[Socket] Disconnect Cleanup Error:", error);
                }
            });

            // F. ERROR HANDLER
            socket.on('error', (err) => {
                logger.error(`[Socket Error] User ${userId}:`, err);
            });
        });
        
        return io;
    },

    getIO: () => {
        if (!io) throw new Error("Socket.io not initialized!");
        return io;
    }
};