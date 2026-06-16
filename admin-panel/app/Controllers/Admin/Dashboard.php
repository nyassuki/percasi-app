<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\RawSql;

class Dashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // 1. Ambil Statistik Utama (Card Stats)
        $data['total_users']   = $this->db->table('users')->where('user_status', 'ACT')->countAllResults();
        $data['active_tourney'] = $this->db->table('tournaments')->where('status', 'active')->countAllResults();
        $data['pending_kyc']   = $this->db->table('users')->where('kyc_status', 'pending')->countAllResults();
        
        // Ambil Total Saldo Platform dari tabel Wallets
        $queryWallet = $this->db->table('wallets')->selectSum('balance')->get()->getRow();
        $data['platform_cash'] = $queryWallet->balance ?? 0;

        // 2. Data Grafik Transaksi (7 Hari Terakhir) - Arus Masuk
        $data['chart_finance'] = $this->db->query("
            SELECT DATE(created_at) as tgl, SUM(amount) as total 
            FROM transactions 
            WHERE flow = 'in' AND status = 'success' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at) 
            ORDER BY tgl ASC
        ")->getResultArray();

        // 3. Data Grafik Lingkaran (Status Member)
        $data['chart_user_status'] = $this->db->table('users')
            ->select('user_status, COUNT(*) as jumlah')
            ->groupBy('user_status')
            ->get()->getResultArray();

        // 4. Riwayat Pertandingan (Mengambil dari VIEW yang Anda buat)
        $data['recent_matches'] = $this->db->table('view_match_history')
            ->limit(5)
            ->get()->getResultArray();

        return view('admin/dashboard', $data);
    }
}