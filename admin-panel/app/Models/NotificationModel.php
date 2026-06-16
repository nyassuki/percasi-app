<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 'title', 'message', 'type', 'is_read',
        'action_url', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'title' => 'permit_empty|max_length[100]',
        'type' => 'required|in_list[info,success,warning,danger]',
        'is_read' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk notifikasi user yang belum dibaca
    public function getUnreadNotifications($userId, $limit = 20)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    // Method untuk semua notifikasi user
    public function getUserNotifications($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    // Method untuk mark as read
    public function markAsRead($notificationId)
    {
        return $this->update($notificationId, ['is_read' => 1]);
    }
    
    // Method untuk mark all as read
    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->set(['is_read' => 1])
                    ->update();
    }
    
    // Method untuk membuat notifikasi baru
    public function createNotification($userId, $title, $message, $type = 'info', $actionUrl = null)
    {
        return $this->insert([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'is_read' => 0
        ]);
    }
}