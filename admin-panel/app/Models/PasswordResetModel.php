<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'token', 'expires_at',
        'created_at', 'is_used'
    ];
    
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'token' => 'required|max_length[64]',
        'expires_at' => 'required|valid_date',
        'is_used' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk membuat token reset
    public function createResetToken($userId, $token, $expiresInHours = 1)
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInHours} hours"));
        
        // Invalidate existing tokens for this user
        $this->where('user_id', $userId)
             ->where('is_used', 0)
             ->where('expires_at >', date('Y-m-d H:i:s'))
             ->set(['is_used' => 1])
             ->update();
        
        $data = [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt,
            'is_used' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }
    
    // Method untuk validasi token
    public function validateToken($token)
    {
        return $this->where('token', $token)
                    ->where('is_used', 0)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }
    
    // Method untuk mark token as used
    public function markTokenAsUsed($tokenId)
    {
        return $this->update($tokenId, ['is_used' => 1]);
    }
    
    // Method untuk menghapus token expired
    public function cleanupExpiredTokens()
    {
        return $this->where('expires_at <=', date('Y-m-d H:i:s'))
                    ->orWhere('is_used', 1)
                    ->delete();
    }
}