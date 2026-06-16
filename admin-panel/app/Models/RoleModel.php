<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'name', 'slug', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'name' => 'required|max_length[50]',
        'slug' => 'required|max_length[50]|is_unique[roles.slug]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk mendapatkan role berdasarkan slug
    public function findBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }
    
    // Method untuk mendapatkan semua roles dengan permissions
    public function getRolesWithPermissions()
    {
        $roles = $this->findAll();
        
        foreach ($roles as $role) {
            $role->permissions = $this->db->table('role_has_permissions')
                ->join('permissions', 'permissions.id = role_has_permissions.permission_id')
                ->where('role_id', $role->id)
                ->get()
                ->getResult();
        }
        
        return $roles;
    }
}