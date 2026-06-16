<?php

namespace App\Models;

use CodeIgniter\Model;

class BonusMasterModel extends Model
{
    protected $table = 'bonus_master';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'code', 'name', 'description', 'point_value',
        'max_daily_point', 'is_active',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'code' => 'required|max_length[50]|is_unique[bonus_master.code]',
        'name' => 'required|max_length[100]',
        'point_value' => 'required|integer|greater_than[0]',
        'max_daily_point' => 'permit_empty|integer|greater_than[0]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk bonus aktif
    public function getActiveBonuses()
    {
        return $this->where('is_active', 1)
                    ->orderBy('point_value', 'DESC')
                    ->findAll();
    }
    
    // Method untuk mencari bonus berdasarkan code
    public function findByCode($code)
    {
        return $this->where('code', $code)->first();
    }
    
    // Method untuk mendapatkan point value bonus
    public function getPointValue($bonusId)
    {
        $bonus = $this->find($bonusId);
        return $bonus ? $bonus->point_value : 0;
    }
}