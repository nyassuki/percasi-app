<?php

namespace App\Models;

use CodeIgniter\Model;

class TournamentParticipantModel extends Model
{
    protected $table = 'tournament_participants';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'tournament_id', 'user_id', 'current_score',
        'tie_break_score', 'is_disqualified', 'has_bye',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'tournament_id' => 'required|integer|is_not_unique[tournaments.id]',
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'current_score' => 'permit_empty|decimal',
        'tie_break_score' => 'permit_empty|decimal',
        'is_disqualified' => 'permit_empty|in_list[0,1]',
        'has_bye' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk peserta tournament
    public function getTournamentParticipants($tournamentId)
    {
        return $this->where('tournament_id', $tournamentId)
                    ->orderBy('current_score', 'DESC')
                    ->orderBy('tie_break_score', 'DESC')
                    ->findAll();
    }
    
    // Method untuk cek apakah user sudah terdaftar
    public function isUserRegistered($tournamentId, $userId)
    {
        return $this->where('tournament_id', $tournamentId)
                    ->where('user_id', $userId)
                    ->first();
    }
    
    // Method untuk mendaftarkan peserta
    public function registerParticipant($tournamentId, $userId)
    {
        $data = [
            'tournament_id' => $tournamentId,
            'user_id' => $userId,
            'current_score' => 0.0,
            'tie_break_score' => 0.00,
            'is_disqualified' => 0,
            'has_bye' => 0
        ];
        
        return $this->insert($data);
    }
    
    // Method untuk update score peserta
    public function updateParticipantScore($participantId, $score, $tieBreakScore = 0)
    {
        return $this->update($participantId, [
            'current_score' => $score,
            'tie_break_score' => $tieBreakScore
        ]);
    }
}