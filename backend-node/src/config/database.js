/**
 * File: backend-node/src/config/database.js
 * Author: yassuki
 * Creation Date: 2025-12-11
 * Last Modified: 2025-12-11
 * Version: 1.0.0
 * 
 * Description: 
 * This module configures and exports a MySQL connection pool using mysql2/promise.
 * It establishes a reusable pool of database connections for efficient query execution,
 * handles environment-based configuration, and includes a connection test function.
 * The pool manages multiple connections to improve performance and handle concurrent requests.
 * 
 * Dependencies:
 * - mysql2/promise: Promise-based MySQL client
 * - dotenv: Environment variable management
 * - ../utils/logger: Custom logging utility
 * 
 * Environment Variables Required:
 * - DB_HOST: Database server hostname or IP address
 * - DB_USER: Database authentication username
 * - DB_PASSWORD: Database authentication password
 * - DB_NAME: Default database name
 * - DB_PORT: Database server port (optional, defaults to MySQL default 3306)
 * 
 * Usage:
 * const pool = require('./config/database');
 * 
 * // Example query execution:
 * try {
 *     const [rows] = await pool.query('SELECT * FROM users WHERE id = ?', [userId]);
 *     return rows;
 * } catch (error) {
 *     logger.error('Query failed:', error);
 *     throw error;
 * }
 * 
 * Exports:
 * - pool: MySQL connection pool instance configured with environment variables
 * 
 * Connection Pool Configuration Details:
 * - waitForConnections: true (queues connection requests when limit is reached)
 * - connectionLimit: 10 (maximum number of connections in pool)
 * - queueLimit: 0 (unlimited queue size for connection requests)
 * 
 * Best Practices:
 * 1. Always release connections back to the pool after use
 * 2. Use parameterized queries to prevent SQL injection
 * 3. Handle connection errors gracefully in application code
 * 4. Monitor connection usage in production environments
 * 
 * Error Handling:
 * - Connection errors are logged but don't crash the application
 * - Failed connections can be retried through pool's built-in mechanisms
 * - Connection timeouts are handled by mysql2 driver
 * 
 * Security Notes:
 * - Database credentials are loaded from environment variables (not hard-coded)
 * - SSL/TLS configuration can be added via additional pool options if required
 * - Connection strings are never exposed in logs
 */

const mysql = require('mysql2/promise');
require('dotenv').config();
const logger = require('../utils/logger');

/**
 * MySQL Connection Pool Configuration
 * 
 * Creates a pool of database connections that can be reused across multiple queries.
 * The pool manages connection lifecycle, reduces connection establishment overhead,
 * and provides better performance for applications with frequent database operations.
 * 
 * Configuration Parameters:
 * @property {string} host - Database server host (from DB_HOST environment variable)
 * @property {string} user - Database username (from DB_USER environment variable)
 * @property {string} password - Database password (from DB_PASSWORD environment variable)
 * @property {string} database - Default database name (from DB_NAME environment variable)
 * @property {number|string} port - Database server port (from DB_PORT environment variable, optional)
 * @property {boolean} waitForConnections - If true, pool queues connection requests when limit reached
 * @property {number} connectionLimit - Maximum number of connections in pool (default: 10)
 * @property {number} queueLimit - Maximum number of connection requests to queue (0 = unlimited)
 * 
 * Additional Available Options (not currently used):
 * - connectTimeout: Connection timeout in milliseconds
 * - ssl: SSL configuration object
 * - charset: Connection charset
 * - timezone: Server timezone
 * - decimalNumbers: Return numbers as decimals instead of strings
 * 
 * @type {mysql.Pool}
 */
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

/**
 * Tests database connection during application initialization.
 * 
 * This function attempts to acquire a connection from the pool to verify:
 * 1. Database server is reachable
 * 2. Credentials are valid
 * 3. Database exists and is accessible
 * 4. Network connectivity is established
 * 
 * The function logs the connection status (success/failure) but doesn't throw
 * errors, allowing the application to continue running even if database is
 * temporarily unavailable (useful for retry scenarios).
 * 
 * @async
 * @function testConnection
 * @returns {Promise<void>} Resolves when connection test completes (doesn't return value)
 * 
 * @example
 * // Called automatically during module initialization
 * testConnection();
 * 
 * @throws Does not throw errors externally (errors are caught and logged internally)
 * 
 * Logging:
 * - Success: "Database connected successfully!"
 * - Failure: "Database connection failed:" followed by error message
 */
async function testConnection() {
    try {
        // Attempt to acquire a connection from the pool
        const connection = await pool.getConnection();
        
        // Log successful connection
        logger.info('Database connected successfully!');
        
        // Release the connection back to the pool
        // IMPORTANT: Always release connections after use to prevent pool exhaustion
        connection.release();
    } catch (error) {
        // Log connection failure with error details
        // Note: Original error object is not logged to avoid exposing sensitive data
        logger.error('Database connection failed:', error.message);
        
        // Additional debugging information (commented out by default)
        // logger.debug('Full connection error:', error);
        
        // Connection errors are not re-thrown to allow application startup
        // Application code should handle database errors at query time
    }
}

/**
 * Execute connection test during module initialization
 * 
 * This immediate invocation ensures database connectivity is verified
 * when the module is first loaded/required by the application.
 * 
 * Note: The test runs asynchronously and doesn't block module export.
 */
testConnection();

/**
 * Export the configured MySQL connection pool
 * 
 * The pool instance provides methods for executing queries:
 * - pool.query(sql, values): Execute a query with optional parameters
 * - pool.getConnection(): Get a connection for transaction or multiple queries
 * - pool.execute(sql, values): Similar to query but prepares statement
 * - pool.end(): Close all pool connections (call during application shutdown)
 * 
 * @exports pool
 */
module.exports = pool;