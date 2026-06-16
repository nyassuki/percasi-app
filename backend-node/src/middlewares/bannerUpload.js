/**
 * file: backend-node/src/middlewares/bannerUpload.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Middleware Multer khusus untuk upload gambar Banner/Iklan.
 */

const multer = require('multer');
const path = require('path');
const fs = require('fs');
const logger = require('../utils/logger');

// Folder khusus banner
const uploadDir = 'public/uploads/banners';
if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir, {
        recursive: true
    });
}

const storage = multer.diskStorage({
    destination: function(req, file, cb) {
        cb(null, uploadDir);
    },
    filename: function(req, file, cb) {
        // Format: banner-[timestamp].jpg
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, 'banner-' + uniqueSuffix + path.extname(file.originalname));
    }
});

const fileFilter = (req, file, cb) => {
    // Hanya izinkan gambar
    if (file.mimetype.startsWith('image/')) {
        cb(null, true);
    } else {
        cb(new Error('Hanya file gambar yang diperbolehkan!'), false);
    }
};

const upload = multer({
    storage: storage,
    limits: {
        fileSize: 2 * 1024 * 1024
    }, // Max 2MB (Banner harus ringan)
    fileFilter: fileFilter
});

module.exports = upload;