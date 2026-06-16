<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterBankModel extends Model
{
    protected $table = 'master_banks';
    protected $primaryKey = 'bank_code';
    protected $useAutoIncrement = false;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'bank_code', 'bank_name', 'is_active',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'bank_code' => 'required|max_length[10]',
        'bank_name' => 'required|max_length[100]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk bank aktif
    public function getActiveBanks()
    {
        return $this->where('is_active', 1)
                    ->orderBy('bank_name', 'ASC')
                    ->findAll();
    }
    
    // Method untuk mencari bank berdasarkan nama atau kode
    public function searchBank($keyword)
    {
        return $this->groupStart()
                    ->like('bank_code', $keyword)
                    ->orLike('bank_name', $keyword)
                    ->groupEnd()
                    ->where('is_active', 1)
                    ->orderBy('bank_name', 'ASC')
                    ->findAll();
    }
}