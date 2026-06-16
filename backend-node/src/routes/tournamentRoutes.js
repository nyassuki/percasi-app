/**
 * file: backend-node/src/routes/tournamentRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Definisi routing untuk modul Turnamen.
 */

const express = require('express');
const TournamentController = require('../controllers/tournamentController');
const authMiddleware = require('../middlewares/authMiddleware');


const router = express.Router();

router.get('/open', authMiddleware, TournamentController.getOpenTournaments);
router.get('/participant', authMiddleware, TournamentController.getOpenTournaments);
// Trigger Pairing (Biasanya hanya admin yang boleh hit ini)
router.post('/:id/pairings', TournamentController.createPairing);
router.post('/:id/join', authMiddleware, TournamentController.join);
router.get('/:id/lobby', authMiddleware, TournamentController.getLobbyData);
module.exports = router;