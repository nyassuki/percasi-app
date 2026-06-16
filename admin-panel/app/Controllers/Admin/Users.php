<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class Users extends BaseController 
{
    public function index() 
    {
        $model = new UserModel();
        
        // Get pagination parameters
        $page = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage = 10;
        
        // Get search and filter parameters
        $search = $this->request->getGet('search');
        $userStatus = $this->request->getGet('user_status') ?? 'all';
        $kycStatus = $this->request->getGet('kyc_status') ?? 'all';
        
        // Get paginated data
        $users = $model->getAllUsersPaginated($perPage, $page, $search, $userStatus, $kycStatus);
        $totalUsers = $model->countAllUsers($search, $userStatus, $kycStatus);
        
        // Calculate pagination info
        $totalPages = ceil($totalUsers / $perPage);
        
        $data = [
            'title' => 'Database User',
            'users' => $users,
            'stats' => $model->getUserStatistics(),
            'pager' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'totalUsers' => $totalUsers,
                'hasPrevious' => $page > 1,
                'hasNext' => $page < $totalPages,
                'offset' => ($page - 1) * $perPage + 1,
                'limit' => min($page * $perPage, $totalUsers)
            ],
            'search' => $search,
            'userStatus' => $userStatus,
            'kycStatus' => $kycStatus
        ];
        
        return view('admin/users/list', $data);
    }
    
    public function searchByName() 
    {
        $name = $this->request->getPost('name');
        $model = new UserModel();
        
        // Get pagination parameters
        $page = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage = 10;
        
        // Use paginated method instead of getAllUsersByNameOrUserName
        $users = $model->getAllUsersPaginated($perPage, $page, $name, 'all', 'all');
        $totalUsers = $model->countAllUsers($name, 'all', 'all');
        
        // Calculate pagination info
        $totalPages = ceil($totalUsers / $perPage);
        
        $data = [
            'title' => 'Database User (Search: ' . esc($name) . ')',
            'users' => $users,
            'stats' => $model->getUserStatistics(),
            'pager' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'totalUsers' => $totalUsers,
                'hasPrevious' => $page > 1,
                'hasNext' => $page < $totalPages,
                'offset' => ($page - 1) * $perPage + 1,
                'limit' => min($page * $perPage, $totalUsers)
            ],
            'search' => $name,
            'userStatus' => 'all',
            'kycStatus' => 'all'
        ];
        
        return view('admin/users/list', $data);
    }
    
    public function searchByPendingKYC() 
    {
        $model = new UserModel();
        
        // Get pagination parameters
        $page = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage = 10;
        
        // Get search parameter (if any)
        $search = $this->request->getGet('search');
        
        // Use paginated method for pending KYC
        $users = $model->getAllPendingKYCWithPagination($perPage, $page, $search);
        $totalUsers = $model->countPendingKYC($search);
        
        // Calculate pagination info
        $totalPages = ceil($totalUsers / $perPage);
        
        $data = [
            'title' => 'Database User (PENDING KYC)',
            'users' => $users,
            'stats' => $model->getUserStatistics(),
            'pager' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'totalUsers' => $totalUsers,
                'hasPrevious' => $page > 1,
                'hasNext' => $page < $totalPages,
                'offset' => ($page - 1) * $perPage + 1,
                'limit' => min($page * $perPage, $totalUsers)
            ],
            'search' => $search,
            'userStatus' => 'all',
            'kycStatus' => 'pending'
        ];
        
        return view('admin/users/list', $data);
    }
    
    public function detail($id) 
    {
        $model = new UserModel();
        $stats = $model->getUserStats($id);
        
        $data = [
            'title'   => 'Manajemen Atlet',
            'u'       => $model->getFullDetail($id),
            'matches' => $model->getMatchHistory($id),
            'va' => $model->getVA($id),
            'transactions' => $model->getTransactionHistory($id),
            'stats' => $stats
        ];
        
        return view('admin/users/detail', $data);
    }
    
    // Update Status & Open Match
    public function updateStatus($id) 
    {
        $model = new UserModel();
        $model->update($id, [
            'user_status' => $this->request->getPost('user_status'),
            'open_match'  => $this->request->getPost('open_match')
        ]);
        
        return redirect()->back()->with('success', 'Status user berhasil diperbarui');
    }
    
    // Approval KYC
    public function approveKyc($id) 
    {
        $status = $this->request->getPost('status'); // verified / rejected
        $model = new UserModel();
        $now = Time::now('Asia/Jakarta');
        
        $model->update($id, [
            'kyc_status' => $status,
            'kyc_rejection_reason' => $status == 'rejected' ? $this->request->getPost('reason') : null,
            'kyc_verified_at' => $now
        ]);
        
        return redirect()->back()->with('success', 'Status KYC diperbarui');
    }
    
    /**
     * AJAX endpoint untuk mendapatkan user data (optional)
     */
    public function getUsersAjax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Access denied'
            ]);
        }
        
        $model = new UserModel();
        
        // Get parameters
        $page = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage = $this->request->getGet('per_page') ?: 10;
        $search = $this->request->getGet('search');
        $userStatus = $this->request->getGet('user_status') ?? 'all';
        $kycStatus = $this->request->getGet('kyc_status') ?? 'all';
        
        // Get data
        $users = $model->getAllUsersPaginated($perPage, $page, $search, $userStatus, $kycStatus);
        $totalUsers = $model->countAllUsers($search, $userStatus, $kycStatus);
        $totalPages = ceil($totalUsers / $perPage);
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'users' => $users,
                'stats' => $model->getUserStatistics()
            ],
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalUsers' => $totalUsers,
                'perPage' => $perPage
            ]
        ]);
    }
    
    /**
     * Alternative: Keep original method for backward compatibility
     */
    public function getAllUsers()
    {
        $model = new UserModel();
        
        $data = [
            'title' => 'Database User (All - No Pagination)',
            'users' => $model->getAllUsers(),
            'stats' => $model->getUserStatistics()
        ];
        
        return view('admin/users/list', $data);
    }
}