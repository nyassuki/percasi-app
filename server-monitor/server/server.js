/**
 * SERVER DASHBOARD (HUB) - FINAL AUTO-DISCOVERY VERSION
 * Features: Auth, Realtime Metrics Relay, Remote Command Proxy, Auto-Discovery.
 */

require('dotenv').config();
const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const session = require('express-session');
const bodyParser = require('body-parser');

// --- SETUP APP ---
const app = express();
const server = http.createServer(app);
const io = socketIo(server);

// --- MIDDLEWARE ---
app.set('view engine', 'ejs');
app.use(express.static('public')); 
app.use(bodyParser.urlencoded({ extended: true }));
app.use(session({
    secret: process.env.SESSION_SECRET || 'secret_key',
    resave: false,
    saveUninitialized: true
}));

// --- IN-MEMORY STORAGE ---
let agents = {}; 
let agentSockets = {}; // Map: AgentID -> SocketID

// --- AUTH ---
const authMiddleware = (req, res, next) => {
    if (req.session.loggedin) next();
    else res.redirect('/login');
};

// --- ROUTES ---
app.get('/login', (req, res) => res.render('login', { error: null }));

app.post('/login', (req, res) => {
    const { username, password } = req.body;
    if (username === process.env.ADMIN_USER && password === process.env.ADMIN_PASS) {
        req.session.loggedin = true;
        req.session.user = username;
        res.redirect('/');
    } else {
        res.render('login', { error: 'Invalid Credentials' });
    }
});

app.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/login');
});

app.get('/', authMiddleware, (req, res) => {
    res.render('dashboard', { user: req.session.user, agents: agents });
});


// --- SOCKET.IO LOGIC ---

io.on('connection', (socket) => {
    
    const auth = socket.handshake.auth;

    // ===========================
    // 1. LOGIC AGENT CONNECTED
    // ===========================
    if (auth.agentId) {
        const agentId = auth.agentId;
        const agentName = auth.name || agentId;

        console.log(`🔌 AGENT CONNECTED: ${agentName} (${agentId})`);

        // Register Socket
        agentSockets[agentId] = socket.id;
        
        let isNewAgent = false;

        // Cek apakah ini agent baru atau lama yg reconnect
        if (!agents[agentId]) {
            isNewAgent = true;
            agents[agentId] = {
                info: { id: agentId, name: agentName, status: 'online' },
                metrics: {}, 
                lastSeen: new Date()
            };
        } else {
            agents[agentId].info.status = 'online';
            agents[agentId].info.name = agentName;
        }

        // NOTIFIKASI KE DASHBOARD
        if (isNewAgent) {
            // Jika Baru: Kirim seluruh object agent agar UI bisa render sidebar baru
            io.emit('ui-new-agent', { id: agentId, data: agents[agentId] });
        } else {
            // Jika Lama: Cukup update status jadi online
            io.emit('agent-status-change', { id: agentId, status: 'online' });
        }

        // Handle Metrics Stream
        socket.on('agent-metrics', (payload) => {
            if (agents[agentId]) {
                agents[agentId].metrics = payload;
                agents[agentId].lastSeen = new Date();
                // Broadcast ke UI
                io.emit('ui-update-metrics', { agentId, data: payload });
            }
        });

        // Handle Command Result
        socket.on('command-result', (result) => {
            io.emit('ui-command-notification', result);
        });

        // Handle Disconnect
        socket.on('disconnect', (reason) => {
            console.log(`❌ AGENT OFFLINE: ${agentId}`);
            if (agents[agentId]) agents[agentId].info.status = 'offline';
            delete agentSockets[agentId];
            io.emit('agent-status-change', { id: agentId, status: 'offline' });
        });
    } 
    
    // ===========================
    // 2. LOGIC DASHBOARD UI
    // ===========================
    else {
        // Kirim data inisial saat browser refresh
        socket.emit('ui-init-state', agents);

        // Forward Command ke Agent
        socket.on('send-command', (data) => {
            const { agentId, command, payload } = data;
            const targetSocketId = agentSockets[agentId];

            if (targetSocketId) {
                console.log(`📡 Command '${command}' -> ${agentId}`);
                io.to(targetSocketId).emit(`server-command:${command}`, payload);
            } else {
                socket.emit('ui-command-notification', { success: false, msg: 'Agent Offline / Not Found' });
            }
        });
    }
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`🚀 Server Dashboard running on http://localhost:${PORT}`);
});