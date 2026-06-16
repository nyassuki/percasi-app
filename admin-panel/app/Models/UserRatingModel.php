<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRatingModel extends Model
{
    protected $table = 'user_ratings';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'standard_rating', 'rapid_rating', 'blitz_rating',
        'bullet_rating', 'wins', 'losses', 'draws',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'user_id'         => 'required|integer|is_not_unique[users.id]',
        'standard_rating' => 'permit_empty|integer|greater_than_equal_to[0]',
        'rapid_rating'    => 'permit_empty|integer|greater_than_equal_to[0]',
        'blitz_rating'    => 'permit_empty|integer|greater_than_equal_to[0]',
        'bullet_rating'   => 'permit_empty|integer|greater_than_equal_to[0]',
        'wins'            => 'permit_empty|integer|greater_than_equal_to[0]',
        'losses'          => 'permit_empty|integer|greater_than_equal_to[0]',
        'draws'           => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    /**
     * Laporan Admin dengan Pencarian & Join User + Pagination + Sorting
     */
    public function getRatingReportPaginated($search = null, $perPage = 10, $page = 1, $sortBy = 'standard_rating', $sortOrder = 'DESC')
    {
        $offset = ($page - 1) * $perPage;
        
        $builder = $this->select("
                user_ratings.*, 
                users.username, 
                users.full_name, 
                users.avatar_url
            ")
            ->join('users', 'users.id = user_ratings.user_id', 'right');
        
        if ($search) {
            $builder->groupStart()
                    ->like('users.username', $search)
                    ->orLike('users.full_name', $search)
                    ->orWhere('user_ratings.user_id', $search)
                    ->groupEnd();
        }
        
        // Handle sorting
        $allowedSortColumns = ['standard_rating', 'rapid_rating', 'blitz_rating', 'bullet_rating', 'wins', 'losses', 'draws'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'standard_rating';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Special case for win rate calculation
        if ($sortBy === 'win_rate') {
            $builder->orderBy("(user_ratings.wins / (user_ratings.wins + user_ratings.losses + user_ratings.draws))", $sortOrder);
        } else {
            $builder->orderBy("user_ratings.{$sortBy}", $sortOrder);
        }
        
        return $builder->limit($perPage, $offset)->findAll();
    }

    /**
     * Count total rating reports for pagination
     */
    public function countRatingReport($search = null)
    {
        $builder = $this->join('users', 'users.id = user_ratings.user_id', 'right');
        
        if ($search) {
            $builder->groupStart()
                    ->like('users.username', $search)
                    ->orLike('users.full_name', $search)
                    ->orWhere('user_ratings.user_id', $search)
                    ->groupEnd();
        }
        
        return $builder->countAllResults();
    }

    /**
     * Get detailed user match statistics from matches table
     * @param int $userId
     * @return array
     */
    public function getUserMatchStatistics($userId)
    {
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                
                -- Hitung Menang (Wins)
                SUM(CASE 
                    WHEN (white_player_id = ? AND result = '1-0') THEN 1
                    WHEN (black_player_id = ? AND result = '0-1') THEN 1
                    ELSE 0 
                END) as wins,

                -- Hitung Seri (Draws)
                SUM(CASE 
                    WHEN result = '1/2-1/2' THEN 1
                    ELSE 0 
                END) as draws,

                -- Hitung Kekalahan (Losses)
                SUM(CASE 
                    WHEN (white_player_id = ? AND result = '0-1') THEN 1
                    WHEN (black_player_id = ? AND result = '1-0') THEN 1
                    ELSE 0 
                END) as losses,

                -- Hitung sebagai Putih (White)
                SUM(CASE 
                    WHEN white_player_id = ? THEN 1
                    ELSE 0 
                END) as as_white,

                -- Hitung sebagai Hitam (Black)
                SUM(CASE 
                    WHEN black_player_id = ? THEN 1
                    ELSE 0 
                END) as as_black,

                -- Hitung kemenangan sebagai Putih
                SUM(CASE 
                    WHEN white_player_id = ? AND result = '1-0' THEN 1
                    ELSE 0 
                END) as wins_as_white,

                -- Hitung kemenangan sebagai Hitam
                SUM(CASE 
                    WHEN black_player_id = ? AND result = '0-1' THEN 1
                    ELSE 0 
                END) as wins_as_black

            FROM matches 
            WHERE 
                (white_player_id = ? OR black_player_id = ?) 
                AND result IN ('1-0', '0-1', '1/2-1/2')
                AND status = 'completed'
        ", array_fill(0, 10, $userId));

        $result = $query->getRow();
        
        if (!$result) {
            return [
                'total' => 0,
                'wins' => 0,
                'losses' => 0,
                'draws' => 0,
                'as_white' => 0,
                'as_black' => 0,
                'wins_as_white' => 0,
                'wins_as_black' => 0,
                'win_rate' => 0,
                'win_rate_as_white' => 0,
                'win_rate_as_black' => 0
            ];
        }

        // Hitung persentase
        $total = (int)$result->total;
        $wins = (int)$result->wins;
        $asWhite = (int)$result->as_white;
        $asBlack = (int)$result->as_black;
        $winsAsWhite = (int)$result->wins_as_white;
        $winsAsBlack = (int)$result->wins_as_black;

        $winRate = $total > 0 ? round(($wins / $total) * 100, 2) : 0;
        $winRateAsWhite = $asWhite > 0 ? round(($winsAsWhite / $asWhite) * 100, 2) : 0;
        $winRateAsBlack = $asBlack > 0 ? round(($winsAsBlack / $asBlack) * 100, 2) : 0;

        return [
            'total' => $total,
            'wins' => $wins,
            'losses' => (int)$result->losses,
            'draws' => (int)$result->draws,
            'as_white' => $asWhite,
            'as_black' => $asBlack,
            'wins_as_white' => $winsAsWhite,
            'wins_as_black' => $winsAsBlack,
            'win_rate' => $winRate,
            'win_rate_as_white' => $winRateAsWhite,
            'win_rate_as_black' => $winRateAsBlack,
            'longest_streak' => $this->getLongestWinStreak($userId),
            'recent_form' => $this->getRecentForm($userId, 10)
        ];
    }

    /**
     * Get longest win streak for a user
     * @param int $userId
     * @return int
     */
    private function getLongestWinStreak($userId)
    {
        $query = $this->db->query("
            SELECT 
                MAX(streak) as longest_streak
            FROM (
                SELECT 
                    COUNT(*) as streak
                FROM (
                    SELECT 
                        match_id,
                        CASE 
                            WHEN (white_player_id = ? AND result = '1-0') THEN 'win'
                            WHEN (black_player_id = ? AND result = '0-1') THEN 'win'
                            WHEN (white_player_id = ? AND result = '0-1') THEN 'loss'
                            WHEN (black_player_id = ? AND result = '1-0') THEN 'loss'
                            WHEN result = '1/2-1/2' THEN 'draw'
                        END as outcome
                    FROM matches
                    WHERE 
                        (white_player_id = ? OR black_player_id = ?)
                        AND result IN ('1-0', '0-1', '1/2-1/2')
                        AND status = 'completed'
                    ORDER BY match_id
                ) as game_outcomes
                WHERE outcome = 'win'
                GROUP BY (
                    SELECT COUNT(*)
                    FROM (
                        SELECT 
                            CASE 
                                WHEN (white_player_id = ? AND result = '1-0') THEN 'win'
                                WHEN (black_player_id = ? AND result = '0-1') THEN 'win'
                                WHEN (white_player_id = ? AND result = '0-1') THEN 'loss'
                                WHEN (black_player_id = ? AND result = '1-0') THEN 'loss'
                                WHEN result = '1/2-1/2' THEN 'draw'
                            END as prev_outcome
                        FROM matches as m2
                        WHERE m2.match_id <= game_outcomes.match_id
                            AND (m2.white_player_id = ? OR m2.black_player_id = ?)
                            AND m2.result IN ('1-0', '0-1', '1/2-1/2')
                            AND m2.status = 'completed'
                        ORDER BY m2.match_id
                    ) as prev_games
                    WHERE prev_outcome != 'win'
                )
            ) as streaks
        ", array_fill(0, 14, $userId));

        $result = $query->getRow();
        return $result ? (int)$result->longest_streak : 0;
    }

    /**
     * Get recent form (last N games)
     * @param int $userId
     * @param int $limit
     * @return array
     */
    private function getRecentForm($userId, $limit = 10)
    {
        $query = $this->db->query("
            SELECT 
                match_id,
                CASE 
                    WHEN (white_player_id = ? AND result = '1-0') THEN 'win'
                    WHEN (black_player_id = ? AND result = '0-1') THEN 'win'
                    WHEN (white_player_id = ? AND result = '0-1') THEN 'loss'
                    WHEN (black_player_id = ? AND result = '1-0') THEN 'loss'
                    WHEN result = '1/2-1/2' THEN 'draw'
                END as outcome,
                result,
                white_player_id,
                black_player_id,
                match_date
            FROM matches
            WHERE 
                (white_player_id = ? OR black_player_id = ?)
                AND result IN ('1-0', '0-1', '1/2-1/2')
                AND status = 'completed'
            ORDER BY match_date DESC, match_id DESC
            LIMIT ?
        ", array_merge(array_fill(0, 6, $userId), [$limit]));

        $results = $query->getResultArray();
        $form = [];
        
        foreach ($results as $row) {
            switch ($row['outcome']) {
                case 'win':
                    $form[] = 'W';
                    break;
                case 'loss':
                    $form[] = 'L';
                    break;
                case 'draw':
                    $form[] = 'D';
                    break;
            }
        }
        
        return $form;
    }

    /**
     * Get user performance by time control
     * @param int $userId
     * @return array
     */
    public function getUserPerformanceByTimeControl($userId)
    {
        $query = $this->db->query("
            SELECT 
                time_control,
                COUNT(*) as total_games,
                SUM(CASE 
                    WHEN (white_player_id = ? AND result = '1-0') THEN 1
                    WHEN (black_player_id = ? AND result = '0-1') THEN 1
                    ELSE 0 
                END) as wins,
                SUM(CASE 
                    WHEN (white_player_id = ? AND result = '0-1') THEN 1
                    WHEN (black_player_id = ? AND result = '1-0') THEN 1
                    ELSE 0 
                END) as losses,
                SUM(CASE 
                    WHEN result = '1/2-1/2' THEN 1
                    ELSE 0 
                END) as draws
            FROM matches
            WHERE 
                (white_player_id = ? OR black_player_id = ?)
                AND result IN ('1-0', '0-1', '1/2-1/2')
                AND status = 'completed'
            GROUP BY time_control
            ORDER BY total_games DESC
        ", array_fill(0, 6, $userId));

        $results = $query->getResultArray();
        
        $performance = [];
        foreach ($results as $row) {
            $total = $row['total_games'];
            $wins = $row['wins'];
            
            $performance[$row['time_control']] = [
                'total_games' => (int)$total,
                'wins' => (int)$wins,
                'losses' => (int)$row['losses'],
                'draws' => (int)$row['draws'],
                'win_rate' => $total > 0 ? round(($wins / $total) * 100, 2) : 0
            ];
        }
        
        return $performance;
    }

    /**
     * Update user rating statistics from matches table
     * @param int $userId
     * @return bool
     */
    public function updateUserStatsFromMatches($userId)
    {
        $stats = $this->getUserMatchStatistics($userId);
        
        return $this->update($userId, [
            'wins' => $stats['wins'],
            'losses' => $stats['losses'],
            'draws' => $stats['draws'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Memastikan record rating ada, jika tidak buat baru dengan default 1200
     */
    private function ensureUserExists($userId)
    {
        if (!$this->find($userId)) {
            $this->insert([
                'user_id'         => $userId,
                'standard_rating' => 1200,
                'rapid_rating'    => 1200,
                'blitz_rating'    => 1200,
                'bullet_rating'   => 1200,
                'wins'            => 0,
                'losses'          => 0,
                'draws'           => 0
            ]);
        }
    }

    /**
     * Update Rating berdasarkan tipe (standard/rapid/blitz/bullet)
     */
    public function updateRating($userId, $ratingType, $newRating)
    {
        $allowedTypes = ['standard_rating', 'rapid_rating', 'blitz_rating', 'bullet_rating'];
        
        if (!in_array($ratingType, $allowedTypes)) {
            throw new \InvalidArgumentException('Invalid rating type');
        }

        $this->ensureUserExists($userId);
        
        return $this->update($userId, [$ratingType => $newRating]);
    }

    /**
     * Update Stats (Atomic Increment)
     * Menggunakan set(col, val, false) agar database melakukan increment secara langsung
     */
    public function updateStats($userId, $result)
    {
        $this->ensureUserExists($userId);

        $column = '';
        switch ($result) {
            case 'win':  $column = 'wins'; break;
            case 'loss': $column = 'losses'; break;
            case 'draw': $column = 'draws'; break;
            default: return false;
        }

        return $this->set($column, "$column + 1", false)
                    ->where('user_id', $userId)
                    ->update();
    }
}