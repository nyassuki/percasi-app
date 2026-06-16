<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchModel extends Model
{
    protected $table = 'view_match_history';
    protected $primaryKey = 'match_id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'tournament_id', 'white_player_id', 'black_player_id', 'result',
        'win_reason', 'pgn_string', 'start_time', 'end_time', 'is_analyzed',
        'cheat_probability', 'round_number', 'player_timer', 'white_time_ms',
        'black_time_ms', 'last_move_time', 'fen', 'status',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'white_player_id' => 'required|integer|is_not_unique[users.id]',
        'black_player_id' => 'required|integer|is_not_unique[users.id]',
        'result' => 'permit_empty|in_list[1-0,0-1,1/2-1/2,ongoing,aborted]',
        'win_reason' => 'permit_empty|in_list[checkmate,timeout,resignation,cheat_detected,agreement]',
        'status' => 'permit_empty|in_list[pending_start,ongoing,completed,aborted]',
        'cheat_probability' => 'permit_empty|decimal',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk match yang sedang berlangsung dengan paginasi
    public function getOngoingMatches($page = 1, $perPage = 10, $search = null)
    {
        $query = $this->where('status', 'ongoing');
        
        if (!empty($search)) {
            $query->groupStart()
                  ->orLike('white_player_id', $search)
                  ->orLike('black_player_id', $search)
                  ->orLike('tournament_id', $search)
                  ->groupEnd();
        }
        
        return $query->orderBy('created_at', 'DESC')
                     ->paginate($perPage, 'default', $page);
    }
    
    // Method untuk match berdasarkan tournament dengan paginasi
    public function getTournamentMatches($tournamentId, $page = 1, $perPage = 10, $filters = [])
    {
        $query = $this->where('tournament_id', $tournamentId);
        
        // Filter berdasarkan status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        // Filter berdasarkan result
        if (!empty($filters['result'])) {
            $query->where('result', $filters['result']);
        }
        
        // Filter berdasarkan round
        if (!empty($filters['round'])) {
            $query->where('round_number', $filters['round']);
        }
        
        // Search
        if (!empty($filters['search'])) {
            $query->groupStart()
                  ->orLike('white_player_id', $filters['search'])
                  ->orLike('black_player_id', $filters['search'])
                  ->groupEnd();
        }
        
        return $query->orderBy('round_number', 'ASC')
                     ->orderBy('created_at', 'DESC')
                     ->paginate($perPage, 'default', $page);
    }
    
    // Method untuk match history user dengan paginasi
    public function getUserMatches($userId, $page = 1, $perPage = 20, $filters = [])
    {
        $query = $this->groupStart()
                    ->where('white_player_id', $userId)
                    ->orWhere('black_player_id', $userId)
                    ->groupEnd()
                    ->where('status', 'completed');
        
        // Filter berdasarkan result
        if (!empty($filters['result'])) {
            if ($filters['result'] === 'win') {
                $query->groupStart()
                      ->where('white_player_id', $userId)->where('result', '1-0')
                      ->orWhere('black_player_id', $userId)->where('result', '0-1')
                      ->groupEnd();
            } elseif ($filters['result'] === 'loss') {
                $query->groupStart()
                      ->where('white_player_id', $userId)->where('result', '0-1')
                      ->orWhere('black_player_id', $userId)->where('result', '1-0')
                      ->groupEnd();
            } elseif ($filters['result'] === 'draw') {
                $query->where('result', '1/2-1/2');
            }
        }
        
        // Filter berdasarkan tournament
        if (!empty($filters['tournament_id'])) {
            $query->where('tournament_id', $filters['tournament_id']);
        }
        
        // Filter berdasarkan tanggal
        if (!empty($filters['start_date'])) {
            $query->where('created_at >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('created_at <=', $filters['end_date']);
        }
        
        return $query->orderBy('created_at', 'DESC')
                     ->paginate($perPage, 'default', $page);
    }
    
    // Method untuk mencari semua match dengan paginasi
    public function getAllMatches($page = 1, $perPage = 20, $filters = [])
    {
        $query = $this;
        
        // Filter berdasarkan status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        // Filter berdasarkan result
        if (!empty($filters['result'])) {
            $query->where('result', $filters['result']);
        }
        
        // Filter berdasarkan tournament
        if (!empty($filters['tournament_id'])) {
            $query->where('tournament_id', $filters['tournament_id']);
        }
        
        // Filter berdasarkan player
        if (!empty($filters['player_id'])) {
            $query->groupStart()
                  ->where('white_player_id', $filters['player_id'])
                  ->orWhere('black_player_id', $filters['player_id'])
                  ->groupEnd();
        }
        
        // Search
        if (!empty($filters['search'])) {
            $query->groupStart()
                  ->orLike('white_player_id', $filters['search'])
                  ->orLike('black_player_id', $filters['search'])
                  ->orLike('tournament_id', $filters['search'])
                  ->orLike('result', $filters['search'])
                  ->groupEnd();
        }
        
        return $query->orderBy('created_at', 'DESC')
                     ->paginate($perPage, 'default', $page);
    }
    
    // Method untuk update match result
    public function updateResult($matchId, $result, $winReason = null, $endTime = null)
    {
        $data = [
            'result' => $result,
            'win_reason' => $winReason,
            'status' => 'completed'
        ];
        
        if ($endTime) {
            $data['end_time'] = $endTime;
        } else {
            $data['end_time'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($matchId, $data);
    }
    
    // Method untuk mendapatkan pager
    public function getPager()
    {
        return $this->pager;
    }
    
    // Method untuk mendapatkan statistik match
    public function getMatchStatistics($userId = null)
    {
        $query = $this;
        
        if ($userId) {
            $query->groupStart()
                  ->where('white_player_id', $userId)
                  ->orWhere('black_player_id', $userId)
                  ->groupEnd();
        }
        
        $totalMatches = $query->countAllResults();
        $completedMatches = $query->where('status', 'completed')->countAllResults();
        $ongoingMatches = $query->where('status', 'ongoing')->countAllResults();
        
        return [
            'total' => $totalMatches,
            'completed' => $completedMatches,
            'ongoing' => $ongoingMatches,
        ];
    }
}