<?php

namespace App\Models;

use CodeIgniter\Model;

class ViewMatchHistoryModel extends Model
{
    protected $table = 'view_match_history';
    protected $primaryKey = 'match_id';
    protected $useAutoIncrement = false;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    // View tidak bisa di-insert/update/delete
    protected $allowedFields = [];
    protected $useTimestamps = false;
    
    // Method untuk match history user
    public function getUserMatchHistory($userId, $limit = 20)
    {
        return $this->groupStart()
                    ->where('white_player_id', $userId)
                    ->orWhere('black_player_id', $userId)
                    ->groupEnd()
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    // Method untuk detail match
    public function getMatchDetails($matchId)
    {
        return $this->find($matchId);
    }
    
    // Method untuk match terbaru
    public function getRecentMatches($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}