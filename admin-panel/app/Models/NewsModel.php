<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'title', 'news_image_url', 'summary', 'content',
        'is_active', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'summary' => 'required',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk berita aktif
    public function getActiveNews($limit = 10)
    {
        return $this->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    // Method untuk berita terbaru dengan pagination
    public function getLatestNews($page = 1, $perPage = 10)
    {
        return $this->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage, 'default', $page);
    }
    
    // Method untuk search berita
    public function searchNews($keyword, $limit = 10)
    {
        return $this->like('title', $keyword)
                    ->orLike('summary', $keyword)
                    ->orLike('content', $keyword)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}