<?php

namespace App\Models;

use CodeIgniter\Model;

class UserVirtualAccountModel extends Model
{
    protected $table = 'user_virtual_accounts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'bank_code', 'va_number', 'status',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'bank_code' => 'required|max_length[10]',
        'va_number' => 'required|max_length[50]',
        'status' => 'permit_empty|in_list[active,inactive]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk VA user aktif
    public function getUserActiveVA($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'active')
                    ->first();
    }
    
    // Method untuk semua VA user
    public function getUserVAs($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    // Method untuk update status VA
    public function updateVAStatus($vaId, $status)
    {
        return $this->update($vaId, ['status' => $status]);
    }
}