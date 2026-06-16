/**
 * file: backend-node/src/socket/qrHandler.js
 * updated by: yassuki & AI Assistant
 * updated date: 2025-12-26
 * description: Handler QR Code untuk Play-on-the-spot (Direct Match)
 */

const { v4: uuidv4 } = require('uuid');
const redis = require('../config/redis'); 
const logger = require('../utils/logger');
const MatchModel = require('../models/matchModel'); // Wajib untuk create match DB

module.exports = (io, socket) => {
    
    // Helper Auth
    const requireAuth = () => {
        if (!socket.user || !socket.user.id) {
            socket.emit('qr_error', { message: 'Sesi tidak valid. Silakan login ulang.' });
            return false;
        }
        return true;
    };

    // ============================================================
    // 1. GENERATE QR CODE (HOST / WHITE PLAYER)
    // ============================================================
    socket.on('qr_generate', async () => {
        if (!requireAuth()) return;

        try {
            const userId = socket.user.id;
            const lobbyId = `game$$${uuidv4()}`; // Gunakan UUID bersih saja agar mudah dibaca
            
            // Simpan di Redis: " <lobbyId>" = userId
            // Expire dalam 300 detik (5 menit)
            await redis.set(` ${lobbyId}`, userId, 'EX', 300);

            // Kita tidak perlu join room khusus, karena notifikasi akan dikirim ke room 'user:{id}'
            
            // Kirim token ke Player A untuk dijadikan QR
            socket.emit('qr_generated', { lobbyId });
            logger.info(`[QR] User ${userId} generated QR Lobby ${lobbyId}`);

        } catch (err) {
            logger.error('[QR Generate] Error:', err);
            socket.emit('qr_error', { message: 'Gagal membuat QR Code.' });
        }
    });

    // ============================================================
    // 2. SCAN QR CODE (SCANNER / BLACK PLAYER)
    // ============================================================
    socket.on('qr_scan', async ({ lobbyId }) => {
        if (!requireAuth()) return;

        try {
            const scannerId = parseInt(socket.user.id); // Player B (Black)

            // 1. Ambil Host ID dari Redis
            const hostIdRaw = await redis.get(` ${lobbyId}`);
            if (!hostIdRaw) {
                return socket.emit('qr_error', { message: 'Kode QR tidak valid atau sudah kadaluarsa.' });
            }
            
            const hostId = parseInt(hostIdRaw); // Player A (White)

            // 2. Cegah Scan Diri Sendiri
            if (hostId === scannerId) {
                return socket.emit('qr_error', { message: 'Anda tidak bisa scan kode sendiri.' });
            }

            // 3. Hapus QR Dulu (Cegah Double Scan)
            await redis.del(` ${lobbyId}`);

            // 4. Ambil Data Lengkap Kedua User dari Lobby (Redis)
            // Ini penting untuk mendapatkan Avatar, Username, dan SocketID terbaru
            const rawHost = await redis.hget('lobby:users', hostId);
            const rawScanner = await redis.hget('lobby:users', scannerId);

            if (!rawHost || !rawScanner) {
                return socket.emit('qr_error', { message: 'Salah satu pemain sedang offline.' });
            }

            const hostUser = JSON.parse(rawHost);
            const scannerUser = JSON.parse(rawScanner);

            // Cek status
            if (hostUser.status === 'playing' || scannerUser.status === 'playing') {
                return socket.emit('qr_error', { message: 'Salah satu pemain sedang bermain.' });
            }

            logger.info(`[QR] Creating Match: Host ${hostUser.username} vs Scanner ${scannerUser.username}`);

            // 5. BUAT MATCH DI DATABASE & REDIS (Mirip logic lobbyHandler)
            const matchId = uuidv4(); // ID untuk Room Socket & Redis Key
            
            // A. Create DB Record
            const dbMatchId = await MatchModel.createMatch(
                hostId,    // White
                scannerId, // Black
                600,       // Default 10 Menit (Bisa disesuaikan)
                0          // Increment
            );

            if (!dbMatchId) throw new Error("Gagal membuat match database.");

            // B. Update Status ke Playing
            hostUser.status = 'playing';
            scannerUser.status = 'playing';

            // C. Siapkan Game State Awal
            const initialGameState = {
                matchId: matchId,
                dbId: dbMatchId,
                white: {
                    id: hostUser.id,
                    username: hostUser.username,
                    avatar: hostUser.avatar_url
                },
                black: {
                    id: scannerUser.id,
                    username: scannerUser.username,
                    avatar: scannerUser.avatar_url
                },
                fen: 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
                turn: 'white',
                history: [],
                status: 'active',
                timeControl: 600,
                createdAt: Date.now()
            };

            // D. Eksekusi Redis (Atomic-like)
            await Promise.all([
                redis.hset('lobby:users', hostId, JSON.stringify(hostUser)),
                redis.hset('lobby:users', scannerId, JSON.stringify(scannerUser)),
                redis.set(`match:${matchId}`, JSON.stringify(initialGameState), 'EX', 7200)
            ]);

            // E. Broadcast Update Lobby (User jadi orange)
            // Note: getLobbyList harusnya di-export dari lobbyHandler atau utils, 
            // tapi disini kita skip broadcast lobby list demi simplisitas, 
            // atau copas logic getLobbyList disini jika perlu.
            
            // F. EMIT START GAME
            const startPayload = {
                match_id: matchId, // UUID
                matchId: dbMatchId, // DB ID
                whiteId: hostId,
                blackId: scannerId,
                fen: initialGameState.fen
            };

            // Kirim ke Host (White)
            io.to(`user:${hostId}`).emit('cmatch_start', startPayload);
            
            // Kirim ke Scanner (Black)
            // Scanner adalah socket yang me-request ini, jadi bisa pakai socket.emit juga,
            // tapi pakai io.to(`user:...`) lebih aman dan konsisten.
            io.to(`user:${scannerId}`).emit('tmatch_start', startPayload);

            logger.info(`[QR] Match Started via QR: ${matchId}`);

        } catch (err) {
            logger.error("QR Scan Error:", err);
            socket.emit('qr_error', { message: 'Gagal memproses pertandingan.' });
        }
    });
};