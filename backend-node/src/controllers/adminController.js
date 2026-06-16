/**
 * file: backend-node/src/controllers/adminController.js
 * updated by : yassuki & AI Assistant
 * updated date: 2025-12-26
 * description: Controller Admin dengan Redis Session Support & Queue Handling.
 */

const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const AdminModel = require('../models/adminModel');
const NotificationService = require('../services/notificationService');
const PendingMatchService = require('../services/PendingMatchService');
const NotificationModel = require('../models/notificationModel');
const WalletService = require('../services/walletService');


const logger = require('../utils/logger');
const redis = require('../config/redis'); // [WAJIB] Import Redis

class AdminController {

    /**
     * Login khusus Admin.
     * POST /api/admin/login
     */
    static async login(req, res) {
        try {
            const { email, password } = req.body;

            // 1. Cek Admin di DB
            const admin = await AdminModel.findByEmail(email);
            if (!admin) {
                return res.status(401).json({ message: 'Email atau password salah.' });
            }

            // 2. Verifikasi Password
            const isMatch = await bcrypt.compare(password, admin.password_hash);
            if (!isMatch) {
                return res.status(401).json({ message: 'Email atau password salah.' });
            }

            // 3. Generate Token
            const token = jwt.sign({
                    id: admin.id,
                    email: admin.email,
                    role: 'admin' // Flag penting untuk adminMiddleware
                },
                process.env.JWT_SECRET, {
                    expiresIn: '12h'
                }
            );

            // 4. [REDIS ACTION] Simpan Sesi Admin
            // Ini WAJIB agar adminMiddleware bisa memvalidasi token
            // Key disamakan dengan format user: auth:session:{id}
            // Expire 12 Jam (43200 detik)
            await redis.set(`auth:session:${admin.id}`, token, 'EX', 43200);

            res.status(200).json({
                status: 'success',
                message: 'Admin login berhasil',
                token,
                admin: {
                    id: admin.id,
                    name: admin.full_name
                }
            });

        } catch (error) {
            logger.error("[Admin Login Error]", error);
            res.status(500).json({ message: error.message });
        }
    }

    /**
     * Mengirim Broadcast ke semua user.
     * POST /api/admin/broadcast
     */
    static async sendBroadcast(req, res) {
        try {
            const { title, message, type } = req.body;

            if (!title || !message) {
                return res.status(400).json({ message: 'Judul dan Pesan wajib diisi.' });
            }

            // Kirim via Socket (Redis Adapter Broadcast)
            await NotificationService.broadcast(title, message, type || 'info');
            
            // Simpan history ke DB
            await NotificationModel.create(0, title, message, type || 'info', null);
            
            res.status(200).json({
                status: 'success',
                message: 'Broadcast berhasil dikirim ke semua user online.'
            });

        } catch (error) {
            res.status(500).json({ message: error.message });
        }
    }

    /**
     * Mengirim Notif Private ke User tertentu.
     * POST /api/admin/notify-user
     */
    static async sendUserNotif(req, res) {
        try {
            const { userId, title, message, type } = req.body;
            
            logger.info(`[Admin] Sending notif to ${userId}`);
            
            await NotificationService.sendToUser(userId, title, message, type || 'info');

            res.status(200).json({
                status: 'success',
                message: `Notifikasi dikirim ke User ID ${userId}`
            });
        } catch (error) {
            res.status(500).json({ message: error.message });
        }
    }

    /**
     * Trigger Match Manual (Admin)
     * POST /api/admin/trigger-match
     */
    static async triggerMatch(req, res) {
        try {
            const { whitePlayerId, blackPlayerId } = req.body;

            if (!whitePlayerId || !blackPlayerId) {
                return res.status(400).json({ error: "White and Black player IDs are required" });
            }

            // Panggil Service (bisa return matchId string ATAU object status 'queued')
            const result = await PendingMatchService.createAdminMatch(whitePlayerId, blackPlayerId);

            // Handle jika masuk antrian (karena user sedang main/offline)
            if (typeof result === 'object' && result.status === 'queued') {
                return res.json({
                    status: true,
                    message: "Match queued. Players are currently busy or offline.",
                    data: result
                });
            }

            // Handle sukses instan
            res.json({
                status: true,
                message: "Invitation sent immediately.",
                matchId: result
            });

        } catch (e) {
            logger.error("[Trigger Match Error]", e);
            res.status(500).json({ error: e.message });
        }
    };

    /**
     * [BARU] Logout Admin
     * POST /api/admin/logout
     */
    static async logout(req, res) {
        try {
            const adminId = req.admin.id; // Dari middleware
            
            // Hapus sesi dari Redis
            await redis.del(`auth:session:${adminId}`);
            
            res.status(200).json({ status: true, message: "Admin logout berhasil." });
        } catch (error) {
            res.status(500).json({ message: error.message });
        }
    }
     
    /**
     * Endpoint: POST /api/v1/admin/wallet/repair
     * Deskripsi: Memperbaiki saldo wallet yang corrupt/tampered
     */
    static async repairWallet(req, res) {
        try {
            const { user_id } = req.body;

            if (!user_id) {
                return res.status(400).json({ message: 'User ID is required' });
            }

            // Panggil service rekonsiliasi
            const result = await WalletService.repairWalletIntegrity(user_id);

            return res.status(200).json({
                status: 'success',
                message: 'Wallet balance has been reconciled with transaction ledger.',
                data: result
            });
        } catch (error) {
            return res.status(500).json({
                status: 'error',
                message: error.message
            });
        }
    }
    /**
     * file: backend-node/src/controllers/adminController.js
     * deskripsi: Controller Admin dengan Audit Trail dan Granular Error Handling.
     */

    static async processWithdraw(req, res) {
        // 1. Inisialisasi metadata
        const adminId = req.admin?.id; 
        const { transaction_id, action, note } = req.body;
        const ipAddress = req.ip || req.headers['x-forwarded-for'];

        try {
            // 2. Validasi Input Dasar
            if (!transaction_id || !['approve', 'reject'].includes(action)) {
                return res.status(400).json({ 
                    status: 'error', 
                    message: 'ID transaksi atau aksi tidak valid.' 
                });
            }

            // 3. AMBIL SNAPSHOT LAMA (Old Values)
            // Ini penting agar kita tahu data awal sebelum disetujui/ditolak
            const [oldRows] = await Transaction.findByIdRaw(transaction_id); 
            if (!oldRows || oldRows.length === 0) {
                return res.status(404).json({ status: 'error', message: 'Transaksi tidak ditemukan.' });
            }
            const oldValues = oldRows[0];

            logger.info(`[ADMIN_ACTION] Admin ${adminId} attempting to ${action} TX: ${transaction_id}`);

            // 4. EKSEKUSI SERVICE (Internal logic: Saldo & Signature)
            const result = await WalletService.approveWithdrawal(transaction_id, action, adminId);

            // 5. AMBIL SNAPSHOT BARU (New Values)
            const [newRows] = await Transaction.findByIdRaw(transaction_id);
            const newValues = newRows[0];

            // 6. CATAT KE ADMIN AUDIT LOG
            // Kita panggil secara asinkron (tanpa await jika ingin respon cepat, 
            // atau dengan await untuk menjamin log tersimpan)
            await AuditService.logAdminAction({
                adminId,
                action: action === 'approve' ? 'APPROVE_WITHDRAWAL' : 'REJECT_WITHDRAWAL',
                targetType: 'transaction',
                targetId: transaction_id,
                oldValues: oldValues,
                newValues: newValues,
                reason: note || 'Diproses via Admin Dashboard',
                req: req // Mengirim object req untuk ekstraksi IP & User-Agent
            });

            // 7. Response Sukses
            return res.status(200).json({
                status: 'success',
                message: `Penarikan dana dengan ID ${transaction_id} telah berhasil di-${action}.`,
                data: { 
                    transaction_id,
                    new_status: result.status 
                }
            });

        } catch (error) {
            // 8. Granular Error Mapping
            let statusCode = 500;
            const msg = error.message;

            if (msg.includes('NOT_FOUND')) statusCode = 404;
            if (msg.includes('PROCESSED') || msg.includes('INTEGRITY')) statusCode = 400;

            logger.error(`[ADMIN_PROCESS_ERR] Admin: ${adminId} | TX: ${transaction_id} | Error: ${msg}`);

            return res.status(statusCode).json({ 
                status: 'error', 
                message: msg 
            });
        }
    }
    /**
     * [GET] /api/v1/admin/audit-logs
     * Mengambil daftar jejak audit dengan filter dan paginasi.
     */
    static async getAuditLogs(req, res) {
        try {
            const { 
                admin_id, 
                action, 
                target_type, 
                start_date, 
                end_date, 
                page = 1, 
                limit = 20 
            } = req.query;

            const offset = (page - 1) * limit;
            let query = `
                SELECT al.*, u.full_name as admin_name 
                FROM admin_audit_logs al
                JOIN users u ON al.admin_id = u.id
                WHERE 1=1
            `;
            const params = [];

            // 1. Dynamic Filtering
            if (admin_id) {
                query += ` AND al.admin_id = ?`;
                params.push(admin_id);
            }
            if (action) {
                query += ` AND al.action = ?`;
                params.push(action);
            }
            if (target_type) {
                query += ` AND al.target_type = ?`;
                params.push(target_type);
            }
            if (start_date && end_date) {
                query += ` AND al.created_at BETWEEN ? AND ?`;
                params.push(`${start_date} 00:00:00`, `${end_date} 23:59:59`);
            }

            // 2. Sorting & Pagination
            query += ` ORDER BY al.created_at DESC LIMIT ? OFFSET ?`;
            params.push(parseInt(limit), parseInt(offset));

            const [rows] = await pool.execute(query, params);

            // 3. Count Total untuk Pagination Frontend
            const [totalRows] = await pool.execute(`SELECT COUNT(*) as total FROM admin_audit_logs`);

            return res.status(200).json({
                status: 'success',
                pagination: {
                    current_page: parseInt(page),
                    per_page: parseInt(limit),
                    total_records: totalRows[0].total
                },
                data: rows
            });

        } catch (error) {
            logger.error(`[GET_AUDIT_LOGS_ERR] ${error.message}`);
            return res.status(500).json({ status: 'error', message: 'Gagal memuat log audit' });
        }
    }
    // Menyalakan Maintenance
    static async   enableMaintenance(message) {
        const data = {
            is_maintenance: true,
            message: message || "Sistem sedang maintenance.",
            allowed_ips: ["127.0.0.1"] // Masukkan IP Anda agar tetap bisa akses
        };
        await redis.set('system:maintenance', JSON.stringify(data));
        console.log("⚠️ System Maintenance: ON");
    }

    // Mematikan Maintenance (Normal)
    static async   disableMaintenance() {
        await redis.del('system:maintenance');
        console.log("✅ System Maintenance: OFF");
    }

}

module.exports = AdminController;