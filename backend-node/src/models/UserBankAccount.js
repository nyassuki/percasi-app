const db = require('../config/database');
const logger = require('../utils/logger');

class UserBankAccount {

    // Helper: Cari data by User ID
    static async findByUserId(userId) {
        const query = `SELECT user_bank_accounts.id,user_bank_accounts.user_id,user_bank_accounts.bank_code,master_banks.bank_name,user_bank_accounts.account_number,user_bank_accounts.account_holder_name FROM user_bank_accounts INNER JOIN master_banks ON user_bank_accounts.bank_code=master_banks.bank_code WHERE user_id = ? LIMIT 1`;
        try {
            const [rows] = await db.execute(query, [userId]);
            return rows.length > 0 ? rows[0] : null;
        } catch (error) {
            throw error;
        }
    }

    // FUNGSI UTAMA: Create or Update (Upsert)
    static async upsert(userId, data) {
        const { bank_code, account_number, account_holder_name } = data;

        try {
            // 1. Cek apakah user sudah punya data
            const existingAccount = await this.findByUserId(userId);

            if (existingAccount) {
                // --- KONDISI A: DATA ADA -> LAKUKAN UPDATE ---
                const updateQuery = `
                    UPDATE user_bank_accounts 
                    SET 
                        bank_code = ?, 
                        account_number = ?, 
                        account_holder_name = ?,
                        updated_at = NOW()
                    WHERE user_id = ?
                `;
                
                await db.execute(updateQuery, [
                    bank_code, 
                    account_number, 
                    account_holder_name, 
                    userId
                ]);

                return { 
                    action: 'updated', 
                    data: { ...existingAccount, ...data } 
                };

            } else {
                // --- KONDISI B: DATA TIDAK ADA -> LAKUKAN INSERT ---
                const insertQuery = `
                    INSERT INTO user_bank_accounts 
                    (user_id, bank_code, account_number, account_holder_name, is_verified, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 0, NOW(), NOW())
                `;

                const [result] = await db.execute(insertQuery, [
                    userId, 
                    bank_code, 
                    account_number, 
                    account_holder_name
                ]);

                return { 
                    action: 'created', 
                    data: { id: result.insertId, user_id: userId, ...data } 
                };
            }

        } catch (error) {
            throw error;
        }
    }
}

module.exports = UserBankAccount;