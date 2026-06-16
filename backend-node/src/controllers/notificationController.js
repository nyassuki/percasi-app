/**
 * file: backend-node/src/controllers/notificationController.js
 * created by : yassuki
 * created date: 2025-12-11
 */

const NotificationModel = require('../models/notificationModel');
const notifQueue = require('../queues/notificationQueue');
const pool = require('../config/database'); // Asumsi pakai MySQL
const logger = require('../utils/logger');

class NotificationController {

    // 1. Get My Notifications
    static async getMyNotifs(req, res) {
        try {
            const userId = req.user.id;
            const data = await NotificationModel.getUserNotifications(userId, 50); // Ambil 50 terakhir
            res.status(200).json({
                status: 'success',
                data
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    // 2. Mark as Read
    static async markRead(req, res) {
        try {
            const userId = req.user.id;
            const {
                id
            } = req.params;
            const unread = await NotificationModel.markAsRead(id, userId);
             
            res.status(200).json({
                status: 'success',
                data: unread
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }

    // 3. Blast Notification (PERBAIKAN UTAMA DI SINI)
    // - Masuk ke dalam Class
    // - Ditambahkan keyword 'static' dan 'async'
    static async blastNotification(req, res) {
        try {
            const {
                title,
                message,
                type,
                target
            } = req.body;

            // A. Ambil Target User
            let users = [];

            if (target === 'all') {
                // Ambil semua ID user dari database (Query Ringan)
                const [rows] = await pool.execute("SELECT id FROM users");
                users = rows;
            } else if (Array.isArray(target)) {
                // Jika target array ID spesifik [1, 2, 5]
                users = target.map(id => ({
                    id
                }));
            }

            if (users.length === 0) {
                return res.status(400).json({
                    message: "Tidak ada user target."
                });
            }

            // B. Siapkan Data Job (Bulk)
            // Kita mapping array user menjadi array Job BullMQ
            const jobs = users.map(user => ({
                name: 'send-notif', // Nama job (bebas)
                data: {
                    userId: user.id,
                    title: title,
                    message: message,
                    type: type || 'info'
                },
                opts: {
                    removeOnComplete: true, // Hapus dari redis kalau sukses (biar hemat memori)
                    attempts: 3 // Coba lagi 3x kalau gagal
                }
            }));

            // C. Masukkan ke Antrian Sekaligus (Sangat Cepat)
            await notifQueue.addBulk(jobs);

            // D. Response Cepat ke Admin
            // Kita tidak menunggu notifikasi selesai dikirim, kita langsung balas "OK"
            res.json({
                status: true,
                message: `Proses blasting ke ${users.length} user dimulai di latar belakang.`,
                jobCount: users.length
            });

        } catch (error) {
            logger.error("Blast Error:", error);
            res.status(500).json({
                message: "Internal Server Error"
            });
        }
    }
    static async getUnreadCount(req, res) {
        try {
            const userId = req.user.id;
            // Hitung jumlah yang is_read = 0
            const [rows] = await pool.execute(
                "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [userId]
            );
            

            res.json({
                status: 'success',
                count: rows[0].count
            });
        } catch (error) {
            res.status(500).json({
                message: error.message
            });
        }
    }
}

module.exports = NotificationController;