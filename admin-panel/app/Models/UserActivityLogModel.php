<?php

namespace App\Models;

use CodeIgniter\Model;

class UserActivityLogModel extends Model
{
    protected $table = 'user_activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'module', 'action', 'description',
        'metadata', 'ip_address', 'user_agent',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'module' => 'required|max_length[50]',
        'action' => 'required|max_length[100]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk log aktivitas user
    public function logActivity($userId, $module, $action, $description = null, $metadata = null)
    {
        $request = service('request');
        
        $data = [
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'metadata' => json_encode($metadata),
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()
        ];
        
        return $this->insert($data);
    }
    
    // Method untuk mendapatkan log aktivitas user
    public function getUserLogs($userId, $limit = 100)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    // Method untuk mencari log berdasarkan module dan action
    public function searchLogs($module = null, $action = null, $startDate = null, $endDate = null)
    {
        $builder = $this;
        
        if ($module) {
            $builder->where('module', $module);
        }
        
        if ($action) {
            $builder->where('action', $action);
        }
        
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }
}