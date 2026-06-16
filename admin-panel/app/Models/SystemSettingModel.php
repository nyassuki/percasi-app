<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'setting_key';
    protected $useAutoIncrement = false;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'setting_key', 'app_name', 'description', 'min_deposit',
        'max_deposit', 'min_withdrawal', 'max_withdrawal',
        'rapid_match_time', 'blitz_match_time', 'fide_clasic_time',
        'is_active', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'setting_key' => 'required|max_length[50]',
        'min_deposit' => 'required|decimal|greater_than_equal_to[0]',
        'max_deposit' => 'required|decimal|greater_than[min_deposit]',
        'min_withdrawal' => 'required|decimal|greater_than_equal_to[0]',
        'max_withdrawal' => 'required|decimal|greater_than[min_withdrawal]',
        'rapid_match_time' => 'permit_empty|integer|greater_than[0]',
        'blitz_match_time' => 'permit_empty|integer|greater_than[0]',
        'fide_clasic_time' => 'permit_empty|integer|greater_than[0]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk mendapatkan semua settings aktif
    public function getActiveSettings()
    {
        return $this->where('is_active', 1)->findAll();
    }
    
    // Method untuk mendapatkan setting berdasarkan key
    public function getSetting($key)
    {
        return $this->find($key);
    }
    
    // Method untuk mendapatkan multiple settings
    public function getSettings(array $keys)
    {
        return $this->whereIn('setting_key', $keys)->findAll();
    }
    
    // Method untuk update setting
    public function updateSetting($key, $data)
    {
        return $this->update($key, $data);
    }
    
    // Method untuk mendapatkan waktu match berdasarkan type
    public function getMatchTime($type)
    {
        $setting = $this->where('setting_key', $type . '_match_time')
                       ->where('is_active', 1)
                       ->first();
        
        return $setting ? (int)$setting->value : 600; // default 600 detik
    }
}