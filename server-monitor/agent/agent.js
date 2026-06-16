/**
 * AGENT MONITOR - V6 (Added Ping & Latency)
 * Features: CPU, Mem, Disk, Network (Graph+IPs), Redis, MySQL, Apache, Nginx, PM2, Reboot, PING.
 */

const io = require('socket.io-client');
const si = require('systeminformation');
const pm2 = require('pm2');
const redis = require('redis');
const mysql = require('mysql2/promise');
const ping = require('ping'); // <--- NEW LIBRARY
const os = require('os');
const { exec } = require('child_process');
require('dotenv').config();

// --- CONFIGURATION ---
const DASHBOARD_URL = process.env.DASHBOARD_URL || 'http://localhost:3000';
const AGENT_ID = process.env.AGENT_ID || os.hostname();
const AGENT_NAME = process.env.AGENT_NAME || AGENT_ID;
const WEB_PORTS = (process.env.WEB_PORTS || '80,443').split(',');

console.log(`🚀 Agent Started: ${AGENT_NAME}`);
console.log(`📡 Dashboard: ${DASHBOARD_URL}`);

// --- REDIS CONNECTION ---
let redisClient = null;
if (process.env.ENABLE_REDIS === 'true') {
    redisClient = redis.createClient({ url: process.env.REDIS_URL || 'redis://localhost:6379' });
    redisClient.on('error', (err) => {}); 
    (async () => { try { await redisClient.connect(); console.log('✅ Redis Connected'); } catch(e){} })();
}

// --- MYSQL CONNECTION ---
let mysqlPool = null;
if (process.env.ENABLE_MYSQL === 'true') {
    mysqlPool = mysql.createPool({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASS,
        database: process.env.DB_NAME,
        waitForConnections: true,
        connectionLimit: 1,
        queueLimit: 0
    });
    console.log('✅ MySQL Enabled');
}

// --- SOCKET CONNECTION ---
const socket = io(DASHBOARD_URL, {
    auth: { agentId: AGENT_ID, secret: process.env.AGENT_SECRET, name: AGENT_NAME },
    reconnection: true
});

socket.on('connect', () => console.log('✅ Connected to Dashboard'));
socket.on('connect_error', (e) => console.log(`❌ Connection Error: ${e.message}`));

// --- HELPER FUNCTIONS ---

// 1. Internet Ping (8.8.8.8)
async function getInternetPing() {
    try {
        const res = await ping.promise.probe('8.8.8.8', { timeout: 1 });
        return res.isAlive ? Math.floor(res.time) : 999;
    } catch (e) { return -1; }
}

// 2. Nginx Stats
function getNginxStats() {
    return new Promise(resolve => {
        exec('curl -s --max-time 1 "http://127.0.0.1/nginx_status"', (error, stdout, stderr) => {
            if (error || stderr || !stdout) return resolve({});
            try {
                const lines = stdout.split('\n');
                const active = lines[0].match(/\d+/)[0];
                const rw = lines[3].trim().split(' ');
                resolve({ status: 'Active', active_connections: active, reading: rw[1], writing: rw[3], waiting: rw[5] });
            } catch (e) { resolve({}); }
        });
    });
}

// 3. Apache Stats
function getApacheStats() {
    return new Promise(resolve => {
        exec('curl -s --max-time 1 "http://localhost/server-status?auto"', (error, stdout, stderr) => {
            if (error || stderr) return resolve({});
            try {
                const stats = { status: 'Active' };
                stdout.split('\n').forEach(line => {
                    const parts = line.split(':');
                    if (parts.length >= 2) stats[parts[0].trim()] = parts.slice(1).join(':').trim();
                });
                resolve(stats);
            } catch (e) { resolve({}); }
        });
    });
}

// 4. MySQL Stats
async function getMysqlStats(allProcesses) {
    if (!mysqlPool) return { status: 'Disabled' };
    
    // Cari Resource Usage OS
    let mysqlCpu = 0, mysqlMem = 0;
    const sqlProc = allProcesses.find(p => ['mysqld', 'mariadbd'].includes(p.name) || p.command.includes('mysqld'));
    if (sqlProc) { mysqlCpu = sqlProc.cpu.toFixed(1); mysqlMem = (sqlProc.memRss / 1024 / 1024).toFixed(0); }

    try {
        const [stRows] = await mysqlPool.query("SHOW GLOBAL STATUS WHERE Variable_name IN ('Threads_connected', 'Threads_running', 'Uptime', 'Questions', 'Slow_queries')");
        const st = {}; stRows.forEach(r => st[r.Variable_name] = r.Value);

        // --- PERBAIKAN DISINI ---
        // 1. Gunakan FULL PROCESSLIST agar text query tidak terpotong
        const [procRows] = await mysqlPool.query("SHOW FULL PROCESSLIST");
        
        const processList = procRows
            // .filter(p => p.State !== 'init') // <--- BARIS INI SAYA HAPUS AGAR SEMUA MUNCUL
            .slice(0, 50) // Naikkan limit dari 10 ke 50
            .map(p => ({
                id: p.Id, 
                user: p.User, 
                db: p.db || '-', // Handle jika DB null
                time: p.Time, 
                state: p.Command === 'Sleep' ? 'Sleep' : (p.State || p.Command), // Tampilkan Status yang jelas
                // Tampilkan Query. Jika Sleep, tampilkan kosong atau label
                info: p.Info ? p.Info : (p.Command === 'Sleep' ? '' : '-') 
            }));

        const uptime = parseInt(st['Uptime']) || 1;
        const qps = (parseInt(st['Questions']) / uptime).toFixed(2);

        return {
            status: 'Online',
            resource: { cpu: mysqlCpu, mem: mysqlMem },
            connections: st['Threads_connected'],
            running: st['Threads_running'],
            slow_queries: st['Slow_queries'],
            qps: qps,
            uptime: st['Uptime'],
            process_list: processList
        };
    } catch (e) { return { status: 'Offline', error: e.message }; }
}

// 5. Redis Stats
async function getRedisStats(allProcesses) {
    let redisCpu = 0;
    const redisProc = allProcesses.find(p => p.name === 'redis-server' || p.command.includes('redis-server'));
    if (redisProc) redisCpu = redisProc.cpu.toFixed(2);
    if (!redisClient || !redisClient.isOpen) return { status: 'Down', cpu: 0, keys: [] };
    try {
        const infoRaw = await redisClient.info();
        const usedMem = infoRaw.match(/used_memory_human:(.*)/)?.[1] || '0B';
        const clientCount = infoRaw.match(/connected_clients:(.*)/)?.[1] || '0';
        const keys = await redisClient.keys('*');
        const limitedKeys = keys.slice(0, 50);
        const detailedKeys = await Promise.all(limitedKeys.map(async (key) => {
            try { return { name: key, type: (await redisClient.type(key)).toUpperCase() }; } catch { return { name: key, type: 'UNK' }; }
        }));
        return { status: 'Up', cpu: redisCpu, memory: usedMem, clients: clientCount, total_keys: keys.length, key_list: detailedKeys };
    } catch (e) { return { status: 'Error', cpu: 0, keys: [] }; }
}

// 6. PM2 Stats
function getPm2Status() {
    return new Promise((resolve) => {
        pm2.connect((err) => {
            if (err) return resolve([]);
            pm2.list((err, list) => {
                pm2.disconnect();
                if (err || !list) return resolve([]);
                resolve(list.map(p => ({
                    pid: p.pid, pm_id: p.pm_id, name: p.name, status: p.pm2_env.status,
                    memory: (p.monit.memory / 1024 / 1024).toFixed(0), cpu: Number(p.monit.cpu).toFixed(1)
                })));
            });
        });
    });
}

// 7. Web Connections
function getWebConnections() {
    return new Promise(r => {
        exec(`netstat -an | grep ESTABLISHED | grep -E ":(${WEB_PORTS.join('|')}) " | wc -l`, (e, out) => r(parseInt(out)||0));
    });
}

// --- MAIN LOOP ---
setInterval(async () => {
    if (!socket.connected) return;

    try {
        const [cpu, mem, fs, netStats, processes, osInfo, webConn, pm2Data, netInterfaces, apacheData, nginxData, internetPing] = await Promise.all([
            si.currentLoad(), si.mem(), si.fsSize(), si.networkStats(), si.processes(), si.osInfo(),
            getWebConnections(), getPm2Status(), si.networkInterfaces(), 
            getApacheStats(), getNginxStats(), getInternetPing() // <--- Added Ping
        ]);

        const mysqlData = await getMysqlStats(processes.list);
        const redisData = await getRedisStats(processes.list);

        let totalRx = 0, totalTx = 0;
        if(Array.isArray(netStats)) netStats.forEach(i => { totalRx += (i.rx_sec||0); totalTx += (i.tx_sec||0); });

        const interfacesList = Array.isArray(netInterfaces) ? netInterfaces : [netInterfaces];
        const formattedInterfaces = interfacesList.map(iface => ({
            iface: iface.iface, ip4: iface.ip4||'', ip6: iface.ip6||'', state: iface.operstate
        }));

        const topProcs = processes.list.sort((a, b) => b.cpu - a.cpu).slice(0, 5)
            .map(p => ({ pid: p.pid, name: p.name, cpu: p.cpu.toFixed(1), user: p.user, mem: p.mem.toFixed(1) }));

        const rootDisk = fs.find(d => d.mount === '/') || fs[0];

        socket.emit('agent-metrics', {
            agentId: AGENT_ID,
            sent_at: Date.now(), // <--- Timestamp untuk hitung Latency
            os: { hostname: osInfo.hostname, distro: osInfo.distro, uptime: os.uptime() },
            cpu: { load: cpu.currentLoad.toFixed(1), cores: cpu.cpus.length },
            memory: { percent: ((mem.active/mem.total)*100).toFixed(1), used: mem.active, total: mem.total },
            disk: rootDisk ? { percent: rootDisk.use.toFixed(1), used: rootDisk.used, size: rootDisk.size } : {},
            network: { rx: totalRx, tx: totalTx, interfaces: formattedInterfaces, ping: internetPing }, // <--- Add Ping
            mysql: mysqlData,
            redis: redisData,
            pm2: pm2Data,
            web_conn: webConn,
            apache: apacheData,
            nginx: nginxData,
            top_procs: topProcs
        });

    } catch (e) { console.error('Metrics Error:', e.message); }
}, 3000);

// --- ACTIONS ---
socket.on('server-command:reboot-os', () => exec(process.platform === 'win32' ? 'shutdown /r /t 0' : 'sudo /sbin/shutdown -r now', (e) => sendResult('reboot-os', !e, e?e.message:'Rebooting...')));
socket.on('server-command:pm2-restart', (id) => pm2.connect(() => pm2.restart(id, (e) => { pm2.disconnect(); sendResult('pm2-restart', !e, e?e.message:'Success'); })));
socket.on('server-command:delete-redis-key', async (k) => { try { await redisClient.del(k); sendResult('delete-redis-key', true, `Deleted ${k}`); } catch(e){ sendResult('delete-redis-key', false, e.message); }});
socket.on('server-command:kill-process', (p) => { try { process.kill(p, 'SIGKILL'); sendResult('kill-process', true, `Killed PID ${p}`); } catch(e){ sendResult('kill-process', false, e.message); }});
function sendResult(cmd, success, msg) { socket.emit('command-result', { command: cmd, success, msg, agentId: AGENT_ID }); }