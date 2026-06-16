<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TransactionModel;

class TransactionController extends BaseController
{
    public function index()
    {
        $model = new TransactionModel();

        // Ambil parameter untuk filter dan paging
        $search = $this->request->getGet("search");
        $type = $this->request->getGet("type");
        $status = $this->request->getGet("status");
        $flow = $this->request->getGet("flow");
        $dateFrom = $this->request->getGet("date_from");
        $dateTo = $this->request->getGet("date_to");
        $perPage = $this->request->getGet("per_page") ?? 10;

        // Siapkan parameter untuk model
        $params = [
            "search" => $search,
            "type" => $type,
            "status" => $status,
            "flow" => $flow,
            "date_from" => $dateFrom,
            "date_to" => $dateTo,
            "per_page" => $perPage,
            "page" => $this->request->getGet("page") ?? 1,
        ];
        //print_r($params);
       // die();
        // Ambil data dengan paging
        $result = $model->getAdminReport($params);

        // Ambil filter options untuk dropdown
        $filterOptions = $model->getFilterOptions();

        $data = [
            "title" => "Laporan Transaksi",
            "transactions" => $result["data"],
            "pager" => [
                "total" => $result["total"],
                "per_page" => $result["per_page"],
                "current_page" => $result["current_page"],
                "total_pages" => $result["total_pages"],
            ],
            "search" => $search,
            "type" => $type,
            "status" => $status,
            "flow" => $flow,
            "date_from" => $dateFrom,
            "date_to" => $dateTo,
            "per_page" => $perPage,
            "filter_options" => $filterOptions,
            "params" => $params, // Untuk keperluan pagination links
        ];

        return view("admin/transactions/index", $data);
    }

    // Alternative: Menggunakan paginate bawaan CI4 (lebih sederhana)
    public function indexPaginated()
    {
        $model = new TransactionModel();

        // Ambil parameter
        $search = $this->request->getGet("search");
        $perPage = $this->request->getGet("per_page") ?? 10;

        // Ambil data dengan paginate
        $transactions = $model->getPaginatedAdminReport($search, $perPage);

        // Pager akan otomatis tersedia
        $pager = $model->pager;

        // Ambil filter options
        $filterOptions = $model->getFilterOptions();

        $data = [
            "title" => "Laporan Transaksi",
            "transactions" => $transactions,
            "pager" => $pager,
            "search" => $search,
            "per_page" => $perPage,
            "filter_options" => $filterOptions,
        ];

        return view("admin/transactions/index_paginated", $data);
    }

     // Di dalam class TransactionController, tambahkan method berikut:

/**
 * Show transaction detail
 */
public function detail($id)
    {
        $model = new TransactionModel();
        
        // Get transaction detail
        $transaction = $model->getTransactionDetail($id);
        
        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan');
        }
        
        // Format data untuk view
        $transactionData = [
            'id' => $transaction->id,
            'transaction_id' => 'TRX-' . str_pad($transaction->id, 8, '0', STR_PAD_LEFT),
            'transaction_code' => 'TRX-' . str_pad($transaction->id, 8, '0', STR_PAD_LEFT),
            'user' => [
                'name' => $transaction->user_fullname,
                'username' => $transaction->username,
                'email' => $transaction->email,
                'phone' => $transaction->phone_number
            ],
            'type' => $transaction->type,
            'flow' => $transaction->flow,
            'kode_transaksi' => $transaction->kode_transaksi,
            'amount' => (float)$transaction->amount,
            'amount_formatted' => 'Rp ' . number_format($transaction->amount, 0, ',', '.'),
            'status' => $transaction->status,
            'description' => $transaction->description,
            'created_at' => date('d F Y H:i', strtotime($transaction->created_at)),
            'created_date' => date('d F Y', strtotime($transaction->created_at)),
            'created_time' => date('H:i', strtotime($transaction->created_at)),
            'approved_by' => $transaction->admin_username ? [
                'name' => $transaction->admin_full_name,
                'username' => $transaction->admin_username
            ] : null,
            'current_balance' => $transaction->current_balance_snapshot,
            'tournament' => $transaction->tournament_title,
            'related_user_id' => $transaction->related_user_id,
            'related_user_name' => $transaction->lwt_username,
            'related_user_full_name' => $transaction->lwt_fullname,
            'related_user_phone' => $transaction->lwt_phone_number,
            'related_user_email' => $transaction->lwt_phone_number,
            'va_bank_name' => $transaction->va_bank_name,
            'va_account_number' => $transaction->va_account_number,
            'bank_name' => $transaction->bank_name,
            'account_name' => $transaction->account_holder_name,
            'account_number' => $transaction->account_number
        ];
        
        // Get payment info
        $paymentInfo = $this->getPaymentInfo($transaction);
        
        $data = [
            'title' => 'Detail Transaksi',
            'transaction' => $transactionData,
            'payment_info' => $paymentInfo,
            'previous_url' => $this->request->getGet('from') ?? base_url('admin/transactions')
        ];
        
        return view('admin/transactions/detail', $data);
    }
    


/**
 * Helper method to get payment information
 */
private function getPaymentInfo($transaction)
{
    $paymentInfo = [];
    
    // Check VA payment
    if ($transaction->va_bank_name && $transaction->va_account_number) {
        $paymentInfo['type'] = 'Virtual Account';
        $paymentInfo['bank'] = $transaction->va_bank_name;
        $paymentInfo['account_number'] = $transaction->va_account_number;
    }
    
    // Check Bank Transfer
    if ($transaction->bank_name && $transaction->account_number) {
        $paymentInfo['type'] = 'Bank Transfer';
        $paymentInfo['bank'] = $transaction->bank_name;
        $paymentInfo['account_name'] = $transaction->account_name;
        $paymentInfo['account_number'] = $transaction->account_number;
    }
    
    // Default if no specific payment info
    if (empty($paymentInfo)) {
        $paymentInfo['type'] = ucwords(str_replace('_', ' ', $transaction->type));
        $paymentInfo['method'] = 'System';
    }
    
    return $paymentInfo;
}

/**
 * Update transaction status
 */
public function updateStatus($id)
{
    $model = new TransactionModel();
    
    // Get input data
    $status = $this->request->getPost('status');
    $description = $this->request->getPost('description');
    $adminId = session()->get('user_id'); // Get current admin ID
    
    // Prepare data for update
    $data = [
        'status' => $status,
        'description' => $description,
        'approved_by_admin_id' => $adminId
    ];
    
    // Update transaction
    $result = $model->updateTransaction($id, $data);
    
    if ($result['success']) {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Transaction status updated successfully',
            'transaction_id' => $id,
            'status' => $status
        ]);
    } else {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Failed to update transaction',
            'errors' => $result['errors'] ?? []
        ]);
    }
}

/**
 * Delete transaction (soft delete if implemented)
 */
public function delete($id)
{
    $model = new TransactionModel();
    
    // Check if transaction exists
    $transaction = $model->find($id);
    
    if (!$transaction) {
        return $this->response->setStatusCode(404)->setJSON([
            'success' => false,
            'message' => 'Transaction not found'
        ]);
    }
    
    // Check if transaction can be deleted (only pending transactions)
    if ($transaction->status !== 'pending') {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Only pending transactions can be deleted'
        ]);
    }
    
    // Delete transaction
    $deleted = $model->delete($id);
    
    if ($deleted) {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Transaction deleted successfully',
            'transaction_id' => $id
        ]);
    } else {
        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'Failed to delete transaction'
        ]);
    }
}

/**
 * Export transactions
 */
public function export()
{
    $model = new TransactionModel();
    
    // Get filter parameters
    $params = [
        'search' => $this->request->getGet('search'),
        'type' => $this->request->getGet('type'),
        'status' => $this->request->getGet('status'),
        'flow' => $this->request->getGet('flow'),
        'date_from' => $this->request->getGet('date_from'),
        'date_to' => $this->request->getGet('date_to'),
        'per_page' => 0, // Get all data
        'page' => 1
    ];
    
    // Get data
    $result = $model->getAdminReport($params);
    
    // Get export format
    $format = $this->request->getGet('format') ?? 'excel';
    
    // Create CSV content
    $csvContent = $this->generateCSV($result['data']);
    
    // Set headers for download
    $filename = 'transactions_' . date('Y-m-d_H-i-s') . '.csv';
    
    return $this->response->setHeader('Content-Type', 'text/csv')
                         ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                         ->setBody($csvContent);
}

/**
 * Generate CSV content
 */
private function generateCSV($transactions)
{
    $output = fopen('php://temp', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Add headers
    fputcsv($output, [
        'ID',
        'Tanggal',
        'User',
        'Username',
        'Tipe',
        'Arus',
        'Jumlah',
        'Status',
        'Keterangan',
        'Created At'
    ]);
    
    // Add data rows
    foreach ($transactions as $tx) {
        fputcsv($output, [
            $tx->id,
            date('d/m/Y H:i', strtotime($tx->created_at)),
            $tx->full_name ?? 'N/A',
            $tx->username ?? 'N/A',
            $tx->type,
            $tx->flow,
            $tx->amount,
            $tx->status,
            $tx->description ?? '',
            $tx->created_at
        ]);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Get transaction statistics
 */
public function stats()
{
    $model = new TransactionModel();
    
    $userId = $this->request->getGet('user_id');
    $startDate = $this->request->getGet('start_date');
    $endDate = $this->request->getGet('end_date');
    
    // Get basic stats
    $stats = $model->getTransactionStats($userId);
    
    // Get daily stats for chart
    $dailyStats = $this->getDailyTransactionStats($startDate, $endDate, $userId);
    
    return $this->response->setJSON([
        'success' => true,
        'stats' => $stats,
        'daily_stats' => $dailyStats,
        'period' => [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    ]);
}

/**
 * Get daily transaction statistics for chart
 */
private function getDailyTransactionStats($startDate = null, $endDate = null, $userId = null)
{
    $model = new TransactionModel();
    
    // Set default date range (last 30 days)
    if (!$startDate) {
        $startDate = date('Y-m-d', strtotime('-30 days'));
    }
    if (!$endDate) {
        $endDate = date('Y-m-d');
    }
    
    $builder = $model->select("DATE(created_at) as date, 
                              SUM(CASE WHEN flow = 'in' AND status = 'success' THEN amount ELSE 0 END) as income,
                              SUM(CASE WHEN flow = 'out' AND status = 'success' THEN amount ELSE 0 END) as expense,
                              COUNT(*) as transaction_count")
                     ->where('DATE(created_at) >=', $startDate)
                     ->where('DATE(created_at) <=', $endDate);
    
    if ($userId) {
        $builder->where('user_id', $userId);
    }
    
    $result = $builder->groupBy('DATE(created_at)')
                      ->orderBy('date', 'ASC')
                      ->findAll();
    
    // Format for chart
    $labels = [];
    $incomeData = [];
    $expenseData = [];
    $transactionCount = [];
    
    foreach ($result as $row) {
        $labels[] = date('d M', strtotime($row->date));
        $incomeData[] = (float)$row->income;
        $expenseData[] = (float)$row->expense;
        $transactionCount[] = (int)$row->transaction_count;
    }
    
    return [
        'labels' => $labels,
        'income' => $incomeData,
        'expense' => $expenseData,
        'transaction_count' => $transactionCount
    ];
    }
public function summary1()
    {
        $model = new TransactionModel();

        // 1. Ambil tanggal dari Query String (GET)
        // Default ke awal bulan ini dan hari ini jika tidak ada input
        $dateFrom = $this->request->getGet('date_from') ?: date('Y-m-01');
        $dateTo   = $this->request->getGet('date_to') ?: date('Y-m-d');

        // 2. Ambil data rekap dari Model
        $summary = $model->getVolumeByType($dateFrom, $dateTo);

        // 3. Kalkulasi Grand Total untuk informasi tambahan di Dashboard
        $grandTotalIn  = 0;
        $grandTotalOut = 0;

        foreach ($summary as $item) {
            if ($item->flow === 'in') {
                $grandTotalIn += $item->total_nominal;
            } else {
                $grandTotalOut += $item->total_nominal;
            }
        }
        $data = [
            'title'          => 'Ringkasan Keuangan',
            'summary'        => $summary,
            'dateFrom'       => $dateFrom,
            'dateTo'         => $dateTo,
            'totalIn'        => $grandTotalIn,
            'totalOut'       => $grandTotalOut,
            'netProfit'      => $grandTotalIn - $grandTotalOut
        ];

        return view('admin/transactions/summary', $data);
    }
    public function summary()
    {
        $model = new TransactionModel();

        // 1. Ambil input tanggal (Default: 30 hari terakhir)
        $dateFrom = $this->request->getGet('date_from') ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo   = $this->request->getGet('date_to') ?: date('Y-m-d');

        // 2. Ambil data volume per tipe
        $volumeData = $model->getVolumeByType($dateFrom, $dateTo);

        // 3. Hitung total seluruh volume untuk kalkulasi persentase
        $totalAllVolume = array_sum(array_column($volumeData, 'total_akumulasi'));

        $data = [
            'title'      => 'Analisis Volume Transaksi',
            'volumeData' => $volumeData,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'grandTotal' => $totalAllVolume
        ];

        return view('admin/transactions/volume_report', $data);
    }
    // File: app/Controllers/Admin/FinanceReport.php (atau sesuai nama controller Anda)

    public function systemImpact()
    {
        $model = new TransactionModel();
        $dateFrom = $this->request->getGet('date_from') ?: date('Y-m-01');
        $dateTo   = $this->request->getGet('date_to') ?: date('Y-m-d');

        $report = $model->getSystemBalanceByType($dateFrom, $dateTo);

        $chartLabels = [];
        $chartData = [];
        $netProfit = 0;

        foreach ($report as $row) {
            $chartLabels[] = str_replace('_', ' ', strtoupper($row->type));
            $chartData[]   = $row->saldo_impact;
            $netProfit    += $row->saldo_impact;
        }

        // --- PASTIKAN BAGIAN INI ADA ---
        $data = [
            'report'      => $report,
            'chartLabels' => json_encode($chartLabels), // Variabel ini yang dicari View
            'chartData'   => json_encode($chartData),   // Variabel ini juga penting
            'netProfit'   => $netProfit,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo
        ];

        // Pastikan path view sesuai dengan lokasi file Anda
        return view('admin/transactions/system_impact', $data); 
    }
}
