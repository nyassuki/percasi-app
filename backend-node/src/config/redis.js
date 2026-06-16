/**
 * File: backend-node/src/config/redis.js
 * Author: yassuki
 * Creation Date: 25-12-2025
 * Last Modified: 25-12-2025
 * Version: 1.0.0
 * 
 * Description:
 * This module configures and manages Redis connections for the application.
 * It establishes two distinct Redis connections with different purposes:
 * 1. MAIN connection: For general application use (authentication, lobby management, socket adapter)
 * 2. QUEUE connection: For BullMQ job processing (if implemented separately)
 * 
 * The module implements robust error handling, automatic reconnection strategies,
 * and provides a configuration object for external use (e.g., Socket.IO adapter).
 * 
 * Architecture Overview:
 * - Main Connection: Persistent connection for general Redis operations
 * - Configuration Export: Redis settings for other modules (Socket.IO, etc.)
 * - Event-Driven Monitoring: Connection status tracking and error handling
 * 
 * Dependencies:
 * - ioredis: Redis client for Node.js with full Promise support
 * - dotenv: Environment variable management
 * - ../utils/logger: Custom logging utility
 * 
 * Environment Variables Required:
 * - REDIS_HOST: Redis server hostname or IP address (default: 127.0.0.1)
 * - REDIS_PORT: Redis server port (default: 6379)
 * - REDIS_PASSWORD: Redis authentication password (optional)
 * 
 * Usage:
 * // Import the main Redis connection
 * const redis = require('./config/redis');
 * 
 * // Use for general operations
 * await redis.set('key', 'value');
 * const value = await redis.get('key');
 * 
 * // Access configuration for other modules
 * const redisConfig = redis.redisConfig;
 * 
 * Exports:
 * - Default: Main Redis connection instance
 * - redisConfig: Configuration object for external use
 * 
 * Connection Strategies:
 * 1. Main Connection: Optimized for reliability with aggressive retry strategy
 * 2. Queue Connection: Can be added later for BullMQ with different settings
 * 
 * Error Handling:
 * - Automatic retry with exponential backoff
 * - Non-blocking error logging
 * - Connection monitoring without crashing application
 * 
 * Performance Considerations:
 * - Max retries: 20 attempts before failing a request
 * - Retry delay: Exponential backoff up to 2 seconds max
 * - Connection pooling: Managed by ioredis internally
 * 
 * Security Notes:
 * - Password loaded from environment variables
 * - No sensitive data in logs
 * - Optional SSL/TLS support available via ioredis options
 */

require('dotenv').config();
const IORedis = require('ioredis');
const logger = require('../utils/logger');

/**
 * Base Redis Configuration
 * 
 * Shared configuration used by all Redis connections.
 * These settings are loaded from environment variables with fallback defaults.
 * 
 * @constant {Object} baseConfig
 * @property {string} host - Redis server host (from REDIS_HOST or default 127.0.0.1)
 * @property {number|string} port - Redis server port (from REDIS_PORT or default 6379)
 * @property {string|undefined} password - Redis authentication password (from REDIS_PASSWORD, optional)
 * 
 * @example
 * // Configuration sources:
 * // 1. Environment variables (production)
 * // 2. Default values (development)
 */
const baseConfig = {
  host: process.env.REDIS_HOST || '127.0.0.1',
  port: process.env.REDIS_PORT || 6379,
  password: process.env.REDIS_PASSWORD || undefined,
};

/**
 * MAIN Redis Connection (General Purpose)
 * 
 * Primary Redis connection used throughout the application for:
 * - User authentication sessions
 * - Lobby/game room management
 * - Socket.IO adapter storage
 * - Real-time data caching
 * - Pub/Sub messaging
 * 
 * This connection uses an aggressive retry strategy to maintain availability
 * even during temporary Redis server disruptions.
 * 
 * @constant {IORedis} connection
 * @property {Object} config - Connection configuration
 * @property {number} maxRetriesPerRequest - Maximum retry attempts per request
 * @property {Function} retryStrategy - Custom retry timing strategy
 * 
 * Configuration Details:
 * - maxRetriesPerRequest: 20 (retry up to 20 times before failing)
 * - retryStrategy: Exponential backoff starting at 50ms, max 2000ms delay
 * 
 * Retry Strategy Formula:
 * Delay = Math.min(retryAttempt × 50ms, 2000ms)
 * Example: 1st retry: 50ms, 2nd: 100ms, ... 40th+: 2000ms
 */
const connection = new IORedis({
  ...baseConfig,
  
  /**
   * Maximum number of retry attempts for failed requests
   * @type {number}
   */
  maxRetriesPerRequest: 20,
  
  /**
   * Custom retry strategy with exponential backoff
   * 
   * @param {number} times - Current retry attempt count (starts at 1)
   * @returns {number} Milliseconds to wait before next retry
   * 
   * Retry Pattern:
   * Attempt 1: 50ms delay
   * Attempt 2: 100ms delay
   * Attempt 3: 150ms delay
   * ...
   * Attempt 40+: 2000ms delay (maximum)
   */
  retryStrategy(times) {
    return Math.min(times * 50, 2000);
  },
});

/**
 * Redis Connection Event Listeners
 * 
 * Monitors connection state and handles events:
 * - 'connect': Successful connection establishment
 * - 'error': Connection errors or operational failures
 * - 'close': Connection closed (handled by ioredis internally)
 * - 'reconnecting': Automatic reconnection in progress
 * 
 * Note: ioredis automatically handles reconnection based on retryStrategy.
 */

/**
 * Connection Established Event
 * 
 * Triggered when Redis connection is successfully established.
 * 
 * @event connection:connect
 * @listens connection#connect
 */
connection.on('connect', () => {
  logger.info('[Redis Main] Connected successfully');
  
  // Optional: Log additional connection details
  // logger.debug(`[Redis Main] Connected to ${baseConfig.host}:${baseConfig.port}`);
});

/**
 * Connection Error Event
 * 
 * Triggered on Redis connection errors or operational failures.
 * Errors are logged but don't crash the application.
 * 
 * @event connection:error
 * @listens connection#error
 * @param {Error} err - Error object containing failure details
 */
connection.on('error', (err) => {
  logger.error('[Redis Main] Connection error:', err.message);
  
  // Optional: Additional debugging for specific error types
  // if (err.code === 'ECONNREFUSED') {
  //   logger.error('[Redis Main] Cannot connect to Redis server');
  // }
});

/**
 * Optional: Additional Event Listeners (commented out by default)
 * 
 * Uncomment these for more detailed connection monitoring:
 * 
 * connection.on('ready', () => {
 *   logger.debug('[Redis Main] Ready to accept commands');
 * });
 * 
 * connection.on('close', () => {
 *   logger.warn('[Redis Main] Connection closed');
 * });
 * 
 * connection.on('reconnecting', (delay) => {
 *   logger.info(`[Redis Main] Reconnecting in ${delay}ms`);
 * });
 */

/**
 * Redis Configuration Export
 * 
 * Exports the Redis configuration for external modules that need to create
 * their own Redis connections (e.g., Socket.IO adapter, BullMQ workers).
 * 
 * This configuration object is attached to the main connection instance
 * for easy access by other modules.
 * 
 * @property {Object} redisConfig - Redis configuration for external use
 * @property {string} redisConfig.host - Redis server host
 * @property {number} redisConfig.port - Redis server port
 * @property {string|undefined} redisConfig.password - Redis password
 * @property {number|null} redisConfig.maxRetriesPerRequest - Retry limit (null for some adapters)
 * @property {Function} redisConfig.retryStrategy - Retry timing function
 * 
 * Usage Example (in io.js):
 * const redisConfig = redis.redisConfig;
 * const adapter = new RedisAdapter(redisConfig);
 */
connection.redisConfig = {
  ...baseConfig,
  
  /**
   * Retry configuration for duplicate connections
   * Some adapters may require null or different retry settings
   * @type {number|null}
   */
  maxRetriesPerRequest: null,
  
  /**
   * Retry strategy function
   * Same as main connection but can be overridden by adapter
   * @type {Function}
   */
  retryStrategy: (times) => Math.min(times * 50, 2000)
};

/**
 * Export the Redis Connection Instance
 * 
 * The main Redis connection is exported as the default export.
 * Modules importing this file will receive the connected Redis instance.
 * 
 * Available Methods:
 * - Standard Redis commands: get, set, hget, hset, etc.
 * - Pipeline support: .pipeline().set().get().exec()
 * - Pub/Sub: subscribe, publish, psubscribe
 * - Transaction: multi, exec
 * 
 * @exports connection
 * 
 * Cleanup Recommendation:
 * // During application shutdown
 * await redis.quit();
 */
module.exports = connection;

/**
 * Optional: Secondary Queue Connection
 * 
 * For future implementation of BullMQ job queue.
 * Uncomment and configure as needed.
 * 
 * const queueConnection = new IORedis({
 *   ...baseConfig,
 *   maxRetriesPerRequest: 10,
 *   db: 1, // Optional: Use separate database index
 *   enableOfflineQueue: false, // Optional: Disable offline queue for workers
 * });
 * 
 * module.exports.queueConnection = queueConnection;
 */