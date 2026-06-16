<?php

namespace App\Models;

use CodeIgniter\Model;

class BonusDailyUserModel extends Model
{
    protected $table = 'bonus_daily_user';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'bonus_master_id', 'bonus_date',
        'total_point', 'total_event',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'bonus_master_id' => 'required|integer|is_not_unique[bonus_master.id]',
        'bonus_date' => 'required|valid_date',
        'total_point' => 'required|integer|greater_than_equal_to[0]',
        'total_event' => 'required|integer|greater_than_equal_to[0]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk mendapatkan bonus harian user
    public function getUserDailyBonus($userId, $bonusMasterId, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        return $this->where('user_id', $userId)
                    ->where('bonus_master_id', $bonusMasterId)
                    ->where('bonus_date', $date)
                    ->first();
    }
    
    // Method untuk menambahkan event bonus
    public function addBonusEvent($userId, $bonusMasterId, $pointValue)
    {
        $date = date('Y-m-d');
        $dailyBonus = $this->getUserDailyBonus($userId, $bonusMasterId, $date);
        
        if ($dailyBonus) {
            // Update existing record
            $newTotalPoint = $dailyBonus->total_point + $pointValue;
            $newTotalEvent = $dailyBonus->total_event + 1;
            
            return $this->update($dailyBonus->id, [
                'total_point' => $newTotalPoint,
                'total_event' => $newTotalEvent
            ]);
        } else {
            // Create new record
            return $this->insert([
                'user_id' => $userId,
                'bonus_master_id' => $bonusMasterId,
                'bonus_date' => $date,
                'total_point' => $pointValue,
                'total_event' => 1
            ]);
        }
    }
}