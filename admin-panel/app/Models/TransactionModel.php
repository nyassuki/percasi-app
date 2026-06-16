<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = "vw_transaction";
    protected $primaryKey = "id";
    protected $useAutoIncrement = true;

    protected $returnType = "object";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "user_id",
        "related_user_id",
        "type",
        "flow",
        "amount",
        "current_balance_snapshot",
        "status",
        "user_va_id",
        "user_bank_account_id",
        "tournament_id",
        "approved_by_admin_id",
        "description",
        "created_at",
        "updated_at",
    ];

    protected $useTimestamps = true;
    protected $createdField = "created_at";
    protected $updatedField = "updated_at";

    protected $validationRules = [
        "user_id" => "required|integer|is_not_unique[users.id]",
        "type" =>
            "required|in_list[topup_va,tournament_fee,prize_payout,withdrawal,refund,penalty,Transfer]",
        "flow" => "required|in_list[in,out]",
        "amount" => "required|decimal|greater_than[0]",
        "status" => "permit_empty|in_list[pending,success,failed]",
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    // Method untuk transaksi user
    public function getUserTransactions($userId, $limit = 50)
    {
        return $this->where("user_id", $userId)
            ->orderBy("created_at", "DESC")
            ->limit($limit)
            ->findAll();
    }

    // Method untuk transaksi pending
    public function getPendingTransactions($type = null)
    {
        $builder = $this->where("status", "pending");

        if ($type) {
            $builder->where("type", $type);
        }

        return $builder->findAll();
    }

    // Method untuk transaksi berdasarkan type
    public function getTransactionsByType($type, $status = null, $limit = 100)
    {
        $builder = $this->where("type", $type);

        if ($status) {
            $builder->where("status", $status);
        }

        return $builder
            ->orderBy("created_at", "DESC")
            ->limit($limit)
            ->findAll();
    }

    // Method untuk update status transaksi
    public function updateTransactionStatus(
        $transactionId,
        $status,
        $adminId = null
    ) {
        $data = ["status" => $status];

        if ($adminId && in_array($status, ["success", "failed"])) {
            $data["approved_by_admin_id"] = $adminId;
        }

        return $this->update($transactionId, $data);
    }

    // Method untuk report admin dengan paging dan search
    public function getAdminReport($params = [])
    {
        // 1. Sanitasi & Inisialisasi Parameter
        $perPage = (int) ($params["per_page"] ?? 10);
        $page = (int) ($params["page"] ?? 1);
        $search = $params["search"] ?? "";
        $type = $params["type"] ?? "";
        $status = $params["status"] ?? "";
        $flow = $params["flow"] ?? "";
        $dateFrom = $params["date_from"] ?? "";
        $dateTo = $params["date_to"] ?? "";

        $offset = ($page - 1) * $perPage;

        // 2. Inisialisasi Builder dari View/Table Utama
        // Gunakan table() secara eksplisit agar lebih aman
        $builder = $this->db->table("vw_transaction");

        $builder->select(
            'vw_transaction.*, 
         u.username, 
         u.full_name, 
         adm.full_name as admin_full_name' // Gunakan alias yang lebih deskriptif
        );

        // 3. Joins
        $builder->join("users u", "u.id = vw_transaction.user_id", "left");
        $builder->join(
            "users adm",
            "adm.id = vw_transaction.approved_by_admin_id",
            "left"
        );

        // 4. Kondisi Pencarian (Grouped)
        if (!empty($search)) {
            $builder
                ->groupStart()
                ->like("u.username", $search)
                ->orLike("u.full_name", $search)
                ->orLike("vw_transaction.kode_transaksi", $search)
                ->orLike("vw_transaction.description", $search)
                ->orLike("adm.full_name", $search)
                ->groupEnd();
        }

        // 5. Filter Kategori
        if (!empty($type)) {
            $builder->where("vw_transaction.type", $type);
        }
        if (!empty($status)) {
            $builder->where("vw_transaction.status", $status);
        }
        if (!empty($flow)) {
            $builder->where("vw_transaction.flow", $flow);
        }

        // 6. Filter Tanggal (Optimasi SARGable)
        // Hindari fungsi DATE() di sisi kolom agar database bisa menggunakan INDEX
        if (!empty($dateFrom)) {
            $builder->where(
                "vw_transaction.created_at >=",
                $dateFrom . " 00:00:00"
            );
        }
        if (!empty($dateTo)) {
            $builder->where(
                "vw_transaction.created_at <=",
                $dateTo . " 23:59:59"
            );
        }

        // 7. Hitung Total (Clone builder agar filter tetap terjaga)
        $totalBuilder = clone $builder;
        $total = $totalBuilder->countAllResults();

        // 8. Ambil Data dengan Paging
        $data = $builder
            ->orderBy("vw_transaction.created_at", "DESC")
            ->limit($perPage, $offset)
            ->get()
            ->getResult();

        return [
            "data" => $data,
            "total" => $total,
            "per_page" => $perPage,
            "current_page" => $page,
            "total_pages" => $perPage > 0 ? ceil($total / $perPage) : 1,
        ];
    }

    // Alternative: Menggunakan paginate CI4 (lebih sederhana)
    public function getPaginatedAdminReport($search = null, $perPage = 10)
    {
        $builder = $this->select(
            "vw_transaction.*, users.username, users.full_name"
        )
            ->join("users", "users.id = vw_transaction.user_id", "left")
            ->join(
                "users as admin_users",
                "admin_users.id = vw_transaction.approved_by_admin_id",
                "left"
            );

        if ($search) {
            $builder
                ->groupStart()
                ->like("users.username", $search)
                ->orLike("users.full_name", $search)
                ->orLike("vw_transaction.type", $search)
                ->orLike("vw_transaction.description", $search)
                ->orLike("vw_transaction.amount", $search)
                ->orLike("admin_users.full_name", $search)
                ->groupEnd();
        }

        return $builder
            ->orderBy("vw_transaction.created_at", "DESC")
            ->paginate($perPage);
    }

    // Method untuk mendapatkan filter options (untuk dropdown)
    public function getFilterOptions()
    {
        return [
            "types" => $this->distinct()
                ->select("type")
                ->orderBy("type")
                ->findAll(),
            "statuses" => $this->distinct()
                ->select("status")
                ->where("status IS NOT NULL")
                ->orderBy("status")
                ->findAll(),
            "flows" => $this->distinct()
                ->select("flow")
                ->orderBy("flow")
                ->findAll(),
        ];
    }
    // Di dalam class TransactionModel, tambahkan method berikut:

    /**
     * Get transaction detail with all related information
     */
    // Di dalam class TransactionModel, tambahkan method berikut:

    /**
     * Get transaction detail with all related information
     */
    public function getTransactionDetail($id)
    {
        return $this->select(
            'vw_transaction.*, 
                         admin_users.full_name as admin_username,
                         admin_users.full_name as admin_full_name,
                         va_banks.bank_code as va_bank_name,
                         va_banks.va_number as va_account_number,
                         bank_accounts.bank_code as bank_name,
                         bank_accounts.account_holder_name,
                         bank_accounts.account_number,
                         tournaments.title as tournament_title'
        )
            ->join(
                "admins as admin_users",
                "admin_users.id = vw_transaction.approved_by_admin_id",
                "left"
            )
            ->join(
                "user_virtual_accounts as va_banks",
                "va_banks.id = vw_transaction.user_va_id",
                "left"
            )
            ->join(
                "user_bank_accounts as bank_accounts",
                "bank_accounts.id = vw_transaction.user_bank_account_id",
                "left"
            )
            ->join(
                "tournaments",
                "tournaments.id = vw_transaction.tournament_id",
                "left"
            )
            ->where("vw_transaction.id", $id)
            ->first();
    }

    /**
     * Get transaction statistics for dashboard
     */
    public function getTransactionStats($userId = null)
    {
        $builder = $this;

        if ($userId) {
            $builder->where("user_id", $userId);
        }

        // Total transactions
        $total = $builder->countAllResults();

        // Total income
        $income = clone $builder;
        $totalIncome =
            $income
                ->selectSum("amount")
                ->where("flow", "in")
                ->where("status", "success")
                ->get()
                ->getRow()->amount ?? 0;

        // Total expense
        $expense = clone $builder;
        $totalExpense =
            $expense
                ->selectSum("amount")
                ->where("flow", "out")
                ->where("status", "success")
                ->get()
                ->getRow()->amount ?? 0;

        // Pending transactions
        $pending = clone $builder;
        $totalPending = $pending->where("status", "pending")->countAllResults();

        return [
            "total_transactions" => $total,
            "total_income" => (float) $totalIncome,
            "total_expense" => (float) $totalExpense,
            "total_pending" => $totalPending,
            "net_balance" => (float) ($totalIncome - $totalExpense),
        ];
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions($limit = 10)
    {
        return $this->select(
            "vw_transaction.*, users.username, users.full_name"
        )
            ->join("users", "users.id = vw_transaction.user_id", "left")
            ->orderBy("created_at", "DESC")
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get transactions by date range
     */
    public function getTransactionsByDateRange(
        $startDate,
        $endDate,
        $userId = null
    ) {
        $builder = $this->select(
            "vw_transaction.*, users.username, users.full_name"
        )
            ->join("users", "users.id = vw_transaction.user_id", "left")
            ->where("DATE(vw_transaction.created_at) >=", $startDate)
            ->where("DATE(vw_transaction.created_at) <=", $endDate);

        if ($userId) {
            $builder->where("vw_transaction.user_id", $userId);
        }

        return $builder->orderBy("created_at", "DESC")->findAll();
    }

    /**
     * Update transaction with validation
     */
    public function updateTransaction($id, $data)
    {
        // Validate required fields
        $validationRules = [
            "status" => "required|in_list[pending,success,failed]",
            "description" => "permit_empty|string|max_length[500]",
        ];

        $validation = \Config\Services::validation();

        if (!$validation->setRules($validationRules)->run($data)) {
            return [
                "success" => false,
                "errors" => $validation->getErrors(),
            ];
        }

        // Update transaction
        $result = $this->update($id, $data);

        if ($result) {
            return [
                "success" => true,
                "message" => "Transaction updated successfully",
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to update transaction",
        ];
    }
    public function getTotalByType($dateFrom, $dateTo)
    {
        return $this->db
            ->table("transactions")
            ->select(
                "type, flow, COUNT(*) as jumlah_transaksi, SUM(amount) as total_nominal"
            )
            ->where("created_at >=", $dateFrom . " 00:00:00")
            ->where("created_at <=", $dateTo . " 23:59:59")
            ->where("status", "success")
            ->groupBy("type, flow")
            ->orderBy("total_nominal", "DESC")
            ->get()
            ->getResult();
    }
    /**
     * Menghitung total volume transaksi per tipe dalam rentang tanggal
     * Tanpa mempedulikan flow (In/Out)
     */
    public function getVolumeByType($dateFrom, $dateTo)
    {
        return $this->db->table("transactions")
            ->select("type, COUNT(*) as jumlah_transaksi, SUM(amount) as total_akumulasi")
            ->where("created_at >=", $dateFrom . " 00:00:00")
            ->where("created_at <=", $dateTo . " 23:59:59")
            ->where("status", "success")
            ->groupBy("type")
            ->orderBy("total_akumulasi", "DESC")
            ->get()
            ->getResult();
    }
    /**
     * Menghitung saldo/dampak finansial per tipe transaksi terhadap sistem
     * Berdasarkan logika bisnis pendapatan vs pengeluaran sistem
     */
    public function getSystemBalanceByType($dateFrom, $dateTo)
    {
        // Menggunakan CASE WHEN untuk menentukan multiplier (1, -1, atau 0)
        // sesuai dengan penjelasan logika sistem Anda
        $caseLogic = "
            CASE 
                WHEN type = 'tournament_fee' THEN amount
                WHEN type = 'penalty'       THEN amount
                WHEN type = 'prize_payout'   THEN (amount * -1)
                WHEN type = 'refund'         THEN (amount * -1)
                WHEN type = 'topup_va'       THEN 0
                WHEN type = 'withdrawal'     THEN 0
                WHEN type = 'Transfer'       THEN 0
                ELSE 0 
            END";

        return $this->db->table("transactions")
            ->select("type")
            ->select("COUNT(*) as total_transaksi")
            // Sum berdasarkan logika CASE di atas
            ->selectSum($caseLogic, "saldo_impact")
            ->where("created_at >=", $dateFrom . " 00:00:00")
            ->where("created_at <=", $dateTo . " 23:59:59")
            ->where("status", "success")
            ->groupBy("type")
            ->orderBy("saldo_impact", "DESC")
            ->get()
            ->getResult();
    }
}
