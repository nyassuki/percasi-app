<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserRatingModel;

class RatingController extends BaseController {
     protected $userRatingModel;
    
    public function __construct()
    {
        $this->userRatingModel = new UserRatingModel();
    }
    
    /**
     * Menampilkan daftar pemain dengan pagination
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $page = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage = 10; // Sesuaikan dengan kebutuhan
        
        // Get data dengan pagination
        $players = $this->userRatingModel->getRatingReportPaginated($search, $perPage, $page);
        $totalPlayers = $this->userRatingModel->countRatingReport($search);
        $totalPages = ceil($totalPlayers / $perPage);
        
        // Validasi halaman
        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        
        return view('admin/ratings/index', [
            'title' => 'Rating Pemain',
            'players' => $players,
            'search' => $search,
            'pager' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'totalPlayers' => $totalPlayers,
                'hasPrevious' => $page > 1,
                'hasNext' => $page < $totalPages,
                'offset' => ($page - 1) * $perPage + 1,
                'limit' => min(($page * $perPage), $totalPlayers)
            ]
        ]);
    }
    
    /**
     * AJAX method untuk pencarian dan pagination
     */
    public function searchPlayers()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/ratings');
        }
        
        $search = $this->request->getGet('search');
        $page = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage = 20;
        
        // Get data dengan pagination
        $players = $this->userRatingModel->getRatingReportPaginated($search, $perPage, $page);
        $totalPlayers = $this->userRatingModel->countRatingReport($search);
        $totalPages = ceil($totalPlayers / $perPage);
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'players' => $players,
                'search' => $search
            ],
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalPlayers' => $totalPlayers,
                'perPage' => $perPage
            ]
        ]);
    }
    
    /**
     * Get top players for dashboard
     */
    public function getTopPlayers()
    {
        $type = $this->request->getGet('type') ?: 'standard_rating';
        $limit = $this->request->getGet('limit') ?: 20;
        
        $topPlayers = $this->userRatingModel->getTopPlayers($type, $limit);
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $topPlayers
        ]);
    }
}