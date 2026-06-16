/**
 * file: backend-node/src/cron/matchCron.js
 */
const cron = require('node-cron');
const redis = require('../config/redis'); // Menggunakan config redis Anda
const PendingMatchService = require('../services/PendingMatchService');
const TournamentService = require('../services/tournamentService');
const AuditIntegrityCheck = require('../scripts/audit-integrity');
const CryptoMonitorService = require('../services/CryptoMonitorService');
const CryptoPaymentModel = require('../models/CryptoPaymentModel');
const workerMaintenance = require('../workers/worker-maintenance');
const logger = require('../utils/logger');

const LOCK_KEY = 'lock:cron:routine_tasks';
const LOCK_TTL = 60; // Kunci otomatis lepas setelah 60 detik jika crash

const initMatchCron = () => {
    logger.info("[Cron] Match Queue Processor Initialized with Redis Locking.");

    // 1. Antrean Rutin (Setiap 10 Detik)
    cron.schedule('*/10 * * * * *', async () => {
        // ATOMIC LOCK: 'NX' = Only set if not exists, 'EX' = Expiry
        const lockAcquired = await redis.set(LOCK_KEY, 'locked', 'NX', 'EX', LOCK_TTL);

        if (!lockAcquired) {
            // Jika gagal ambil lock, artinya proses sebelumnya masih jalan
            // logger.debug("[Cron] Previous task still running, skipping this cycle.");
            return;
        }

        try {
            // Jalankan semua tugas berat
            await PendingMatchService.processMatchQueue();
            await TournamentService.updateTournamentStatuses();
            await AuditIntegrityCheck.runGlobalAudit();
            await CryptoMonitorService.scan();
            await CryptoPaymentModel.markExpiredPayments();
        } catch (error) {
            logger.error("[Cron Error] Failed to process routine tasks:", error);
        } finally {
            // PENTING: Selalu hapus lock setelah selesai agar proses berikutnya bisa jalan
            await redis.del(LOCK_KEY);
        }
    });

    // 2. Pemeliharaan Harian (Jam 00:00)
    cron.schedule('0 0 * * *', async () => {
        try {
            logger.info("[Cron] Memulai jadwal optimasi database harian...");
            await workerMaintenance.runMaintenanceAndOptimize();
        } catch (error) {
            logger.error("[Cron Error] Maintenance worker failed:", error);
        }
    }, {
        scheduled: true,
        timezone: "Asia/Jakarta"
    });
};

module.exports = initMatchCron;