<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchMoveModel extends Model
{
    protected $table = 'match_moves';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'match_id', 'move_number', 'san', 'uci',
        'fen_snapshot', 'time_spent', 'stockfish_eval',
        'is_best_move', 'move_category',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'match_id' => 'required|integer|is_not_unique[matches.id]',
        'move_number' => 'required|integer|greater_than[0]',
        'san' => 'required|max_length[10]',
        'uci' => 'required|max_length[10]',
        'move_category' => 'permit_empty|in_list[best,good,inaccuracy,mistake,blunder]',
        'is_best_move' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk moves berdasarkan match
    public function getMatchMoves($matchId)
    {
        return $this->where('match_id', $matchId)
                    ->orderBy('move_number', 'ASC')
                    ->findAll();
    }
    
    // Method untuk menambahkan move baru
    public function addMove($matchId, $moveNumber, $san, $uci, $fenSnapshot = null, $timeSpent = null)
    {
        $data = [
            'match_id' => $matchId,
            'move_number' => $moveNumber,
            'san' => $san,
            'uci' => $uci,
            'fen_snapshot' => $fenSnapshot,
            'time_spent' => $timeSpent
        ];
        
        return $this->insert($data);
    }
    
    // Method untuk update move dengan evaluasi Stockfish
    public function updateMoveEvaluation($moveId, $stockfishEval, $isBestMove = false, $moveCategory = null)
    {
        $data = [
            'stockfish_eval' => $stockfishEval,
            'is_best_move' => $isBestMove ? 1 : 0
        ];
        
        if ($moveCategory) {
            $data['move_category'] = $moveCategory;
        }
        
        return $this->update($moveId, $data);
    }
}