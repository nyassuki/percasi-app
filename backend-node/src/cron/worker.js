// worker.js
require('dotenv').config();
const CryptoMonitorService = require('../services/CryptoMonitorService');
const CryptoPaymentModel = require('../models/CryptoPaymentModel');

console.log("Crypto Monitor Worker started...");

// Jalankan scanner setiap 1 menit
setInterval(async () => {
    try {
        await CryptoMonitorService.scan();
    } catch (err) {
        console.error("Worker Error:", err);
    }
}, 60000); 

// Jalankan pembersihan data expired setiap 5 menit
setInterval(async () => {
    try {
        const affected = await CryptoPaymentModel.markExpiredPayments();
        if (affected > 0) console.log(`${affected} payments marked as EXPIRED`);
    } catch (err) {
        console.error("Cleanup Error:", err);
    }
}, 300000);