<?php 
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AccountingModel;

class Accounting extends BaseController {
    protected $accModel;

    public function __construct() {
        $this->accModel = new AccountingModel();
    }

    public function index() {
        $db = \Config\Database::connect();
        // Menampilkan Jurnal Umum
        $data['journals'] = $db->table('journal_entries e')
            ->select('e.*, d.debit, d.credit, coa.account_name, coa.account_code')
            ->join('journal_details d', 'd.journal_id = e.id')
            ->join('chart_of_accounts coa', 'coa.id = d.coa_id')
            ->orderBy('e.transaction_date', 'DESC')
            ->orderBy('e.id', 'DESC')
            ->get()->getResult();

        return view('admin/accounting/index', $data);
    }

    public function postJurnal() {
        $model = new AccountingModel();
        if ($model->autoPost() === true) {
            return redirect()->to('/admin/accounting')->with('success', 'Posting Jurnal Berhasil.');
        }
        return redirect()->to('/admin/accounting')->with('error', 'Terjadi kesalahan atau data tidak ditemukan.');
    }
    public function createJournal()
    {
        $db = \Config\Database::connect();
        $data['accounts'] = $db->table('chart_of_accounts')->where('is_active', 1)->get()->getResult();
        $data['periods']  = $db->table('accounting_periods')->where('is_closed', 0)->get()->getResult();
        
        return view('admin/accounting/form_journal', $data);
    }

   public function saveJournal()
    {
        $model = new \App\Models\AccountingModel();

        // Siapkan data Header
        $header = [
            'transaction_date' => $this->request->getPost('transaction_date'),
            'reference_no'     => $this->request->getPost('reference_no'),
            'description'      => $this->request->getPost('description'),
            'period_id'        => $this->request->getPost('period_id'),
            'is_posted'        => 1,
            'created_by'       => session()->get('user_id')
        ];

        // Siapkan data Detail
        $details = [
            'coa_id' => $this->request->getPost('coa_id'),
            'debit'  => $this->request->getPost('debit'),
            'credit' => $this->request->getPost('credit')
        ];
        $journal_details = $this->request->getPost('journal_details');

      
        // Panggil fungsi di Model
        if ($model->saveManualJournal($header, $journal_details)) {
            return redirect()->to('/admin/accounting')->with('success', 'Jurnal berhasil disimpan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan jurnal');
        }
    }
    public function balanceSheet()
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $model = new \App\Models\AccountingModel();
        $rawData = $model->getBalanceSheet($date);

        $report = [
            'assets' => [],
            'liabilities' => [],
            'equity' => []
        ];

        $totals = ['assets' => 0, 'liabilities' => 0, 'equity' => 0];

        foreach ($rawData as $row) {
            // Hitung Saldo Akhir berdasarkan Normal Balance
            $balance = ($row->normal_balance == 'DEBIT') 
                       ? ($row->total_debit - $row->total_credit) 
                       : ($row->total_credit - $row->total_debit);

            $category = '';
            if ($row->group_code == '1') $category = 'assets';
            elseif ($row->group_code == '2') $category = 'liabilities';
            elseif ($row->group_code == '3') $category = 'equity';

            if ($category) {
                $report[$category][$row->subgroup_name][] = [
                    'code' => $row->account_code,
                    'name' => $row->account_name,
                    'balance' => $balance
                ];
                $totals[$category] += $balance;
            }
        }

        return view('admin/accounting/balance_sheet', [
            'report' => $report,
            'totals' => $totals,
            'date'   => $date
        ]);
    }
    public function trialBalance()
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $model = new \App\Models\AccountingModel();
        $rawData = $model->getTrialBalance($date);

        $results = [];
        $totals = ['debit' => 0, 'credit' => 0];

        foreach ($rawData as $row) {
            $debitBalance = 0;
            $creditBalance = 0;

            // Hitung Saldo Akhir berdasarkan Normal Balance Akun
            if ($row->normal_balance == 'DEBIT') {
                $balance = $row->total_debit - $row->total_credit;
                if ($balance >= 0) $debitBalance = $balance;
                else $creditBalance = abs($balance);
            } else {
                $balance = $row->total_credit - $row->total_debit;
                if ($balance >= 0) $creditBalance = $balance;
                else $debitBalance = abs($balance);
            }

            $results[] = [
                'code' => $row->account_code,
                'name' => $row->account_name,
                'debit' => $debitBalance,
                'credit' => $creditBalance
            ];

            $totals['debit'] += $debitBalance;
            $totals['credit'] += $creditBalance;
        }

        return view('admin/accounting/trial_balance', [
            'data'   => $results,
            'totals' => $totals,
            'date'   => $date
        ]);
    }
    public function incomeStatement()
    {
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end') ?? date('Y-m-t');
        
        $model = new \App\Models\AccountingModel();
        $rawData = $model->getIncomeStatement($start, $end);

        $report = [
            'revenue' => [], 'cogs' => [], 'operating_exp' => [],
            'depreciation' => [], 'other_inc' => [], 'other_exp' => []
        ];

        $totals = ['revenue' => 0, 'cogs' => 0, 'operating_exp' => 0, 'depreciation' => 0, 'other' => 0];

        foreach ($rawData as $row) {
            // Hitung saldo akhir (Revenue/Other Inc normalnya Kredit, Expense normalnya Debit)
            $balance = ($row->group_code == '4' || $row->group_code == '7') 
                       ? ($row->total_credit - $row->total_debit) 
                       : ($row->total_debit - $row->total_credit);

            if ($row->subgroup_code == '4100') {
                $report['revenue'][] = $row; $totals['revenue'] += $balance;
            } elseif ($row->subgroup_code == '5100') {
                $report['cogs'][] = $row; $totals['cogs'] += $balance;
            } elseif ($row->subgroup_code == '6100' || $row->subgroup_code == '6200') {
                $report['operating_exp'][] = $row; $totals['operating_exp'] += $balance;
            } elseif ($row->subgroup_code == '6300') {
                $report['depreciation'][] = $row; $totals['depreciation'] += $balance;
            } elseif ($row->group_code == '7') {
                if ($row->total_credit > $row->total_debit) {
                    $report['other_inc'][] = $row; $totals['other'] += $balance;
                } else {
                    $report['other_exp'][] = $row; $totals['other'] -= $balance;
                }
            }
        }

        return view('admin/accounting/income_statement', [
            'report' => $report,
            'totals' => $totals,
            'start'  => $start,
            'end'    => $end
        ]);
    }
    public function profitLoss()
    {
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end') ?? date('Y-m-t');
        
        $model = new \App\Models\AccountingModel();
        $rawData = $model->getProfitLoss($start, $end);

        $report = [
            'revenue' => [], 
            'cogs' => [], 
            'operating_exp' => [],
            'other_income' => [], 
            'other_expense' => []
        ];

        $totals = ['revenue' => 0, 'cogs' => 0, 'opex' => 0, 'other' => 0];

        foreach ($rawData as $row) {
            // Hitung Saldo (Pendapatan normalnya Kredit, Beban normalnya Debit)
            $isRevenue = in_array($row->group_code, ['4', '7']);
            $balance = $isRevenue ? ($row->total_credit - $row->total_debit) : ($row->total_debit - $row->total_credit);

            if ($row->subgroup_code == '4100') {
                $report['revenue'][] = ['name' => $row->account_name, 'val' => $balance];
                $totals['revenue'] += $balance;
            } elseif ($row->subgroup_code == '5100') {
                $report['cogs'][] = ['name' => $row->account_name, 'val' => $balance];
                $totals['cogs'] += $balance;
            } elseif (in_array($row->group_code, ['6'])) {
                $report['operating_exp'][] = ['name' => $row->account_name, 'val' => $balance];
                $totals['opex'] += $balance;
            } elseif ($row->group_code == '7') {
                if ($balance >= 0) {
                    $report['other_income'][] = ['name' => $row->account_name, 'val' => $balance];
                    $totals['other'] += $balance;
                } else {
                    $report['other_expense'][] = ['name' => $row->account_name, 'val' => abs($balance)];
                    $totals['other'] -= abs($balance);
                }
            }
        }

        return view('admin/accounting/profit_loss', [
            'report' => $report,
            'totals' => $totals,
            'start'  => $start,
            'end'    => $end
        ]);
    }
    public function accountStatement()
    {
        $coa_id = $this->request->getGet('coa_id');
        $start  = $this->request->getGet('start') ?? date('Y-m-01');
        $end    = $this->request->getGet('end') ?? date('Y-m-t');

        $db = \Config\Database::connect();
        $data['accounts'] = $db->table('chart_of_accounts')->orderBy('account_code', 'ASC')->get()->getResult();
        
        $data['selected_account'] = null;
        $data['mutations'] = [];
        $data['opening_balance'] = 0;

        if ($coa_id) {
            $model = new \App\Models\AccountingModel();
            $data['selected_account'] = $db->table('chart_of_accounts')->where('id', $coa_id)->get()->getRow();
            $data['opening_balance']  = $model->getOpeningBalance($coa_id, $start);
            $data['mutations']        = $model->getAccountMutations($coa_id, $start, $end);
        }

        $data['start'] = $start;
        $data['end']   = $end;

        
        return view('admin/accounting/account_statement', $data);
    }
}