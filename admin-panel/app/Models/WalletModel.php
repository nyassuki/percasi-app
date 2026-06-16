<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletModel extends Model
{
    protected $table = 'wallets';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = false;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'balance', 'locked_balance', 'currency',
        'pin_hash', 'is_frozen', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'balance' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'locked_balance' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'currency' => 'permit_empty|string|max_length[3]',
        'is_frozen' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk mendapatkan balance user
    public function getUserBalance($userId)
    {
        return $this->find($userId);
    }
    
    // Method untuk update balance (tambah/kurang)
    public function updateBalance($userId, $amount, $type = 'add')
    {
        $wallet = $this->find($userId);
        
        if (!$wallet) {
            // Create wallet jika tidak ada
            return $this->insert([
                'user_id' => $userId,
                'balance' => ($type === 'add') ? $amount : 0,
                'currency' => 'IDR'
            ]);
        }
        
        $newBalance = ($type === 'add') 
            ? $wallet->balance + $amount 
            : $wallet->balance - $amount;
            
        if ($newBalance < 0) {
            throw new \Exception('Insufficient balance');
        }
        
        return $this->update($userId, ['balance' => $newBalance]);
    }
    
    // Method untuk lock/unlock balance
    public function updateLockedBalance($userId, $amount, $type = 'lock')
    {
        $wallet = $this->find($userId);
        
        if (!$wallet) {
            return false;
        }
        
        if ($type === 'lock') {
            if ($wallet->balance < $amount) {
                throw new \Exception('Insufficient balance to lock');
            }
            
            $newBalance = $wallet->balance - $amount;
            $newLocked = $wallet->locked_balance + $amount;
        } else {
            if ($wallet->locked_balance < $amount) {
                throw new \Exception('Insufficient locked balance');
            }
            
            $newBalance = $wallet->balance + $amount;
            $newLocked = $wallet->locked_balance - $amount;
        }
        
        return $this->update($userId, [
            'balance' => $newBalance,
            'locked_balance' => $newLocked
        ]);
    }
}