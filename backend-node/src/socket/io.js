/**
 * file: backend-node/src/socket/io.js
 * description: Socket.io Entry with Manual Redis Connection (Stable Fix)
 */

const socketIo = require('socket.io');
const jwt = require('jsonwebtoken');
const { createAdapter } = require("@socket.io/redis-adapter");
const IORedis = require('ioredis'); // Import Class directly
require('dotenv').config();

// IMPORT HANDLERS
const UserSocketManager = require('../utils/UserSocketManager'); 
const qrHandler = require('./qrHandler');
const matchSocketHandler = require('./matchSocket');
const lobbyHandler = require('./lobbyHandler');
const transferHandler = require('./transferHandler');
const logger = require('../utils/logger');

// Import Main Redis Client
const redis = require('../config/redis'); 

let io;

module.exports = {
    init: async (httpServer) => {
        io = socketIo(httpServer, {
            cors: { 
                origin: true, // Allow all origins for dev
                methods: ["GET", "POST"],
                credentials: true 
            },
            transports: ['websocket', 'polling']
        });

        // ---------------------------------------------------------
        // 1. SETUP REDIS ADAPTER (MANUAL CONNECTION FIX)
        // ---------------------------------------------------------
        try {
            const config = redis.redisConfig; // Get config from step 1

            if (!config) throw new Error("Redis Config missing");

            // Create FRESH connections for Pub/Sub
            const pubClient = new IORedis(config);
            const subClient = new IORedis(config);

            // Wait for them to be ready
            await Promise.all([
                new Promise((resolve) => pubClient.once('ready', resolve)),
                new Promise((resolve) => subClient.once('ready', resolve))
            ]);

            io.adapter(createAdapter(pubClient, subClient));
            logger.info("✅ Socket.io Redis Adapter Connected");
            
        } catch (err) {
            logger.error("❌ Socket.io Adapter Failed (Running in Memory Mode):", err.message);
        }

        // ---------------------------------------------------------
        // 2. AUTH MIDDLEWARE
        // ---------------------------------------------------------
        io.use(async (socket, next) => {
            const token = socket.handshake.auth.token || socket.handshake.query.token;
            if (!token) return next(new Error('Authentication error'));

            try {
                const decoded = jwt.verify(token, process.env.JWT_SECRET);
                
                // Validate Session in Redis
                const storedToken = await redis.get(`auth:session:${decoded.id}`);
                if (!storedToken || storedToken !== token) {
                    return next(new Error('Session expired'));
                }

                socket.user = decoded; 
                next();
            } catch (err) {
                return next(new Error('Authentication error'));
            }
        });

        // ---------------------------------------------------------
        // 3. CONNECTION EVENTS
        // ---------------------------------------------------------
        io.on('connection', async (socket) => {
            const userId = socket.user.id;
            const username = socket.user.username;

            logger.info(`🔌 [Socket] Connected: ${username} (${userId})`);
            socket.join(`user:${userId}`);
            
            // Handlers
            qrHandler(io, socket);
            matchSocketHandler(io, socket);
            lobbyHandler(io, socket);
            transferHandler(io, socket);

            // Notify Online
            try {
                await UserSocketManager.addUser(userId, socket.id); 
                socket.broadcast.emit('user_status_update', { userId, isOnline: true });
            } catch (e) {}

            socket.on('disconnect', async () => {
                await UserSocketManager.removeUser(userId);
                socket.broadcast.emit('user_status_update', { userId, isOnline: false });
            });
        });
        
        return io;
    },

    getIO: () => {
        if (!io) throw new Error("Socket.io not initialized!");
        return io;
    }
};