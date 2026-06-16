<?php

namespace App\Models;

use CodeIgniter\Model;

class BannerModel extends Model
{
    protected $table = 'banners';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'title', 'image_url', 'target_url', 'sort_order',
        'is_active', 'start_date', 'expiry_date',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'image_url' => 'required|max_length[255]',
        'sort_order' => 'permit_empty|integer',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk banner aktif yang sedang berjalan
    public function getActiveBanners()
    {
        $currentDate = date('Y-m-d');
        
        return $this->where('is_active', 1)
                    ->groupStart()
                        ->where('start_date IS NULL')
                        ->orWhere('start_date <=', $currentDate)
                    ->groupEnd()
                    ->groupStart()
                        ->where('expiry_date IS NULL')
                        ->orWhere('expiry_date >=', $currentDate)
                    ->groupEnd()
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    // Method untuk banner berdasarkan sort order
    public function getBannersByOrder($limit = 5)
    {
        return $this->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}