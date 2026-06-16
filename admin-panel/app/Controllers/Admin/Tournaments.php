<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TournamentModel;

class Tournaments extends BaseController
{
    protected $tournamentModel;

    public function __construct()
    {
        $this->tournamentModel = new TournamentModel();
    }

    /**
     * Tampilkan Daftar Turnamen dengan Pagination & Search
     */
    public function index()
    {
        // Get pagination parameters
        $page = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage = 10;
        
        // Get search and filter parameters
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');
        $format = $this->request->getGet('format');
        $timeControl = $this->request->getGet('time_control');
        
        // Get paginated tournaments
        $tournaments = $this->tournamentModel->getAllTournamentsPaginated(
            $perPage, 
            $page, 
            $search, 
            $status, 
            $format, 
            $timeControl
        );
        
        // Get total count for pagination
        $totalTournaments = $this->tournamentModel->countTournaments($search, $status, $format, $timeControl);
        $totalPages = ceil($totalTournaments / $perPage);
        
        // Get tournament statistics
        $stats = $this->tournamentModel->getTournamentStatistics();
        
        // Get active tournaments count
        $activeCount = $this->tournamentModel->whereIn('status', ['registration', 'active'])->countAllResults();
        
        $data = [
            'title'        => 'Manajemen Turnamen Catur',
            'tournaments'  => $tournaments,
            'active_count' => $activeCount,
            'stats'        => $stats,
            'pager' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'totalTournaments' => $totalTournaments,
                'hasPrevious' => $page > 1,
                'hasNext' => $page < $totalPages,
                'offset' => ($page - 1) * $perPage + 1,
                'limit' => min($page * $perPage, $totalTournaments)
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'format' => $format,
                'timeControl' => $timeControl
            ]
        ];

        return view('admin/tournaments/index', $data);
    }

    /**
     * Form Tambah Turnamen
     */
    public function create()
    {
        return view('admin/tournaments/form', [
            'title' => 'Buat Turnamen Baru'
        ]);
    }

    /**
     * Simpan Turnamen Baru
     */
    public function store()
    {
        // Ambil semua data post
        $data = $this->request->getPost();
        
        $title = $data['title'];
        $description = $data['description'];

        // created_by biasanya diambil dari session user yang login
        $data['created_by'] = session()->get('user_id') ?? 1;

        // Model->save() otomatis menjalankan validationRules yang ada di Model
        if (!$this->tournamentModel->save($data)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', $this->tournamentModel->errors());
        }
        
        // Send broadcast notification
        send_broadcast($title, $description, 'info');
        
        return redirect()->to('/admin/tournaments')->with('success', 'Turnamen berhasil dibuat.');
    }

    /**
     * Form Edit Turnamen
     */
    public function edit($id)
    {
        $tournament = $this->tournamentModel->find($id);

        if (!$tournament) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Turnamen ID $id tidak ditemukan.");
        }

        return view('admin/tournaments/form', [
            'title'      => 'Edit Turnamen: ' . $tournament->title,
            'tournament' => $tournament // Ini berupa Object
        ]);
    }

    /**
     * Update Data Turnamen
     */
    public function update($id)
    {
        $n_data = $this->request->getPost();
        $csrf_test_name = $n_data['csrf_test_name'];
        $title = $n_data['title'];
        $description = $n_data['description'];
        $registration_close = $n_data['registration_close'];
        $start_time = $n_data['start_time'];
        $end_time = $n_data['end_time'];
        
        $p_data = [
            'csrf_test_name' => $csrf_test_name,
            'title' => $title,
            'description' => $description,
            'registration_close' => $registration_close,
            'start_time' => $start_time,
            'end_time' => $end_time,
        ];

        $participant = $this->tournamentModel->getParticipantCount($id);
        
        if ($participant == 0) {
            $data = $n_data;
        } else {
            $data = $p_data;
        }
        
        if (!$this->tournamentModel->update($id, $data)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', $this->tournamentModel->errors());
        }

        return redirect()->to('/admin/tournaments')->with('success', 'Data turnamen berhasil diperbarui.');
    }

    /**
     * Update Status Turnamen (Menggunakan custom method di Model)
     */
    public function updateStatus($id)
    {
        $newStatus = $this->request->getPost('status');
        
        if ($this->tournamentModel->updateStatus($id, $newStatus)) {
            return redirect()->back()->with('success', 'Status turnamen diperbarui menjadi ' . $newStatus);
        }

        return redirect()->back()->with('error', 'Gagal memperbarui status.');
    }

    /**
     * Hapus Turnamen
     */
    public function delete($id)
    {
        $participant = $this->tournamentModel->getParticipantCount($id);
        
        if ($participant == 0) {
            if ($this->tournamentModel->delete($id)) {
                return redirect()->to('/admin/tournaments')->with('success', 'Turnamen berhasil dihapus.');
            }
        } else {
            return redirect()->back()->with('error', 'Tournament dengan peserta tidak bisa dihapus.');
        }
        
        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }

    /**
     * Detail Turnamen
     */
    public function view($id)
    {
        $tournament = $this->tournamentModel->find($id);

        if (!$tournament) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get tournament with participant count
        $tournamentWithStats = $this->tournamentModel->getTournamentById($id);
        
        $data = [
            'title'        => 'Detail Turnamen',
            'tournament'   => $tournamentWithStats,
            'participants' => $this->tournamentModel->getParticipants($id)
        ];

        return view('admin/tournaments/view', $data);
    }
    
    /**
     * AJAX endpoint untuk pencarian tournament
     */
    public function search()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Access denied'
            ]);
        }
        
        $search = $this->request->getGet('search');
        $limit = $this->request->getGet('limit') ?: 10;
        
        $tournaments = $this->tournamentModel->searchTournaments($search, $limit);
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $tournaments
        ]);
    }
    
    /**
     * Get upcoming tournaments for dashboard
     */
    public function upcoming()
    {
        $limit = $this->request->getGet('limit') ?: 5;
        
        $upcoming = $this->tournamentModel->getUpcomingTournaments($limit);
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $upcoming
        ]);
    }
    
    /**
     * Export tournament data (optional)
     */
    public function export()
    {
        $type = $this->request->getGet('type') ?: 'all';
        
        switch ($type) {
            case 'active':
                $tournaments = $this->tournamentModel->getActiveTournaments();
                break;
            case 'upcoming':
                $tournaments = $this->tournamentModel->getUpcomingTournaments();
                break;
            default:
                $tournaments = $this->tournamentModel->getAllTournaments();
        }
        
        // Simple CSV export example
        $csv = "ID,Title,Format,Status,Start Time,End Time,Participants\n";
        
        foreach ($tournaments as $tournament) {
            $csv .= sprintf(
                '%d,"%s","%s","%s","%s","%s",%d' . "\n",
                $tournament->id,
                $tournament->title,
                $tournament->format,
                $tournament->status,
                $tournament->start_time,
                $tournament->end_time,
                $tournament->total_participants ?? 0
            );
        }
        
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="tournaments_' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }
}