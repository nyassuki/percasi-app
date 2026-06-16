<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterCountryModel extends Model
{
    protected $table = 'master_countries';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'countryCode', 'countryName', 'currencyCode', 'fipsCode',
        'isoNumeric', 'north', 'south', 'east', 'west', 'capital',
        'continentName', 'continent', 'languages', 'isoAlpha3',
        'geonameId', 'telephonePrefix', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'countryCode' => 'required|max_length[2]|is_unique[master_countries.countryCode]',
        'countryName' => 'required|max_length[100]',
        'telephonePrefix' => 'required|max_length[10]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Method untuk mendapatkan semua negara
    public function getAllCountries($activeOnly = true)
    {
        return $this->orderBy('countryName', 'ASC')->findAll();
    }
    
    // Method untuk mencari negara berdasarkan kode
    public function findByCode($countryCode)
    {
        return $this->where('countryCode', $countryCode)->first();
    }
    
    // Method untuk mendapatkan negara berdasarkan nama
    public function searchByName($name)
    {
        return $this->like('countryName', $name)
                    ->orderBy('countryName', 'ASC')
                    ->findAll();
    }
}