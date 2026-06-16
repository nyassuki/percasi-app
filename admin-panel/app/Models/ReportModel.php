<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'reporter_id', 'reported_user_id', 'match_id', 'reason',
        'description', 'status', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'reporter_id' => 'required|integer|is_not_unique[users.id]',
        'reported_user_id' => 'required|integer|is_not_unique[users.id]',
        'reason' => 'permit_empty|in_list[cheating_engine,verbal_abuse,other]',
        'status' => 'permit_empty|in_list[open,resolved,dismissed]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk laporan yang masih open
    public function getOpenReports()
    {
        return $this->where('status', 'open')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    // Method untuk laporan berdasarkan user yang dilaporkan
    public function getReportsByReportedUser($userId)
    {
        return $this->where('reported_user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    // Method untuk update status laporan
    public function updateReportStatus($reportId, $status)
    {
        return $this->update($reportId, ['status' => $status]);
    }
    
    // Method untuk membuat laporan baru
    public function createReport($reporterId, $reportedUserId, $reason, $description = null, $matchId = null)
    {
        $data = [
            'reporter_id' => $reporterId,
            'reported_user_id' => $reportedUserId,
            'reason' => $reason,
            'description' => $description,
            'match_id' => $matchId,
            'status' => 'open'
        ];
        
        return $this->insert($data);
    }
}