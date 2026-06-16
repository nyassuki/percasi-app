/**
 * file: backend-node/src/services/NotificationService.js
 * description: Service untuk kirim notifikasi realtime (Toast/Alert) via Socket Room
 */

const socketIoWrapper = require('../socket/io');
const UserSocketManager = require('../utils/UserSocketManager'); // Untuk cek status online (Redis)
const logger = require('../utils/logger');

class NotificationService {

    /**
     * Kirim notifikasi ke User tertentu
     * @param {number|string} userId - ID User tujuan
     * @param {string} title - Judul Notifikasi
     * @param {string} message - Isi Pesan
     * @param {string} type - 'success', 'error', 'info', 'warning'
     */
    static async sendToUser(userId, title, message, type = 'info') {
        try {
            const io = socketIoWrapper.getIO();
            
            // [FIX] Gunakan Format Room yang Konsisten dengan io.js
            // Format: "user:<ID>" (Pakai titik dua, bukan underscore)
            const targetRoom = `user:${userId}`;

            // (Opsional) Cek dulu apakah user online di Redis agar log akurat
            // Jika Anda ingin "Fire and Forget", bagian if(isOnline) bisa dihapus dan langsung emit.
            const isOnline = await UserSocketManager.getUser(userId); 

            if (isOnline) {
                // [FIX] Emit ke Room, bukan socketId specific
                // Ini aman untuk Load Balancing antar server
                io.to(targetRoom).emit('app_notification', {
                    title,
                    message,
                    type,
                    timestamp: Date.now()
                });
                
                logger.info(`[Notification] Sent to User ${userId}: ${title}`);
                return true;
            } else {
                logger.info(`[Notification] User ${userId} is Offline (Not sent)`);
                // TODO: Di sini Anda bisa simpan ke database 'notifications' agar user bisa baca saat login nanti
                return false;
            }

        } catch (error) {
            logger.error("[Notification] Error:", error);
            return false;
        }
    }

    /**
     * Kirim ke SEMUA User yang Online (Broadcast)
     */
    static async broadcast(title, message, type = 'info') {
        try {
            const io = socketIoWrapper.getIO();
            
            // io.emit akan otomatis disebar ke semua server node.js via Redis Adapter
            io.emit('app_notification', { 
                title, 
                message, 
                type,
                timestamp: Date.now()
            });
            
            logger.info(`[Notification] Broadcast sent: ${title}`);
        } catch (error) {
            logger.error("[Notification Broadcast] Error:", error);
        }
    }
}

module.exports = NotificationService;