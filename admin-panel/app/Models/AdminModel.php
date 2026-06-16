<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table            = 'admins';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Sesuaikan dengan struktur tabel Anda
    protected $allowedFields    = [
        'full_name', 
        'email', 
        'password_hash', 
        'is_active', 
        'two_factor_secret'
    ];

    protected $useTimestamps = true; // Karena ada created_at & updated_at
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
