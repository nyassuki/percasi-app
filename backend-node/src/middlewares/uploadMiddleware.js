/**
 * file: backend-node/src/middlewares/uploadMiddleware.js
 * created by : yassuki
 * created date: 2025-12-11
 * description: Middleware Multer untuk menangani upload file gambar.
 */

const multer = require('multer');
const path = require('path');
const fs = require('fs');
const logger = require('../utils/logger');

// Pastikan folder uploads ada
const uploadDir = 'public/uploads/kyc';
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
        // Format: kyc-[userid]-[timestamp].[ext]
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, file.fieldname + '-' + req.user.id + '-' + uniqueSuffix + path.extname(file.originalname));
    }
});

const fileFilter = (req, file, cb) => {
    if (file.mimetype === 'image/jpeg' || file.mimetype === 'image/png' || file.mimetype === 'image/webp') {
        cb(null, true);
    } else {
        cb(new Error('Format file tidak didukung! Hanya JPG/PNG.'), false);
    }
};

const upload = multer({
    storage: storage,
    limits: {
        fileSize: 5 * 1024 * 1024
    }, // Max 5MB
    fileFilter: fileFilter
});

module.exports = upload;