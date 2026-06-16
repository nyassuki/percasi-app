/**
 * file: backend-node/src/utils/userSocketMap.js
 * description: Mapping in-memory antara User ID (Database) dan Socket ID (Koneksi aktif).
 */

const userSocketMap = new Map();

module.exports = userSocketMap;