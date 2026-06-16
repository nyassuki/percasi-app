<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Cache\CacheInterface;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $cache;

    // Waktu cache untuk query yang berat (dalam detik)
    private const CACHE_TIME = 300; // 5 menit

    protected $allowedFields = [
        'full_name',
        'kyc_status',
        'kyc_rejection_reason',
        'user_status',
        'open_match',
        'kyc_verified_at',
        'is_2fa_active',
        'is_single_login',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->cache = \Config\Services::cache();
    }

    /* =========================
     * PAGING & SEARCH METHODS - OPTIMIZED
     * ========================= */
    
    /**
     * Get all users with pagination and search - OPTIMIZED VERSION
     */
    public function getAllUsersPaginated($perPage = 10, $page = 1, $search = null, $userStatus = 'all', $kycStatus = 'all')
    {
        $offset = ($page - 1) * $perPage;
        
        // Cache key berdasarkan parameter
        $cacheKey = "users_paginated_{$perPage}_{$page}_" . md5($search . $userStatus . $kycStatus);
        
        // Cek cache dulu
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Gunakan query builder dengan optimasi
        $builder = $this->select("
                users.id,
                users.full_name,
                users.username,
                users.email,
                users.avatar_url,
                users.user_status,
                users.kyc_status,
                users.open_match,
                users.is_2fa_active,
                users.is_single_login,
                users.created_at,
                -- Subquery untuk balance
                (SELECT balance FROM wallets WHERE user_id = users.id LIMIT 1) as balance,
                -- Subquery untuk ratings dengan COALESCE untuk handle NULL
                COALESCE((SELECT standard_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as standard_rating,
                COALESCE((SELECT rapid_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as rapid_rating,
                COALESCE((SELECT blitz_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as blitz_rating,
                COALESCE((SELECT bullet_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as bullet_rating,
                -- Subquery untuk province
                (SELECT name FROM master_provinces WHERE id = users.province_id LIMIT 1) as province_name,
                -- Subquery untuk regency
                (SELECT name FROM master_regencies WHERE id = users.regency_id LIMIT 1) as regency_name
            ");
        
        // Search condition dengan FULLTEXT index jika tersedia
        if (!empty($search)) {
            if ($this->hasFulltextIndex()) {
                $builder->match(['full_name', 'username', 'email'], $search);
            } else {
                $builder->groupStart()
                    ->like('users.full_name', $search . '%')
                    ->orLike('users.username', $search . '%')
                    ->orLike('users.email', $search . '%')
                    ->orWhere('users.id', $search)
                    ->groupEnd();
            }
        }
        
        // User status filter
        if ($userStatus !== 'all') {
            $builder->where('users.user_status', $userStatus);
        }
        
        // KYC status filter
        if ($kycStatus !== 'all') {
            $builder->where('users.kyc_status', $kycStatus);
        }
        
        $result = $builder->orderBy('users.id', 'DESC')
                         ->limit($perPage, $offset)
                         ->findAll();
        
        // Cache hasil
        $this->cache->save($cacheKey, $result, self::CACHE_TIME);
        
        return $result;
    }
    
    /**
     * Count all users with filters - OPTIMIZED
     */
    public function countAllUsers($search = null, $userStatus = 'all', $kycStatus = 'all')
    {
        $cacheKey = "count_users_" . md5($search . $userStatus . $kycStatus);
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $builder = $this;
        
        if (!empty($search)) {
            $builder->groupStart()
                ->like('full_name', $search . '%')
                ->orLike('username', $search . '%')
                ->orLike('email', $search . '%')
                ->groupEnd();
        }
        
        if ($userStatus !== 'all') {
            $builder->where('user_status', $userStatus);
        }
        
        if ($kycStatus !== 'all') {
            $builder->where('kyc_status', $kycStatus);
        }
        
        $count = $builder->countAllResults();
        $this->cache->save($cacheKey, $count, self::CACHE_TIME);
        
        return $count;
    }
    
    /**
     * Get pending KYC users with pagination - OPTIMIZED
     */
    public function getAllPendingKYCWithPagination($perPage = 10, $page = 1, $search = null)
    {
        $offset = ($page - 1) * $perPage;
        
        $builder = $this->select("
                users.id,
                users.full_name,
                users.username,
                users.email,
                users.avatar_url,
                users.user_status,
                users.kyc_status,
                users.open_match,
                users.is_2fa_active,
                users.is_single_login,
                (SELECT balance FROM wallets WHERE user_id = users.id LIMIT 1) as balance,
                COALESCE((SELECT standard_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as standard_rating,
                COALESCE((SELECT rapid_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as rapid_rating,
                COALESCE((SELECT blitz_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as blitz_rating,
                COALESCE((SELECT bullet_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as bullet_rating,
                (SELECT name FROM master_provinces WHERE id = users.province_id LIMIT 1) as province_name,
                (SELECT name FROM master_regencies WHERE id = users.regency_id LIMIT 1) as regency_name
            ")
            ->where('users.kyc_status', 'pending');
        
        if (!empty($search)) {
            $builder->groupStart()
                ->like('users.full_name', $search . '%')
                ->orLike('users.username', $search . '%')
                ->orLike('users.email', $search . '%')
                ->groupEnd();
        }
        
        return $builder->orderBy('users.id', 'DESC')
                      ->limit($perPage, $offset)
                      ->findAll();
    }
    
    /**
     * Count pending KYC users - OPTIMIZED
     */
    public function countPendingKYC($search = null)
    {
        $builder = $this->where('kyc_status', 'pending');
        
        if (!empty($search)) {
            $builder->groupStart()
                ->like('full_name', $search . '%')
                ->orLike('username', $search . '%')
                ->orLike('email', $search . '%')
                ->groupEnd();
        }
        
        return $builder->countAllResults();
    }

    /* =========================
     * USER DETAIL - OPTIMIZED
     * ========================= */
    public function getFullDetail(int $id)
    {
        $cacheKey = "user_full_detail_{$id}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $result = $this->select("
                users.*,
                (SELECT balance FROM wallets WHERE user_id = users.id LIMIT 1) as balance,
                COALESCE((SELECT standard_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as standard_rating,
                COALESCE((SELECT wins FROM user_ratings WHERE user_id = users.id LIMIT 1), 0) as wins,
                COALESCE((SELECT losses FROM user_ratings WHERE user_id = users.id LIMIT 1), 0) as losses,
                COALESCE((SELECT draws FROM user_ratings WHERE user_id = users.id LIMIT 1), 0) as draws,
                (SELECT name FROM master_provinces WHERE id = users.province_id LIMIT 1) as prov,
                (SELECT name FROM master_regencies WHERE id = users.regency_id LIMIT 1) as reg,
                (SELECT name FROM master_districts WHERE id = users.district_id LIMIT 1) as dist,
                (SELECT name FROM master_subdistricts WHERE id = users.subdistrict_id LIMIT 1) as subdist,
                (SELECT va_number FROM user_virtual_accounts WHERE user_id = users.id LIMIT 1) as va_number,
                (SELECT bank_code FROM user_virtual_accounts WHERE user_id = users.id LIMIT 1) as va_bank,
                (SELECT account_number FROM user_bank_accounts WHERE user_id = users.id LIMIT 1) as account_number,
                (SELECT account_holder_name FROM user_bank_accounts WHERE user_id = users.id LIMIT 1) as account_holder_name,
                (SELECT bank_code FROM user_bank_accounts WHERE user_id = users.id LIMIT 1) as bank_name
            ")
            ->where('users.id', $id)
            ->first();
            
        $this->cache->save($cacheKey, $result, self::CACHE_TIME);
        
        return $result;
    }

    /* =========================
     * ADMIN USER LIST - OPTIMIZED
     * ========================= */
    public function getAllUsers()
    {
        return $this->select("
                users.id,
                users.full_name,
                users.username,
                users.email,
                users.avatar_url,
                users.user_status,
                users.kyc_status,
                users.open_match,
                users.is_2fa_active,
                users.is_single_login,
                (SELECT balance FROM wallets WHERE user_id = users.id LIMIT 1) as balance,
                COALESCE((SELECT standard_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as standard_rating,
                COALESCE((SELECT rapid_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as rapid_rating,
                COALESCE((SELECT blitz_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as blitz_rating,
                COALESCE((SELECT bullet_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as bullet_rating,
                (SELECT name FROM master_provinces WHERE id = users.province_id LIMIT 1) as province_name,
                (SELECT name FROM master_regencies WHERE id = users.regency_id LIMIT 1) as regency_name
            ")
            ->orderBy('users.id', 'DESC')
            ->findAll();
    }

    /* =========================
     * VIRTUAL ACCOUNT
     * ========================= */
    public function getVA(int $user_id)
    {
        return $this->db->table('user_virtual_accounts')
            ->where('user_id', $user_id)
            ->limit(1)
            ->get()
            ->getResultArray();
    }
    
    public function GetAllPendingKYC()
    {
        $builder = $this->db->table('users u');
        $builder->select([
            'u.id',
            'u.full_name',
            'u.username',
            'u.email',
            'u.avatar_url',
            'u.user_status',
            'u.kyc_status',
            'u.open_match',
            'u.is_2fa_active',
            'u.is_single_login',
            '(SELECT balance FROM wallets WHERE user_id = u.id LIMIT 1) as balance',
            'COALESCE((SELECT standard_rating FROM user_ratings WHERE user_id = u.id LIMIT 1), 1200) as standard_rating',
            'COALESCE((SELECT rapid_rating FROM user_ratings WHERE user_id = u.id LIMIT 1), 1200) as rapid_rating',
            'COALESCE((SELECT blitz_rating FROM user_ratings WHERE user_id = u.id LIMIT 1), 1200) as blitz_rating',
            'COALESCE((SELECT bullet_rating FROM user_ratings WHERE user_id = u.id LIMIT 1), 1200) as bullet_rating',
            '(SELECT name FROM master_provinces WHERE id = u.province_id LIMIT 1) as province_name',
            '(SELECT name FROM master_regencies WHERE id = u.regency_id LIMIT 1) as regency_name',
        ]);

        $builder->where('u.kyc_status', 'pending');
        $builder->orderBy('u.id', 'DESC');
        $builder->limit(1000);

        return $builder->get()->getResultArray();
    }
    
    public function getAllUsersByNameOrUserName($name)
    {
        $builder = $this->select("
                    users.id,
                    users.full_name,
                    users.username,
                    users.email,
                    users.avatar_url,
                    users.user_status,
                    users.kyc_status,
                    users.open_match,
                    users.is_2fa_active,
                    users.is_single_login,
                    (SELECT balance FROM wallets WHERE user_id = users.id LIMIT 1) as balance,
                    COALESCE((SELECT standard_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as standard_rating,
                    COALESCE((SELECT rapid_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as rapid_rating,
                    COALESCE((SELECT blitz_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as blitz_rating,
                    COALESCE((SELECT bullet_rating FROM user_ratings WHERE user_id = users.id LIMIT 1), 1200) as bullet_rating,
                    (SELECT name FROM master_provinces WHERE id = users.province_id LIMIT 1) as province_name,
                    (SELECT name FROM master_regencies WHERE id = users.regency_id LIMIT 1) as regency_name
                ");
        
        if (!empty($name)) {
            $builder->groupStart()
                ->like('users.full_name', $name . '%')
                ->orLike('users.username', $name . '%')
            ->groupEnd();
        }
        
        return $builder->orderBy('users.id', 'DESC')
                      ->limit(50)
                      ->findAll();
    }
    
    /* =========================
     * TRANSACTION HISTORY - OPTIMIZED
     * ========================= */
    public function getTransactionHistory(int $user_id)
    {
        $cacheKey = "transactions_{$user_id}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Gunakan tabel transactions langsung (asumsi ada)
        $result = $this->db->table('transactions')
            ->select('*')
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();
            
        $this->cache->save($cacheKey, $result, self::CACHE_TIME);
        
        return $result;
    }

    /* =========================
     * MATCH HISTORY - OPTIMIZED
     * ========================= */
    public function getMatchHistory($userId)
    {
        $cacheKey = "match_history_{$userId}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Query yang dioptimasi
        $query = "
        (
            SELECT 
                m.id as match_id,
                m.result,
                m.win_reason,
                m.fen_final,
                m.created_at,
                m.player_timer,
                'white' as user_role,
                wu.username as opponent_username,
                wu.avatar_url as opponent_avatar,
                COALESCE(wr.standard_rating, 1200) as opponent_rating,
                COALESCE(ur.standard_rating, 1200) as my_rating
            FROM matches m
            INNER JOIN users wu ON m.white_player_id = wu.id
            LEFT JOIN user_ratings wr ON m.black_player_id = wr.user_id
            LEFT JOIN user_ratings ur ON m.white_player_id = ur.user_id
            WHERE m.white_player_id = ?
        )
        UNION ALL
        (
            SELECT 
                m.id as match_id,
                m.result,
                m.win_reason,
                m.fen_final,
                m.created_at,
                m.player_timer,
                'black' as user_role,
                bu.username as opponent_username,
                bu.avatar_url as opponent_avatar,
                COALESCE(br.standard_rating, 1200) as opponent_rating,
                COALESCE(ur.standard_rating, 1200) as my_rating
            FROM matches m
            INNER JOIN users bu ON m.black_player_id = bu.id
            LEFT JOIN user_ratings br ON m.white_player_id = br.user_id
            LEFT JOIN user_ratings ur ON m.black_player_id = ur.user_id
            WHERE m.black_player_id = ?
        )
        ORDER BY created_at DESC
        LIMIT 100
        ";

        try {
            $result = $this->db->query($query, [$userId, $userId]);
            $rows = $result->getResultArray();
            
            $processedHistory = [];
            foreach ($rows as $row) {
                $timeInMinutes = $row['player_timer'] ?
                    floor($row['player_timer'] / 60000) : 0;

                $processedHistory[] = [
                    'id' => $row['match_id'],
                    'user_role' => $row['user_role'],
                    'result' => $row['result'],
                    'win_reason' => $row['win_reason'],
                    'fen_final' => $row['fen_final'],
                    'date' => $row['created_at'],
                    'time_control_label' => $timeInMinutes > 0 ? $timeInMinutes . ' min' : 'Custom',
                    'opponent_username' => $row['opponent_username'],
                    'opponent_avatar' => $row['opponent_avatar'],
                    'opponent_rating' => $row['opponent_rating'],
                    'my_rating' => $row['my_rating']
                ];
            }
            
            $this->cache->save($cacheKey, $processedHistory, self::CACHE_TIME);
            
            return $processedHistory;
        } catch (\Exception $err) {
            log_message('error', '[SQL ERROR] getMatchHistory: ' . $err->getMessage());
            
            // Return empty array jika error
            return [];
        }
    }
    
    /* =========================
     * USER STATISTICS - OPTIMIZED dengan Caching
     * ========================= */
    public function getUserStatistics()
    {
        $cacheKey = "user_statistics";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $result = $this->db->query("
            SELECT
                COUNT(*) AS total_user,
                SUM(user_status = 'ACT') AS active_user,
                SUM(user_status = 'NCT') AS inactive_user,
                SUM(user_status = 'BND') AS banned_user,
                SUM(open_match = 'YES') AS open_match_yes,
                SUM(open_match = 'NO') AS open_match_no,
                SUM(kyc_status = 'none') AS kyc_none,
                SUM(kyc_status = 'pending') AS kyc_pending,
                SUM(kyc_status = 'verified') AS kyc_verified,
                SUM(kyc_status = 'rejected') AS kyc_rejected,
                SUM(is_phone_verified = 1) AS phone_verified,
                SUM(is_phone_verified = 0) AS phone_not_verified
            FROM users
        ")->getRowArray();
        
        $this->cache->save($cacheKey, $result, self::CACHE_TIME);
        
        return $result;
    }
    
    /**
     * Mendapatkan Statistik User dengan optimasi dan caching
     */
    public function getUserStats($userId)
    {
        $cacheKey = "user_stats_{$userId}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        try {
            // Ambil data rating dengan default values
            $userRating = $this->db->table('user_ratings')
                                   ->select('
                                       COALESCE(standard_rating, 1200) as standard_rating,
                                       COALESCE(wins, 0) as wins,
                                       COALESCE(losses, 0) as losses,
                                       COALESCE(draws, 0) as draws,
                                       COALESCE(rapid_rating, 1200) as rapid_rating,
                                       COALESCE(blitz_rating, 1200) as blitz_rating,
                                       COALESCE(bullet_rating, 1200) as bullet_rating
                                   ')
                                   ->where('user_id', $userId)
                                   ->limit(1)
                                   ->get()
                                   ->getRow();

            if (!$userRating) {
                // Jika tidak ada data di user_ratings, buat default
                $result = [
                    'totalGames'  => 0,
                    'wins'        => 0,
                    'losses'      => 0,
                    'draws'       => 0,
                    'rating'      => 1200,
                    'rapidRating' => 1200,
                    'blitzRating' => 1200,
                    'bulletRating'=> 1200,
                    'rankTitle'   => $this->getRankTitle(1200),
                    'ratingTrend' => 0,
                    'winrate'     => 0
                ];
            } else {
                // Hitung statistik dari data yang ada
                $wins  = (int)$userRating->wins;
                $losses = (int)$userRating->losses;
                $draws = (int)$userRating->draws;
                $total = $wins + $losses + $draws;
                
                $ratingTrend = ($wins * 10) - ($losses * 8);
                $currentRating = (int)$userRating->standard_rating;
                $winrate = ($total > 0) ? round(($wins / $total) * 100, 1) : 0;
                
                $result = [
                    'totalGames'  => $total,
                    'wins'        => $wins,
                    'losses'      => $losses,
                    'draws'       => $draws,
                    'rating'      => $currentRating,
                    'rapidRating' => (int)$userRating->rapid_rating,
                    'blitzRating' => (int)$userRating->blitz_rating,
                    'bulletRating'=> (int)$userRating->bullet_rating,
                    'rankTitle'   => $this->getRankTitle($currentRating),
                    'ratingTrend' => $ratingTrend,
                    'winrate'     => $winrate
                ];
            }
            
            $this->cache->save($cacheKey, $result, self::CACHE_TIME);
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', "Error in getUserStats: " . $e->getMessage());
            
            return [
                'totalGames'  => 0,
                'wins'        => 0,
                'losses'      => 0,
                'draws'       => 0,
                'rating'      => 1200,
                'rapidRating' => 1200,
                'blitzRating' => 1200,
                'bulletRating'=> 1200,
                'rankTitle'   => 'Novice',
                'ratingTrend' => 0,
                'winrate'     => 0
            ];
        }
    }
    
    /**
     * Helper untuk menentukan Title berdasarkan Rating
     */
    private function getRankTitle(int $rating): string
    {
        if ($rating >= 2500) return 'Grandmaster';
        if ($rating >= 2300) return 'Master';
        if ($rating >= 2000) return 'Expert';
        if ($rating >= 1500) return 'Intermediate';
        return 'Novice';
    }
    
    /**
     * Helper untuk mengecek apakah table punya FULLTEXT index
     */
    private function hasFulltextIndex(): bool
    {
        try {
            $query = $this->db->query("
                SHOW INDEX FROM users WHERE Index_type = 'FULLTEXT'
            ");
            return $query->getNumRows() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Method untuk menghapus cache tertentu
     */
    public function clearUserCache($userId = null)
    {
        if ($userId) {
            $this->cache->delete("user_full_detail_{$userId}");
            $this->cache->delete("user_stats_{$userId}");
            $this->cache->delete("match_history_{$userId}");
            $this->cache->delete("transactions_{$userId}");
        }
        // Clear cache general
        $this->cache->delete("user_statistics");
        $this->cache->deleteMatching('users_paginated_*');
        $this->cache->deleteMatching('count_users_*');
    }
    
    /**
     * Batch processing untuk operasi massal
     */
    public function processUsersInBatch($callback, $batchSize = 1000)
    {
        $offset = 0;
        
        while (true) {
            $users = $this->select('id, full_name, email, kyc_status')
                         ->orderBy('id')
                         ->limit($batchSize, $offset)
                         ->findAll();
            
            if (empty($users)) {
                break;
            }
            
            foreach ($users as $user) {
                call_user_func($callback, $user);
            }
            
            $offset += $batchSize;
            
            unset($users);
        }
    }
    
    /**
     * Safe array access dengan default value
     * Untuk mencegah "Undefined array key" error
     */
    public static function safeArrayGet($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}