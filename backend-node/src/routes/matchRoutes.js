/**
 * file: backend-node/src/routes/matchRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Definisi rute API untuk modul Match.
 */

const express = require('express');
const MatchController = require('../controllers/matchController');
const authMiddleware = require('../middlewares/authMiddleware');

const router = express.Router();

// Route: POST /api/matches/history
router.get('/live', MatchController.listLiveMatches);
router.get('/history', authMiddleware, MatchController.getMatchHistory);
router.post('/move', authMiddleware, MatchController.makeMove);
router.post('/casual/join', authMiddleware, MatchController.joinCasualQueue);
router.post('/casual/leave', authMiddleware, MatchController.leaveCasualQueue);
router.get('/active', authMiddleware, MatchController.getActiveMatch);
router.get('/rating-history/:type', authMiddleware, MatchController.getEloHistory);
router.get('/:id', authMiddleware, MatchController.getMatchDetail);

module.exports = router;