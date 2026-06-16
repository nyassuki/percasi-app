/**
 * file: backend-node/server.js
 * description: Entry Point Server Backend (HTTPS / WSS ONLY)
 */

require('dotenv').config();
const https = require('https');
const fs = require('fs');
const path = require('path');
const app = require('./src/app');
const socketManager = require('./src/socket/io'); 

// Import Worker & Database
require('./src/workers/stockfishWorker');
require('./src/config/database'); 

// Import Service untuk Cron Job Matchmaking
const PendingMatchService = require('./src/services/PendingMatchService');

const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0'; 

async function startServer() {
  // 1. MEMUAT SERTIFIKAT SSL (WAJIB UNTUK HTTPS)
  // Pastikan file key.pem dan cert.pem ada di folder 'certs'
  const keyPath = path.join(__dirname, 'cert', 'selfsigned.key');
  const certPath = path.join(__dirname, 'cert', 'selfsigned.crt');

  if (!fs.existsSync(keyPath) || !fs.existsSync(certPath)) {
    console.error("\n❌ [FATAL ERROR] Sertifikat SSL tidak ditemukan!");
    console.error(`   Cek lokasi: ${keyPath}`);
    console.error(`   Cek lokasi: ${certPath}`);
    console.error("   Server HTTPS tidak bisa berjalan tanpa sertifikat.\n");
    process.exit(1);
  }

  const sslOptions = {
    key: fs.readFileSync(keyPath),
    cert: fs.readFileSync(certPath)
  };

  // 2. Buat Server HTTPS
  const server = https.createServer(sslOptions, app);
  console.log("🔒 SSL Certificate Loaded. Protocol: HTTPS/WSS");

  // 3. Inisialisasi Socket via Manager (WAIT FOR REDIS)
  try {
      await socketManager.init(server);
      console.log("✅ Socket.io Initialized (Redis Adapter Connected)");
  } catch (err) {
      console.error("❌ Failed to initialize Socket.io:", err);
      process.exit(1); 
  }

  // 4. Jalankan Worker Matchmaking Queue
  setInterval(() => {
      PendingMatchService.processMatchQueue();
  }, 5000);
  console.log("✅ Matchmaking Queue Worker Started (Interval: 5s)");

  // 5. Error Handling Port
  server.on('error', (e) => {
    if (e.code === 'EADDRINUSE') {
      console.error(`❌ Port ${PORT} sudah digunakan oleh aplikasi lain.`);
      process.exit(1);
    } else {
      console.error(`❌ Terjadi kesalahan server:`, e);
    }
  });

  // 6. Start Server
  server.listen(PORT, HOST, () => {
    console.log(`--------------------------------------------------`);
    console.log(`🚀 Percasi Chess Backend Running (SECURE MODE)`);
    console.log(`📡 URL: https://${HOST}:${PORT}`);
    console.log(`🛠️  Environment: ${process.env.NODE_ENV || 'development'}`);
    console.log(`--------------------------------------------------`);
  });
}

// Anti-Crash Handler
process.on('unhandledRejection', (reason, promise) => {
  console.error('Unhandled Rejection at:', promise, 'reason:', reason);
});

startServer();