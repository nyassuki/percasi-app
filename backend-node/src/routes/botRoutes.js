/**
 * file: backend-node/src/routes/botRoutes.js
 */
const express = require('express');
const BotController = require('../controllers/botController');

const router = express.Router();

router.post('/move', BotController.handleBotMove);

module.exports = router;
