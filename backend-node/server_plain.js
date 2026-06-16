/**
 * file: backend-node/server.js
 * updated by : yassuki & AI Assistant
 * updated date: 2025-12-26
 * description: Entry point server dengan dukungan Redis Adapter & Queue Worker.
 */

require('dotenv').config();
const http = require('http');
const app = require('./src/app');

// 1. Import Singleton Socket Manager
const socketManager = require('./src/socket/io'); 

// 2. [BARU] Import Service untuk Worker Antrian Match
const PendingMatchService = require('./src/services/PendingMatchService');

// Import Worker & Config
require('./src/workers/stockfishWorker');
require('./src/config/database'); 

const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0'; 

// Ubah fungsi menjadi ASYNC karena inisialisasi Redis butuh await
async function startServer() {
  const server = http.createServer(app);
  
  // 3. Inisialisasi Socket via Manager (WAIT FOR REDIS)
  try {
      // Kita tunggu sampai Redis Pub/Sub connect
      await socketManager.init(server);
      console.log("✅ Socket.io Initialized (Redis Adapter Connected)");
  } catch (err) {
      console.error("❌ Failed to initialize Socket.io:", err);
      process.exit(1); // Matikan server jika Redis gagal (Fatal Error)
  }

  // 4. [PENTING] Jalankan Worker untuk Antrian Matchmaking
  // Mengecek antrian setiap 5 detik
  setInterval(() => {
      PendingMatchService.processMatchQueue();
  }, 5000);
  console.log("✅ Matchmaking Queue Worker Started (Interval: 5s)");

  // 5. Jalankan Server Listen
  server.listen(PORT, HOST, () => {
    console.log(`--------------------------------------------------`);
    console.log(`🚀 Server Node.js running on http://${HOST}:${PORT}`);
    console.log(`📡 Socket.io: Ready (Redis Mode)`);
    console.log(`--------------------------------------------------`);
  });
  
  // Handle Error Port Used
  server.on('error', (e) => {
    if (e.code === 'EADDRINUSE') {
      console.error(`❌ Port ${PORT} is already in use.`);
      process.exit(1);
    }
  });
}

startServer();