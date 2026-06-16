<?php

namespace App\Models;

use CodeIgniter\Model;

class UserBankAccountModel extends Model
{
    protected $table = 'user_bank_accounts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'bank_code', 'account_number',
        'account_holder_name', 'is_verified',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'bank_code' => 'required|max_length[10]',
        'account_number' => 'required|max_length[50]',
        'account_holder_name' => 'required|max_length[100]',
        'is_verified' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk bank account user yang verified
    public function getUserVerifiedAccounts($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_verified', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    // Method untuk semua bank account user
    public function getUserBankAccounts($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    // Method untuk verifikasi bank account
    public function verifyAccount($accountId, $verified = true)
    {
        return $this->update($accountId, ['is_verified' => $verified ? 1 : 0]);
    }
}