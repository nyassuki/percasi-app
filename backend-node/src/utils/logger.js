/**
 * file: src/utils/logger.js
 * updated by : yassuki & AI Assistant
 * description: Sistem Logging terintegrasi Winston & Morgan dengan Log Rotation.
 */

const winston = require('winston');
require('winston-daily-rotate-file');
const path = require('path');
require('dotenv').config();

// Cek apakah lingkungan adalah PROD
const isProd = process.env.BENV === 'PROD';

// Format khusus untuk log file (JSON agar mudah dibaca sistem/dashboard)
const fileFormat = winston.format.combine(
    winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
    winston.format.json()
);

// Format khusus untuk Console (Berwarna dan mudah dibaca manusia)
const consoleFormat = winston.format.combine(
    winston.format.colorize(),
    winston.format.printf(({ level, message, timestamp, ...metadata }) => {
        let msg = `[${level}] [${timestamp}]: ${message}`;
        if (Object.keys(metadata).length > 0 && metadata.stack) {
            msg += `\n${metadata.stack}`; // Tampilkan stack trace jika ada error
        }
        return msg;
    })
);

// Konfigurasi Winston
const winstonLogger = winston.createLogger({
    level: isProd ? 'info' : 'debug',
    format: fileFormat,
    transports: [
        // 1. Simpan ERROR ke file terpisah (Rotate setiap hari)
        new winston.transports.DailyRotateFile({
            filename: 'logs/error-%DATE%.log',
            datePattern: 'YYYY-MM-DD',
            level: 'error',
            maxFiles: '14d', // Simpan selama 14 hari
        }),
        // 2. Simpan SEMUA log ke file gabungan
        new winston.transports.DailyRotateFile({
            filename: 'logs/combined-%DATE%.log',
            datePattern: 'YYYY-MM-DD',
            maxFiles: '14d',
        }),
    ],
});

// 3. Jika bukan PROD (atau ingin tetap tampil di monitoring PROD), tambahkan Console transport
if (!isProd || process.env.CONSOLE_LOG === 'true') {
    winstonLogger.add(new winston.transports.Console({
        format: consoleFormat
    }));
}

// Wrapper untuk menjaga kompatibilitas dengan kode lamamu
const logger = {
    info: (...args) => winstonLogger.info(args.join(' ')),
    warn: (...args) => winstonLogger.warn(args.join(' ')),
    error: (msg, err = null) => {
        // Jika argumen kedua adalah object Error, Winston akan menangkap stack trace-nya
        if (err instanceof Error) {
            winstonLogger.error(msg, { stack: err.stack });
        } else {
            winstonLogger.error(typeof msg === 'object' ? JSON.stringify(msg) : msg);
        }
    },
    debug: (...args) => winstonLogger.debug(args.join(' ')),
    
    // Stream untuk integrasi Morgan
    stream: {
        write: (message) => winstonLogger.info(message.trim()),
    }
};

module.exports = logger;