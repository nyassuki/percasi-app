/**
 * file: maintenanceMiddleware.js
 */
const Redis = require('ioredis');
const redis = new Redis(); // Sesuaikan config redis Anda

const maintenanceCheck = async (req, res, next) => {
    try {
        // 1. Ambil status dari Redis
        const maintenanceData = await redis.get('system:maintenance');

        if (maintenanceData) {
            const config = JSON.parse(maintenanceData);

            if (config.is_maintenance) {
                // 2. Fitur Bypass: Admin tetap bisa akses untuk testing
                const userIp = req.ip || req.connection.remoteAddress;
                if (config.allowed_ips && config.allowed_ips.includes(userIp)) {
                    return next();
                }

                // 3. Jika sedang maintenance, stop request di sini
                return res.status(503).json({
                    success: false,
                    message: config.message || "Sistem sedang dalam pemeliharaan (Maintenance).",
                    retry_after: "3600" // Detik
                });
            }
        }

        // Jika tidak maintenance, lanjut ke logic berikutnya
        next();
    } catch (error) {
        // Jika Redis error, biarkan sistem jalan (Fail-safe)
        next();
    }
};

module.exports = maintenanceCheck;