<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminActivityLogModel extends Model
{
    protected $table = 'admin_activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'admin_id', 'action', 'target_table', 'target_id',
        'details', 'ip_address', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'admin_id' => 'required|integer|is_not_unique[admins.id]',
        'action' => 'required|max_length[100]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk log aktivitas admin
    public function logActivity($adminId, $action, $targetTable = null, $targetId = null, $details = null, $ipAddress = null)
    {
        $data = [
            'admin_id' => $adminId,
            'action' => $action,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'details' => json_encode($details),
            'ip_address' => $ipAddress ?? service('request')->getIPAddress()
        ];
        
        return $this->insert($data);
    }
    
    // Method untuk mendapatkan log aktivitas admin
    public function getAdminLogs($adminId = null, $limit = 100)
    {
        $builder = $this->orderBy('created_at', 'DESC')->limit($limit);
        
        if ($adminId) {
            $builder->where('admin_id', $adminId);
        }
        
        return $builder->findAll();
    }
    
    // Method untuk mencari log berdasarkan action
    public function searchLogs($action, $startDate = null, $endDate = null)
    {
        $builder = $this->where('action', $action);
        
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }
}