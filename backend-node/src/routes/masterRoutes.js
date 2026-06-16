/**
 * file: backend-node/src/routes/masterRoutes.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Routing untuk Master Data (Bank & Wilayah).
 */

const express = require('express');
const MasterController = require('../controllers/masterController');

const router = express.Router();

// Route Bank
router.get('/banks', MasterController.getBanks);
router.get('/settings', MasterController.getSettings);
router.get('/bandara', MasterController.getBandara);
router.get('/stasiun', MasterController.getStasiun);

router.get('/clubs', MasterController.getClubs);
router.post('/update-user-club', MasterController.updateProfileClub);

// Route Wilayah (Cascading Dropdown)

router.get('/countries', MasterController.getCountries);
router.get('/provinces/:countryId', MasterController.getProvinces);
router.get('/regencies/:provinceId', MasterController.getRegencies);
router.get('/districts/:regencyId', MasterController.getDistricts);
router.get('/subdistricts/:districtId', MasterController.getSubdistricts);

module.exports = router;
