<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSessionModel extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'refresh_token_hash', 'device_name',
        'device_id', 'platform', 'ip_address', 'is_valid',
        'expires_at', 'last_active', 'created_at'
    ];
    
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'refresh_token_hash' => 'required|max_length[255]',
        'platform' => 'required|in_list[android,ios,web]',
        'is_valid' => 'permit_empty|in_list[0,1]',
        'expires_at' => 'required|valid_date',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk session user yang valid
    public function getUserValidSessions($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_valid', 1)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    // Method untuk membuat session baru
    public function createSession($userId, $refreshTokenHash, $platform, $deviceName = null, $deviceId = null, $ipAddress = null, $expiresInDays = 7)
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days"));
        
        $data = [
            'user_id' => $userId,
            'refresh_token_hash' => $refreshTokenHash,
            'platform' => $platform,
            'device_name' => $deviceName,
            'device_id' => $deviceId,
            'ip_address' => $ipAddress,
            'is_valid' => 1,
            'expires_at' => $expiresAt,
            'last_active' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }
    
    // Method untuk invalidate session
    public function invalidateSession($sessionId)
    {
        return $this->update($sessionId, ['is_valid' => 0]);
    }
    
    // Method untuk invalidate semua session user
    public function invalidateAllUserSessions($userId, $exceptSessionId = null)
    {
        $builder = $this->where('user_id', $userId)->where('is_valid', 1);
        
        if ($exceptSessionId) {
            $builder->where('id !=', $exceptSessionId);
        }
        
        return $builder->set(['is_valid' => 0])->update();
    }
    
    // Method untuk update last active
    public function updateLastActive($sessionId)
    {
        return $this->update($sessionId, ['last_active' => date('Y-m-d H:i:s')]);
    }
}