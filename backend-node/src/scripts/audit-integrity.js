const pool = require('../config/database');
const Security = require('../utils/security');
const Wallet = require('../models/Wallet');
const logger = require('../utils/logger');

async function runGlobalAudit() {
    const startTime = Date.now();
    logger.info("================================================================");
    logger.info("[AUDIT] 🛡️  STARTING GLOBAL DUAL INTEGRITY AUDIT");
    logger.info(`[AUDIT] Timestamp: ${new Date().toISOString()}`);
    logger.info("================================================================");

    const connection = await pool.getConnection();
    
    try {
        // 1. Fetch Wallets
        const [wallets] = await connection.execute("SELECT * FROM wallets");
        const totalUsers = wallets.length;
        logger.info(`[AUDIT] Stage 1: Fetched ${totalUsers} wallets for verification.`);

        let totalTamperedWallets = 0;
        let totalTamperedTX = 0;
        let totalProcessedTX = 0;
        let totalProcessedWallets = 0;

        for (const walletRow of wallets) {
            const userId = walletRow.user_id;
            totalProcessedWallets++;

            // ==========================================
            // A. AUDIT TABEL WALLET (SNAPSHOT INTEGRITY)
            // ==========================================
            const walletPayload = {
                user_id: walletRow.user_id,
                balance: parseFloat(walletRow.balance).toFixed(2),
                locked_balance: parseFloat(walletRow.locked_balance).toFixed(2),
                status: walletRow.status
            };

            const expectedWalletSig = Security.generateSignature(walletPayload);

            if (walletRow.signature !== expectedWalletSig) {
                totalTamperedWallets++;
                logger.error(`[ALARM] ❌ WALLET TAMPERED | User ID: ${userId}`);
                logger.error(`[DETAIL] Expected: ${expectedWalletSig} | Found: ${walletRow.signature}`);
                
                await Wallet.freeze(userId, `AUTO_AUDIT: Wallet balance tampering detected`);
                logger.warn(`[ACTION] Wallet for User ${userId} has been locked/frozen.`);
                continue; 
            }

            // Log progress setiap 100 wallet agar log tidak terlalu penuh
            if (totalProcessedWallets % 100 === 0 || totalProcessedWallets === totalUsers) {
                logger.info(`[AUDIT] Progress: Verified ${totalProcessedWallets}/${totalUsers} wallets...`);
            }

            // ==========================================
            // B. AUDIT TABEL TRANSACTIONS (CHAINING INTEGRITY)
            // ==========================================
            let expectedPrevSignature = 'GENESIS_BLOCK';
            let userTXCount = 0;

            const query = connection.connection.query(
                "SELECT * FROM transactions WHERE user_id = ? ORDER BY id ASC",
                [userId]
            );

            await new Promise((resolve) => {
                query
                    .on('error', (err) => {
                        logger.error(`[STREAM_ERROR] Critical failure for User ${userId}: ${err.message}`);
                        resolve();
                    })
                    .on('result', async (row) => {
                        connection.connection.pause(); 
                        
                        totalProcessedTX++;
                        userTXCount++;
                        
                        const txPayload = {
                            user_id: row.user_id,
                            type: row.type,
                            amount: parseFloat(row.amount).toString(),
                            flow: row.flow,
                            prev_signature: row.prev_signature,
                            idempotency_key: row.idempotency_key
                        };

                        const recalculatedTxSig = Security.generateSignature(txPayload);
                        const isSignatureValid = (recalculatedTxSig === row.signature);
                        const isChainValid = (row.prev_signature === expectedPrevSignature);

                        if (!isSignatureValid || !isChainValid) {
                            totalTamperedTX++;
                            const reason = !isSignatureValid ? 'INVALID_SIG' : 'BROKEN_CHAIN';
                            
                            logger.error(`[ALARM] ❌ TX TAMPERED | User: ${userId} | TX ID: ${row.id} | Reason: ${reason}`);
                            logger.error(`[DETAIL] TX Code: ${row.kode_transaksi} | Expected Prev: ${expectedPrevSignature.substring(0,10)}...`);
                            
                            await Wallet.freeze(userId, `AUTO_AUDIT: Transaction chain broken at TX ${row.kode_transaksi}`);
                            
                            query.destroy(); 
                            connection.connection.resume();
                            return resolve(); 
                        }

                        expectedPrevSignature = row.signature;
                        connection.connection.resume();
                    })
                    .on('end', () => {
                        // Opsi: log jika user memiliki transaksi sangat banyak
                        if(userTXCount > 1000) {
                            logger.info(`[AUDIT] High-volume user ${userId}: ${userTXCount} transactions verified.`);
                        }
                        resolve();
                    });
            });
        }

        const duration = ((Date.now() - startTime) / 1000).toFixed(2);

        // ==========================================
        // FINAL SUMMARY LOGGING
        // ==========================================
        logger.info("================================================================");
        logger.info("🏁 AUDIT COMPLETED");
        logger.info(`[SUMMARY] Time Elapsed         : ${duration} seconds`);
        logger.info(`[SUMMARY] Wallets Processed    : ${totalProcessedWallets}`);
        logger.info(`[SUMMARY] Transactions Checked : ${totalProcessedTX}`);
        logger.info("----------------------------------------------------------------");
        
        if (totalTamperedWallets === 0 && totalTamperedTX === 0) {
            logger.info("[STATUS] ✅ SYSTEM SECURE: No integrity violations found.");
        } else {
            logger.warn("[STATUS] ❌ SYSTEM COMPROMISED");
            logger.warn(`[STATS] Wallet Violations      : ${totalTamperedWallets}`);
            logger.warn(`[STATS] Transaction Violations : ${totalTamperedTX}`);
            logger.warn("[ACTION] Manual investigation and reconciliation required.");
        }
        logger.info("================================================================");

    } catch (error) {
        logger.error("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
        logger.error(`[FATAL_ERROR] Audit crashed: ${error.stack}`);
        logger.error("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
    } finally {
        connection.release();
        if (require.main === module) {
            logger.info("[AUDIT] Closing process...");
            process.exit();
        }
    }
}

module.exports = { runGlobalAudit };