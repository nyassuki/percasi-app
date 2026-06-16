<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MatchModel;
use App\Models\TournamentModel;
use App\Models\UserModel;

class MatchController extends BaseController
{
    protected $matchModel;
    protected $tournamentModel;
    protected $userModel;

    public function __construct()
    {
        $this->matchModel = new MatchModel();
        $this->tournamentModel = new TournamentModel();
        $this->userModel = new UserModel();
        helper('form');
    }

    /**
     * Menampilkan semua match dengan paginasi
     */
    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 20;
        
        // Filter dari request
        $filters = [
            'status' => $this->request->getGet('status'),
            'result' => $this->request->getGet('result'),
            'tournament_id' => $this->request->getGet('tournament_id'),
            'player_id' => $this->request->getGet('player_id'),
            'search' => $this->request->getGet('search'),
        ];
        
        // Hapus filter yang kosong
        $filters = array_filter($filters, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        $matches = $this->matchModel->getAllMatches($page, $perPage, $filters);
        $pager = $this->matchModel->getPager();
        
        // Data untuk filter dropdown
        $tournaments = $this->tournamentModel->findAll();
        $players = $this->userModel->findAll();
        
        $data = [
            'title' => 'All Matches',
            'matches' => $matches,
            'pager' => $pager,
            'filters' => $filters,
            'tournaments' => $tournaments,
            'players' => $players,
            'perPage' => $perPage,
        ];
        

         
        return view('admin/matches/index', $data);
    }

    /**
     * Menampilkan detail match
     */
    public function show($id)
    {
        $match = $this->matchModel->find($id);
        
        if (!$match) {
            return redirect()->back()->with('error', 'Match not found');
        }
        
        // Ambil data player dan tournament
        $whitePlayer = $this->userModel->find($match->white_player_id);
        $blackPlayer = $this->userModel->find($match->black_player_id);
        $tournament = $match->tournament_id ? $this->tournamentModel->find($match->tournament_id) : null;
        
       
        $data = [
            'title' => 'Match Details',
            'match' => $match,
            'whitePlayer' => $whitePlayer,
            'blackPlayer' => $blackPlayer,
            'tournament' => $tournament,
        ];
        
        return view('admin/matches/show', $data);
    }

    /**
     * Menampilkan match berdasarkan tournament dengan paginasi
     */
    public function tournamentMatches($tournamentId)
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 15;
        
        // Filter dari request
        $filters = [
            'status' => $this->request->getGet('status'),
            'result' => $this->request->getGet('result'),
            'round' => $this->request->getGet('round'),
            'search' => $this->request->getGet('search'),
        ];
        
        // Hapus filter yang kosong
        $filters = array_filter($filters, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        $tournament = $this->tournamentModel->find($tournamentId);
        
        if (!$tournament) {
            return redirect()->back()->with('error', 'Tournament not found');
        }
        
        $matches = $this->matchModel->getTournamentMatches($tournamentId, $page, $perPage, $filters);
        $pager = $this->matchModel->getPager();
        
        // Hitung statistik
        $totalMatches = count($matches);
        $completedMatches = array_filter($matches, function($match) {
            return $match->status === 'completed';
        });
        
        $data = [
            'title' => "Matches - {$tournament->name}",
            'matches' => $matches,
            'tournament' => $tournament,
            'pager' => $pager,
            'filters' => $filters,
            'perPage' => $perPage,
            'stats' => [
                'total' => $totalMatches,
                'completed' => count($completedMatches),
            ],
        ];
        
        return view('admin/matches/tournament_matches', $data);
    }

    /**
     * Menampilkan match history user dengan paginasi
     */
    public function userMatches($userId = null)
    {
        // Jika tidak ada userId, gunakan user yang sedang login
        if (!$userId) {
            $userId = session()->get('user_id');
        }
        
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }
        
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 20;
        
        // Filter dari request
        $filters = [
            'result' => $this->request->getGet('result'),
            'tournament_id' => $this->request->getGet('tournament_id'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
        ];
        
        // Hapus filter yang kosong
        $filters = array_filter($filters, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        $matches = $this->matchModel->getUserMatches($userId, $page, $perPage, $filters);
        $pager = $this->matchModel->getPager();
        
        // Hitung statistik user
        $stats = $this->calculateUserStats($userId);
        
        // Data untuk filter dropdown
        $tournaments = $this->tournamentModel->findAll();
        
        $data = [
            'title' => "Match History - {$user->username}",
            'matches' => $matches,
            'user' => $user,
            'pager' => $pager,
            'filters' => $filters,
            'stats' => $stats,
            'tournaments' => $tournaments,
            'perPage' => $perPage,
        ];
        
        return view('admin/matches/user_matches', $data);
    }

    /**
     * Menampilkan match yang sedang berlangsung dengan paginasi
     */
    public function ongoingMatches()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 10;
        $search = $this->request->getGet('search');
        
        $matches = $this->matchModel->getOngoingMatches($page, $perPage, $search);
        $pager = $this->matchModel->getPager();
        
        // Ambil data player untuk setiap match
        foreach ($matches as $match) {
            $match->white_player = $this->userModel->find($match->white_player_id);
            $match->black_player = $this->userModel->find($match->black_player_id);
            
            if ($match->tournament_id) {
                $match->tournament = $this->tournamentModel->find($match->tournament_id);
            }
        }
        
        $data = [
            'title' => 'Ongoing Matches',
            'matches' => $matches,
            'pager' => $pager,
            'search' => $search,
            'perPage' => $perPage,
        ];
        
        return view('admin/matches/ongoing_matches', $data);
    }

    /**
     * Update result match (untuk admin)
     */
    public function updateResult($matchId)
    {
        // Cek authorization (contoh sederhana)
        if (!session()->get('is_admin')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        $match = $this->matchModel->find($matchId);
        
        if (!$match) {
            return redirect()->back()->with('error', 'Match not found');
        }
        
        $validation = \Config\Services::validation();
        $validation->setRules([
            'result' => 'required|in_list[1-0,0-1,1/2-1/2,aborted]',
            'win_reason' => 'permit_empty|in_list[checkmate,timeout,resignation,cheat_detected,agreement]',
        ]);
        
        if (!$this->validate($validation->getRules())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }
        
        $result = $this->request->getPost('result');
        $winReason = $this->request->getPost('win_reason');
        
        $this->matchModel->updateResult($matchId, $result, $winReason);
        
        return redirect()->to("/matches/{$matchId}")
            ->with('success', 'Match result updated successfully');
    }

    /**
     * Menampilkan form untuk membuat match baru
     */
    public function create()
    {
        // Cek authorization
        if (!session()->get('is_admin')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        $players = $this->userModel->findAll();
        $tournaments = $this->tournamentModel->findAll();
        
        $data = [
            'title' => 'Create New Match',
            'players' => $players,
            'tournaments' => $tournaments,
        ];
        
        return view('admin/matches/create', $data);
    }

    /**
     * Menyimpan match baru
     */
    public function store()
    {
        // Cek authorization
        if (!session()->get('is_admin')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        $validation = \Config\Services::validation();
        $validation->setRules([
            'white_player_id' => 'required|integer',
            'black_player_id' => 'required|integer|different[white_player_id]',
            'tournament_id' => 'permit_empty|integer',
            'round_number' => 'permit_empty|integer',
            'status' => 'required|in_list[pending_start,ongoing,completed,aborted]',
        ]);
        
        if (!$this->validate($validation->getRules())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }
        
        $data = [
            'white_player_id' => $this->request->getPost('white_player_id'),
            'black_player_id' => $this->request->getPost('black_player_id'),
            'tournament_id' => $this->request->getPost('tournament_id') ?: null,
            'round_number' => $this->request->getPost('round_number') ?: 1,
            'status' => $this->request->getPost('status'),
            'start_time' => $this->request->getPost('start_time') ?: date('Y-m-d H:i:s'),
        ];
        
        // Jika status ongoing, set result ke ongoing
        if ($data['status'] === 'ongoing') {
            $data['result'] = 'ongoing';
        }
        
        $this->matchModel->insert($data);
        
        return redirect()->to('/admin/matches')
            ->with('success', 'Match created successfully');
    }

    /**
     * Menghitung statistik user
     */
    private function calculateUserStats($userId)
    {
        // Ambil semua match user yang completed
        $matches = $this->matchModel
            ->where('status', 'completed')
            ->groupStart()
                ->where('white_player_id', $userId)
                ->orWhere('black_player_id', $userId)
            ->groupEnd()
            ->findAll();
        
        $stats = [
            'total' => 0,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
            'win_rate' => 0,
        ];
        
        foreach ($matches as $match) {
            $stats['total']++;
            
            if ($match->result === '1/2-1/2') {
                $stats['draws']++;
            } elseif (($match->white_player_id == $userId && $match->result === '1-0') ||
                     ($match->black_player_id == $userId && $match->result === '0-1')) {
                $stats['wins']++;
            } else {
                $stats['losses']++;
            }
        }
        
        if ($stats['total'] > 0) {
            $stats['win_rate'] = round(($stats['wins'] / $stats['total']) * 100, 2);
        }
        
        return $stats;
    }

    /**
     * Export match data ke CSV
     */
    public function export($type = 'all')
    {
        // Cek authorization
        if (!session()->get('is_admin')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        $filename = "matches_export_" . date('Y-m-d_H-i-s') . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, [
            'ID', 'Tournament', 'White Player', 'Black Player', 'Result',
            'Win Reason', 'Status', 'Round', 'Start Time', 'End Time',
            'Created At'
        ]);
        
        // Query data berdasarkan type
        $query = $this->matchModel;
        
        if ($type === 'completed') {
            $query->where('status', 'completed');
        } elseif ($type === 'ongoing') {
            $query->where('status', 'ongoing');
        }
        
        $matches = $query->orderBy('created_at', 'DESC')->findAll();
        
        // Data rows
        foreach ($matches as $match) {
            // Ambil data relasional
            $whitePlayer = $this->userModel->find($match->white_player_id);
            $blackPlayer = $this->userModel->find($match->black_player_id);
            $tournament = $match->tournament_id ? $this->tournamentModel->find($match->tournament_id) : null;
            
            fputcsv($output, [
                $match->id,
                $tournament ? $tournament->name : 'N/A',
                $whitePlayer ? $whitePlayer->username : 'Unknown',
                $blackPlayer ? $blackPlayer->username : 'Unknown',
                $match->result,
                $match->win_reason ?? 'N/A',
                $match->status,
                $match->round_number ?? 'N/A',
                $match->start_time ?? 'N/A',
                $match->end_time ?? 'N/A',
                $match->created_at
            ]);
        }
        
        fclose($output);
        exit();
    }

    /**
     * Delete match (soft delete jika ada, atau hard delete)
     */
    public function delete($id)
    {
        // Cek authorization
        if (!session()->get('is_admin')) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        $match = $this->matchModel->find($id);
        
        if (!$match) {
            return redirect()->back()->with('error', 'Match not found');
        }
        
        // Cek jika match sudah completed atau ongoing
        if ($match->status === 'ongoing') {
            return redirect()->back()->with('error', 'Cannot delete ongoing match');
        }
        
        $this->matchModel->delete($id);
        
        return redirect()->to('/admin/matches')
            ->with('success', 'Match deleted successfully');
    }
     public function livematch()
    {
        return view('admin/matches/livematch');
    }
    public function mirror($matchId)
    {
        // Kirim ID match ke View agar Socket.io tahu room mana yang harus dimasuki
        $data = [
            'matchId' => $matchId,
            'title'   => "Mirroring Match #$matchId"
        ];

        return view('admin/matches/mirror_view', $data);
    }
}