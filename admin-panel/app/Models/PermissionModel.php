<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'name', 'slug', 'group_name', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'slug' => 'required|max_length[100]|is_unique[permissions.slug]',
        'group_name' => 'permit_empty|max_length[50]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk mendapatkan permission berdasarkan slug
    public function findBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }
    
    // Method untuk permissions berdasarkan group
    public function getPermissionsByGroup($groupName)
    {
        return $this->where('group_name', $groupName)->findAll();
    }
    
    // Method untuk mendapatkan semua group permissions
    public function getPermissionGroups()
    {
        return $this->distinct()
                    ->select('group_name')
                    ->where('group_name IS NOT NULL')
                    ->orderBy('group_name', 'ASC')
                    ->findAll();
    }
}