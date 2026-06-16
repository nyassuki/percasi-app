/**
 * file: backend-node/src/queues/notificationQueue.js
 * description: Mengatur antrian dan worker untuk blasting notifikasi
 */
const { Queue, Worker } = require('bullmq');
const NotificationService = require('../services/notificationService');
require('dotenv').config();
const logger = require('../utils/logger');

// Konfigurasi Koneksi Redis untuk BullMQ
const redisOptions = {
    host: process.env.REDIS_HOST || 'localhost',
    port: process.env.REDIS_PORT || 6379,
    password: process.env.REDIS_PASSWORD || undefined,
    maxRetriesPerRequest: null, // WAJIB NULL untuk BullMQ
};

// 1. Definisikan Queue (Tempat menampung job)
const notifQueue = new Queue('app-notifications', {
    connection: redisOptions
});

// 2. Definisikan Worker (Tukang Proses)
// Worker ini akan berjalan otomatis setiap ada job masuk ke Redis
const worker = new Worker('app-notifications', async (job) => {
    
    // Ambil data dari Job
    const { userId, title, message, type } = job.data;
    
    // Panggil Service Pengiriman
    // logger.info(`[Worker] Processing job for User ${userId}`);
    await NotificationService.sendToUser(userId, title, message, type);
    
}, {
    connection: redisOptions,
    concurrency: 50, // PENTING: Proses 50 notifikasi secara PARALEL (Sangat Cepat)
    limiter: {
        max: 1000,    // Maksimal 1000 job
        duration: 1000 // per 1 detik (Rate Limiting agar server tidak jebol)
    }
});

// Event Listener untuk monitoring (Opsional)
worker.on('completed', (job) => {
    // logger.info(`[Worker] Job ${job.id} selesai!`);
});

worker.on('failed', (job, err) => {
    logger.error(`[Worker] Job ${job.id} gagal: ${err.message}`);
});

module.exports = notifQueue;