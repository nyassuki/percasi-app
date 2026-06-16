require('dotenv').config();
const https = require('https');
const http = require('http');
const fs = require('fs');
const path = require('path'); // Add path module
const app = require('./src/app');
const socketManager = require('./src/socket/io'); 
const PendingMatchService = require('./src/services/PendingMatchService');

// Use 0.0.0.0 to allow access from local network (192.168.x.x)
const PORT = process.env.PORT || 3000;
const HOST = '0.0.0.0'; 

// [CRITICAL] Update these paths to where your actual certs are
// If using Vite's mkcert, they might be in your frontend folder
const sslOptions = {
  key: fs.existsSync('certs/key.pem') ? fs.readFileSync('certs/key.pem') : null,
  cert: fs.existsSync('certs/cert.pem') ? fs.readFileSync('certs/cert.pem') : null,
};

async function startServer() {
  let server;

  // SSL Logic
  if (sslOptions.key && sslOptions.cert) {
    server = https.createServer(sslOptions, app);
    console.log("🔒 SSL Certificate Loaded. Protocol: HTTPS/WSS");
  } else {
    server = http.createServer(app);
    console.warn("⚠️  No SSL Certs found at ./certs/. Server running on HTTP.");
    console.warn("⚠️  If Frontend is HTTPS, this will cause ERR_SSL_PROTOCOL_ERROR.");
  }
  
  // Init Socket
  try {
      await socketManager.init(server);
      console.log("✅ Socket.io Initialized");
  } catch (err) {
      console.error("❌ Socket Init Failed:", err);
      process.exit(1);
  }

  // Workers
  setInterval(() => PendingMatchService.processMatchQueue(), 10000);

  // Listen
  server.listen(PORT, HOST, () => {
    const protocol = sslOptions.key ? 'https' : 'http';
    console.log(`--------------------------------------------------`);
    console.log(`🚀 Server running on ${protocol}://${HOST}:${PORT}`);
    console.log(`--------------------------------------------------`);
  });
}

startServer();