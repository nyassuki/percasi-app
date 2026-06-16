/**
 * file: backend-node/src/routes/dashboard.js
 */
const router = require('express').Router();
const DashboardController = require('../controllers/DashboardController'); // Pastikan path benar
const authMiddleware = require('../middlewares/authMiddleware');

// [FIX] Pastikan DashboardController.getStats ADA dan bukan undefined
if (!DashboardController.getGameStats) {
    console.error("❌ Error: DashboardController.getStats is undefined. Cek export controller.");
}

// Route: GET /api/dashboard/stats
router.get('/stats', authMiddleware, DashboardController.getGameStats);

module.exports = router;