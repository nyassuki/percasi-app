const express = require('express');
const bodyParser = require('body-parser');
const dotenv = require('dotenv');
const paymentService = require('./services/paymentService');
const xenditService = require('./services/xenditService'); // Import baru

dotenv.config();

const app = express();
const PORT = process.env.PORT;

app.use(bodyParser.json());

// Middleware Validasi Token Xendit
const verifyXenditToken = (req, res, next) => {
    const callbackToken = req.headers['x-callback-token'];
    
    // Wajib cocok dengan yang ada di Dashboard Xendit
    if (callbackToken !== process.env.XENDIT_CALLBACK_TOKEN) {
        console.error("❌ Invalid Xendit Callback Token");
        return res.status(401).json({ message: "Unauthorized" });
    }
    next();
};

// 1. ROUTE WEBHOOK (Dipanggil oleh Server Xendit)
app.post('/webhook/xendit/va', verifyXenditToken, async (req, res) => {
    try {
        console.log("🔔 Xendit Webhook Received");
        await paymentService.processXenditWebhook(req.body);
        res.status(200).json({ message: 'OK' });
    } catch (err) {
        console.error("Webhook Error:", err.message);
        // Xendit akan mengulang kirim jika kita return 500, jadi hati-hati.
        // Return 200 jika errornya karena data duplikat.
        res.status(500).json({ message: 'Internal Error' });
    }
});

// 2. ROUTE GENERATE VA (Dipanggil oleh User App/Admin)
// Endpoint ini ditembak oleh Backend User App (via API Key Internal)
app.post('/api/create-va', async (req, res) => {
    try {
        const { userId, bank, name } = req.body;
        // Panggil Service Xendit
        const vaData = await xenditService.createFixedVA(userId, bank, name);
        
        // Simpan VA ke database user_virtual_accounts (Logic DB bisa ditaruh di paymentService juga)
        // ... (Kode insert DB di sini atau di User App) ...

        res.json({ success: true, data: vaData });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});
app.post('/api/generate-all-va', async (req, res) => {
    try {
        const { userId, name } = req.body;
        
        if (!userId || !name) return res.status(400).json({ error: "Missing Data" });

        // Proses di background (fire and forget) agar register user tidak loading lama
        // Kita tidak pakai 'await' di sini agar respon instan, 
        // tapi untuk kestabilan awal, kita await saja dulu.
        const result = await xenditService.createBulkVA(userId, name);

        res.json({ success: true, data: result });
    } catch (err) {
        console.error("Bulk VA Error:", err);
        res.status(500).json({ success: false, error: err.message });
    }
});
app.listen(PORT, () => {
    console.log(`💰 Xendit Payment Middleware running on port ${PORT}`);
});