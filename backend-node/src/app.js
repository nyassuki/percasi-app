/**
 * file: backend-node/src/app.js
 * description: App Config (Fixed PathError by removing redundant OPTIONS handler)
 */

const express = require('express');
const cors = require('cors');
const path = require('path');
const requestIp = require('request-ip'); 
const fs = require('fs');
const yaml = require('js-yaml');


require('dotenv').config(); 
require('./queues/notificationQueue');

const logger = require('./utils/logger'); 

// Import Routes
const matchRoutes = require('./routes/matchRoutes');
const tournamentRoutes = require('./routes/tournamentRoutes');
const authRoutes = require('./routes/authRoutes');
const financeRoutes = require('./routes/financeRoutes');
const adminRoutes = require('./routes/adminRoutes');
const notificationRoutes = require('./routes/notificationRoutes');
const contentRoutes = require('./routes/contentRoutes');
const masterRoutes = require('./routes/masterRoutes');
const userRoutes = require('./routes/userRoutes');
const paymentRoutes = require('./routes/paymentRoutes');
const botRoutes = require('./routes/botRoutes');
const walletRoute = require('./routes/walletRoutes');
const dashboard = require('./routes/dashboard'); 
const verification = require('./routes/verification');
const swaggerUi = require('swagger-ui-express');
const initMatchCron = require('./cron/matchCron');
const swaggerDocument = yaml.load(fs.readFileSync('src/openapi.yaml', 'utf8'));
const stockfishWorker = require('./workers/stockfishWorker');
const maintenanceMiddleware = require('./middlewares/maintenanceMiddleware');
initMatchCron();

const app = express();
app.use(maintenanceMiddleware);

// 1. Trust Proxy
app.set('trust proxy', true);
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerDocument));
// 2. Konfigurasi CORS
const corsOptions = {
    origin: true, 
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],
    credentials: true, 
    optionsSuccessStatus: 200
};

// [FIX] Gunakan Global Middleware saja.
// Jangan gunakan app.options('*', ...) karena menyebabkan PathError di library terbaru.
app.use(cors(corsOptions));

// 3. Middleware IP & Security Headers
app.use(requestIp.mw());

app.use((req, res, next) => {
    res.setHeader('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
    res.setHeader('Cross-Origin-Embedder-Policy', 'unsafe-none');
    res.setHeader('Cross-Origin-Resource-Policy', 'cross-origin');
    next();
});

// 4. Body Parsers
app.use(express.json({ limit: '10mb' })); 
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// 5. Static Files
app.use('/public', express.static(path.join(__dirname, '../public')));
app.use('/uploads', express.static(path.join(__dirname, '../uploads'))); 

// 6. API Routes
app.use('/api/auth', authRoutes);
app.use('/api/matches', matchRoutes);
app.use('/api/tournaments', tournamentRoutes);
app.use('/api/finance', financeRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/notifications', notificationRoutes);
app.use('/api/content', contentRoutes);
app.use('/api/master', masterRoutes);
app.use('/api/users', userRoutes);
app.use('/api/payment', paymentRoutes);
app.use('/api/bot', botRoutes);
app.use('/api/wallet', walletRoute);
app.use('/api/dashboard', dashboard);
app.use('/api/verification', verification);

// Health Check
app.get('/', (req, res) => {
    res.status(200).json({
        status: 'success',
        message: 'Percasi Chess Backend API Running',
        timestamp: new Date()
    });
});

// 7. Error Handling
app.use((req, res) => {
    res.status(404).json({ status: false, message: "Route API tidak ditemukan." });
});

app.use((err, req, res, next) => {
    logger.error('[SERVER ERROR]:', err.stack || err.message);
    const message = process.env.NODE_ENV === 'production' 
        ? "Terjadi kesalahan internal." 
        : err.message;
    res.status(500).json({ status: false, message });
});

module.exports = app;