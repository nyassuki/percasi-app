const pool = require('../config/database');
const logger = require('../utils/logger');

class AuditService {
    /**
     * Mencatat aksi admin ke database.
     */
    static async logAdminAction(data) {
        const { 
            adminId, action, targetType, targetId, 
            oldValues, newValues, reason, req 
        } = data;

        const connection = await pool.getConnection();
        try {
            const query = `
                INSERT INTO admin_audit_logs 
                (admin_id, action, target_type, target_id, old_values, new_values, reason, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            `;

            // Ekstrak metadata dari request object (Express)
            const ipAddress = req.ip || req.headers['x-forwarded-for'] || '0.0.0.0';
            const userAgent = req.headers['user-agent'] || 'unknown';

            await connection.execute(query, [
                adminId,
                action,
                targetType,
                targetId,
                oldValues ? JSON.stringify(oldValues) : null,
                newValues ? JSON.stringify(newValues) : null,
                reason || null,
                ipAddress,
                userAgent
            ]);

            logger.info(`[AUDIT_LOGGED] Admin: ${adminId} | Action: ${action} | Target: ${targetType}:${targetId}`);
        } catch (error) {
            // Jangan biarkan error logging menghentikan transaksi utama, tapi catat di logger
            logger.error(`[AUDIT_LOG_FAILED] ${error.message}`);
        } finally {
            connection.release();
        }
    }
}

module.exports = AuditService;