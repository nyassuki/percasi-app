<?php namespace App\Models;

use CodeIgniter\Model;

class AccountingModel extends Model {

    protected $db;

    public function __construct() {
        $this->db = \Config\Database::connect();
    }

    public function autoPost() {
        $this->db->transStart();

        // 1. Dapatkan Periode Aktif
        $period = $this->db->table('accounting_periods')
                           ->where('is_closed', 0)
                           ->get()->getRow();

        if (!$period) return "Periode akuntansi aktif tidak ditemukan.";

        // 2. Ambil transaksi yang SUCCESS dan belum masuk jurnal
        $transactions = $this->db->table('transactions t')
            ->select('t.*')
            ->join('journal_entries j', 'j.transaction_id = t.id', 'left')
            ->where('t.status', 'success')
            ->where('j.id', null)
            ->get()->getResult();


            print "<pre>";
            print_r($transactions);
           // die();
        foreach ($transactions as $tr) {
            // 3. Simpan Header (journal_entries)
            $headerData = [
                'transaction_id'   => $tr->id,
                'transaction_date' => date('Y-m-d', strtotime($tr->created_at)),
                'reference_no'     => $tr->kode_transaksi,
                'description'      => "Auto Post: " . strtoupper($tr->type) . " - " . $tr->description,
                'period_id'        => $period->id,
                'is_posted'        => 1,
                'created_by'       => session()->get('user_id') // Asumsi session user
            ];
            $this->db->table('journal_entries')->insert($headerData);
            $journalId = $this->db->insertID();

            // 4. Logika Posting (Double Entry)
            $this->mapAccountingLogic($journalId, $tr);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    private function mapAccountingLogic($journalId, $tr) {
        switch ($tr->type) {
            case 'topup_va':
                $this->addDetail($journalId, '6201.09', $tr->amount, 0); // Kas (Debit)
                $this->addDetail($journalId, '2101.03', 0, $tr->amount); // Saldo User (Credit)
                break;
            case 'tournament_fee':
                $this->addDetail($journalId, '2101.03', $tr->amount, 0); // Saldo User (Debit)
                $this->addDetail($journalId, '4101.04', 0, $tr->amount); // Pendapatan (Credit)
                break;
            case 'prize_payout':
                $this->addDetail($journalId, '6201.09', $tr->amount, 0); // Beban Hadiah (Debit)
                $this->addDetail($journalId, '2101.03', 0, $tr->amount); // Kas (Debit)
                break;
             case 'Transfer':
                $this->addDetail($journalId, '2101.03', $tr->amount, 0); // Saldo User (Debit)
                $this->addDetail($journalId, '2101.03', 0, $tr->amount); // Saldo User (kredit)
                break;
        }
    }

    private function addDetail($journalId, $coaCode, $debit, $credit) {
        $coa = $this->db->table('chart_of_accounts')->where('account_code', $coaCode)->get()->getRow();
        if ($coa) {
            $this->db->table('journal_details')->insert([
                'journal_id' => $journalId,
                'coa_id'     => $coa->id,
                'debit'      => $debit,
                'credit'     => $credit
            ]);
        }
    }
    public function saveManualJournal(array $headerData, array $detailsData)
    {
        $this->db->transStart();

        // 1. Simpan Header ke journal_entries
        $this->db->table('journal_entries')->insert($headerData);
        $journalId = $this->db->insertID();

       
     

        // 2. Simpan Detail ke journal_details
        foreach ($detailsData as $key => $journal) {
            if (!empty($journal)) {
                print $journal['coa_id'];
                
                $this->db->table('journal_details')->insert([
                    'journal_id' => $journalId,
                    'coa_id'     => $journal['coa_id'],
                    'debit'      => $journal['debit'] ?: 0,
                    'credit'     => $journal['credit'] ?: 0
                ]);
            }
        }

        
        $this->db->transComplete();
       
        // Mengembalikan status transaksi (true/false)
        return $this->db->transStatus();
    }
    public function getBalanceSheet($date)
    {
        return $this->db->table('chart_of_accounts coa')
            ->select('
                g.group_name, 
                g.group_code,
                g.normal_balance,
                sg.subgroup_name, 
                coa.account_code, 
                coa.account_name,
                SUM(jd.debit) as total_debit,
                SUM(jd.credit) as total_credit
            ')
            ->join('coa_subgroups sg', 'sg.id = coa.subgroup_id')
            ->join('coa_groups g', 'g.id = sg.group_id')
            ->join('journal_details jd', 'jd.coa_id = coa.id', 'left')
            ->join('journal_entries je', 'je.id = jd.journal_id', 'left')
            ->where('je.transaction_date <=', $date)
            ->groupBy('coa.id')
            ->orderBy('coa.account_code', 'ASC')
            ->get()->getResult();
    }
    public function getTrialBalance($date)
    {
        return $this->db->table('chart_of_accounts coa')
            ->select('
                coa.account_code, 
                coa.account_name, 
                g.normal_balance,
                SUM(jd.debit) as total_debit, 
                SUM(jd.credit) as total_credit
            ')
            ->join('coa_subgroups sg', 'sg.id = coa.subgroup_id')
            ->join('coa_groups g', 'g.id = sg.group_id')
            ->join('journal_details jd', 'jd.coa_id = coa.id', 'left')
            ->join('journal_entries je', 'je.id = jd.journal_id', 'left')
            ->where('je.transaction_date <=', $date)
            ->groupBy('coa.id')
            ->orderBy('coa.account_code', 'ASC')
            ->get()->getResult();
    }
    public function getIncomeStatement($startDate, $endDate)
    {
        return $this->db->table('chart_of_accounts coa')
            ->select('
                g.group_code,
                sg.subgroup_code,
                sg.subgroup_name,
                coa.account_code,
                coa.account_name,
                SUM(jd.debit) as total_debit,
                SUM(jd.credit) as total_credit
            ')
            ->join('coa_subgroups sg', 'sg.id = coa.subgroup_id')
            ->join('coa_groups g', 'g.id = sg.group_id')
            ->join('journal_details jd', 'jd.coa_id = coa.id', 'left')
            ->join('journal_entries je', 'je.id = jd.journal_id', 'left')
            ->where('je.transaction_date >=', $startDate)
            ->where('je.transaction_date <=', $endDate)
            ->whereIn('g.group_code', ['4', '5', '6', '7']) // Hanya Revenue & Expenses
            ->groupBy('coa.id')
            ->orderBy('coa.account_code', 'ASC')
            ->get()->getResult();
    }
    public function getProfitLoss($startDate, $endDate)
    {
        return $this->db->table('chart_of_accounts coa')
            ->select('
                g.group_code,
                sg.subgroup_code,
                sg.subgroup_name,
                coa.account_code,
                coa.account_name,
                SUM(jd.debit) as total_debit,
                SUM(jd.credit) as total_credit
            ')
            ->join('coa_subgroups sg', 'sg.id = coa.subgroup_id')
            ->join('coa_groups g', 'g.id = sg.group_id')
            ->join('journal_details jd', 'jd.coa_id = coa.id', 'left')
            ->join('journal_entries je', 'je.id = jd.journal_id', 'left')
            ->where('je.transaction_date >=', $startDate)
            ->where('je.transaction_date <=', $endDate)
            ->whereIn('g.group_code', ['4', '5', '6', '7']) // Kelompok Pendapatan & Beban
            ->groupBy('coa.id')
            ->orderBy('coa.account_code', 'ASC')
            ->get()->getResult();
    }
    public function getOpeningBalance($coa_id, $startDate)
        {
            $row = $this->db->table('journal_details jd')
                ->select('SUM(jd.debit) as total_debit, SUM(jd.credit) as total_credit, g.normal_balance')
                ->join('journal_entries je', 'je.id = jd.journal_id')
                ->join('chart_of_accounts coa', 'coa.id = jd.coa_id')
                ->join('coa_subgroups sg', 'sg.id = coa.subgroup_id')
                ->join('coa_groups g', 'g.id = sg.group_id')
                ->where('jd.coa_id', $coa_id)
                ->where('je.transaction_date <', $startDate)
                ->get()->getRow();

            if (!$row) return 0;

            return ($row->normal_balance == 'DEBIT') 
                   ? ($row->total_debit - $row->total_credit) 
                   : ($row->total_credit - $row->total_debit);
        }

        public function getAccountMutations($coa_id, $startDate, $endDate)
        {
            return $this->db->table('journal_details jd')
                ->select('je.transaction_date, je.reference_no, je.description, jd.debit, jd.credit, g.normal_balance')
                ->join('journal_entries je', 'je.id = jd.journal_id')
                ->join('chart_of_accounts coa', 'coa.id = jd.coa_id')
                ->join('coa_subgroups sg', 'sg.id = coa.subgroup_id')
                ->join('coa_groups g', 'g.id = sg.group_id')
                ->where('jd.coa_id', $coa_id)
                ->where('je.transaction_date >=', $startDate)
                ->where('je.transaction_date <=', $endDate)
                ->orderBy('je.transaction_date', 'ASC')
                ->orderBy('je.id', 'ASC')
                ->get()->getResult();
        }
}