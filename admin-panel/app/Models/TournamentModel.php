<?php

namespace App\Models;

use CodeIgniter\Model;

class TournamentModel extends Model
{
    protected $table            = 'tournaments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'title', 'description','registration_close', 'start_time', 'end_time', 'format',
        'tournament_identification', 'time_control_type', 'time_control_base', 
        'time_control_increment', 'entry_fee', 'prize_pool', 'status', 
        'created_by', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // --- CALLBACKS ---
    protected $beforeInsert = ['handlePreSave'];
    protected $beforeUpdate = ['handlePreSave'];
    protected $afterFind    = ['convertToMinutes'];

    protected $validationRules = [
        'title'                  => 'required|min_length[3]|max_length[150]',
        'time_control_type'      => 'required|in_list[standard,rapid,blitz,bullet]',
        'time_control_base'      => 'required|integer|greater_than[0]',
        'time_control_increment' => 'permit_empty|integer|greater_than_equal_to[0]',
        'entry_fee'              => 'permit_empty|decimal',
        'prize_pool'             => 'permit_empty|decimal',
        'status'                 => 'permit_empty|in_list[registration,active,completed,cancelled]',
        'format'                 => 'permit_empty|in_list[swiss,round_robin,knockout,arena]',
    ];

    // --------------------------------------------------------------------------
    // PAGINATION & SEARCH METHODS
    // --------------------------------------------------------------------------

    /**
     * Get all tournaments with pagination and search
     */
    public function getAllTournamentsPaginated($perPage = 10, $page = 1, $search = null, $status = null, $format = null, $timeControl = null)
    {
        $offset = ($page - 1) * $perPage;
        
        $builder = $this->select("tournaments.*")
            ->select("(
                SELECT COUNT(*) 
                FROM tournament_participants 
                WHERE tournament_participants.tournament_id = tournaments.id
            ) as total_participants")
            ->select("(
                SELECT COUNT(*) 
                FROM matches 
                WHERE matches.tournament_id = tournaments.id
            ) as total_matches")
            ->select("(
                SELECT COUNT(*) 
                FROM matches 
                WHERE matches.tournament_id = tournaments.id 
                AND matches.result != 'pending'
            ) as completed_matches");
        
        // Search condition
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('tournaments.title', $search)
                    ->orLike('tournaments.description', $search)
                    ->orLike('tournaments.tournament_identification', $search)
                    ->groupEnd();
        }
        
        // Status filter
        if (!empty($status) && $status !== 'all') {
            $builder->where('tournaments.status', $status);
        }
        
        // Format filter
        if (!empty($format) && $format !== 'all') {
            $builder->where('tournaments.format', $format);
        }
        
        // Time control filter
        if (!empty($timeControl) && $timeControl !== 'all') {
            $builder->where('tournaments.time_control_type', $timeControl);
        }
        
        return $builder->orderBy('tournaments.created_at', 'DESC')
                      ->limit($perPage, $offset)
                      ->findAll();
    }
    
    /**
     * Count tournaments with filters for pagination
     */
    public function countTournaments($search = null, $status = null, $format = null, $timeControl = null)
    {
        $builder = $this;
        
        // Search condition
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('title', $search)
                    ->orLike('description', $search)
                    ->orLike('tournament_identification', $search)
                    ->groupEnd();
        }
        
        // Status filter
        if (!empty($status) && $status !== 'all') {
            $builder->where('status', $status);
        }
        
        // Format filter
        if (!empty($format) && $format !== 'all') {
            $builder->where('format', $format);
        }
        
        // Time control filter
        if (!empty($timeControl) && $timeControl !== 'all') {
            $builder->where('time_control_type', $timeControl);
        }
        
        return $builder->countAllResults();
    }
    
    /**
     * Get tournaments statistics for dashboard
     */
    public function getTournamentStatistics()
    {
        return $this->db->query("
            SELECT
                COUNT(*) as total_tournaments,
                SUM(status = 'registration') as registration_tournaments,
                SUM(status = 'active') as active_tournaments,
                SUM(status = 'completed') as completed_tournaments,
                SUM(status = 'cancelled') as cancelled_tournaments,
                SUM(format = 'swiss') as swiss_format,
                SUM(format = 'round_robin') as round_robin_format,
                SUM(format = 'knockout') as knockout_format,
                SUM(format = 'arena') as arena_format,
                SUM(time_control_type = 'standard') as standard_time_control,
                SUM(time_control_type = 'rapid') as rapid_time_control,
                SUM(time_control_type = 'blitz') as blitz_time_control,
                SUM(time_control_type = 'bullet') as bullet_time_control,
                SUM(entry_fee > 0) as paid_tournaments,
                SUM(entry_fee = 0 OR entry_fee IS NULL) as free_tournaments,
                AVG(prize_pool) as avg_prize_pool,
                SUM(prize_pool) as total_prize_pool
            FROM tournaments
        ")->getRowArray();
    }
    
    /**
     * Search tournaments by title or identification
     */
    public function searchTournaments($keyword, $limit = 50)
    {
        return $this->select("tournaments.*")
            ->select("(
                SELECT COUNT(*) 
                FROM tournament_participants 
                WHERE tournament_participants.tournament_id = tournaments.id
            ) as total_participants")
            ->groupStart()
                ->like('title', $keyword)
                ->orLike('tournament_identification', $keyword)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    // --------------------------------------------------------------------------
    // CALLBACK LOGIC
    // --------------------------------------------------------------------------

    /**
     * Menangani semua logika sebelum data masuk ke database
     */
    protected function handlePreSave(array $data)
    {
        if (!isset($data['data'])) return $data;

        // 1. Konversi Menit ke Detik
        if (isset($data['data']['time_control_base'])) {
            $data['data']['time_control_base'] = (int) $data['data']['time_control_base'] * 60;
        }

        // 2. Logika Irisan Waktu & Tournament Identification
        if (isset($data['data']['start_time']) && isset($data['data']['end_time'])) {
            $start = $data['data']['start_time'];
            $end   = $data['data']['end_time'];
            
            // Ambil ID jika sedang update untuk mengecualikan diri sendiri
            $currentId = $data['id'][0] ?? null;

            // Cari turnamen yang beririsan: (StartA < EndB) AND (EndA > StartB)
            $builder = $this->db->table($this->table)
                ->select('tournament_identification')
                ->where('start_time <', $end)
                ->where('end_time >', $start);

            if ($currentId) {
                $builder->where('id !=', $currentId);
            }

            $overlap = $builder->get()->getRow();

            if ($overlap && !empty($overlap->tournament_identification)) {
                // Gunakan identifikasi yang sudah ada jika beririsan
                $data['data']['tournament_identification'] = $overlap->tournament_identification;
            } else {
                // Generate identifikasi baru jika tidak ada irisan atau baru
                $data['data']['tournament_identification'] = $this->generateRandomString(50);
            }
        }

        return $data;
    }

    /**
     * Konversi Detik ke Menit setelah Ambil Data (find/findAll)
     */
    protected function convertToMinutes(array $data)
    {
        if (empty($data['data'])) return $data;

        $transform = function($row) {
            if (is_object($row) && isset($row->time_control_base)) {
                $row->time_control_base = $row->time_control_base / 60;
            }
            return $row;
        };

        if (is_array($data['data'])) {
            foreach ($data['data'] as &$item) {
                $item = $transform($item);
            }
        } else {
            $data['data'] = $transform($data['data']);
        }

        return $data;
    }

    /**
     * Helper: Generate String Random 50 Karakter
     */
    private function generateRandomString($length = 50) {
        return bin2hex(random_bytes($length / 2));
    }

    // --------------------------------------------------------------------------
    // REPOSITORY METHODS (ORIGINAL - MAINTAINED FOR BACKWARD COMPATIBILITY)
    // --------------------------------------------------------------------------

    /**
     * Helper untuk menambahkan subquery hitung peserta
     */
    private function selectWithCount()
    {
        $subQuery = "(SELECT COUNT(*) FROM tournament_participants WHERE tournament_participants.tournament_id = tournaments.id) as total_participants";
        return $this->select("tournaments.*, {$subQuery}");
    }
    
    public function getActiveTournaments()
    {
        return $this->whereIn('status', ['registration', 'active'])->findAll();
    }
    
    public function getAllTournaments()
    {
        return $this->selectWithCount()
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    public function getUpcomingTournaments($limit = 10)
    {
        return $this->selectWithCount()
                    ->where('status', 'registration')
                    ->where('start_time >', date('Y-m-d H:i:s'))
                    ->orderBy('start_time', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getTournamentById($id)
    {
        return $this->selectWithCount()
                   ->where('id', $id)
                   ->first();
    }
    
    public function getParticipants($tournamentId)
    {
        $tournament = $this->db->table('tournaments')
                           ->select('time_control_type')
                           ->where('id', $tournamentId)
                           ->get()->getRow();

        if (!$tournament) return [];

        $ratingCol = $tournament->time_control_type . '_rating';

        return $this->db->table('tournament_participants')
            ->select("
                tournament_participants.*, 
                users.username, 
                users.full_name, 
                users.email, 
                users.user_status as status, 
                user_ratings.{$ratingCol} as current_rating
            ")
            ->join('users', 'users.id = tournament_participants.user_id')
            ->join('user_ratings', 'user_ratings.user_id = tournament_participants.user_id', 'left')
            ->where('tournament_participants.tournament_id', $tournamentId)
            ->orderBy("user_ratings.{$ratingCol}", 'DESC') 
            ->get()->getResult(); 
    }
    
    public function getParticipantCount($tournamentId)
    {
        return (int) $this->db->table('tournament_participants')
                              ->where('tournament_id', $tournamentId)
                              ->countAllResults();
    }
    
    public function updateStatus($tournamentId, $status)
    {
        return $this->update($tournamentId, ['status' => $status]);
    }
    
    /**
     * Get recent tournaments for dashboard widget
     */
    public function getRecentTournaments($limit = 5)
    {
        return $this->select("tournaments.*")
            ->select("(
                SELECT COUNT(*) 
                FROM tournament_participants 
                WHERE tournament_participants.tournament_id = tournaments.id
            ) as total_participants")
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
    
    /**
     * Get tournaments by creator (user ID)
     */
    public function getTournamentsByCreator($userId, $perPage = 10, $page = 1)
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->selectWithCount()
                   ->where('created_by', $userId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($perPage, $offset)
                   ->findAll();
    }
}