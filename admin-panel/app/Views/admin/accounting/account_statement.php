<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4 py-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-file-invoice-dollar me-2"></i>Mutasi <span class="text-emerald-600">Rekening</span>
            </h1>
            <p class="text-muted mb-0">Detail transaksi dan saldo akun per periode</p>
        </div>
        <div class="d-flex gap-2">
            <?php if($selected_account): ?>
            <button class="btn btn-outline-secondary shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card glass-card border-0 shadow-lg mb-4">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2 text-primary"></i>Filter Mutasi Rekening
            </h5>
        </div>
        <div class="card-body pt-0">
            <form method="get" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">Pilih Akun</label>
                    <div class="input-group input-group-sm">
                       
                        <select name="coa_id" required class="form-control select2 js-select-account" data-placeholder="Cari Kode atau Nama Akun...">
                            <option value=""></option> <?php foreach($accounts as $acc): ?>
                                <option value="<?= $acc->id ?>" <?= ($selected_account && $selected_account->id == $acc->id) ? 'selected' : '' ?>>
                                    <?= $acc->account_code ?> - <?= $acc->account_name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">Dari</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar text-primary"></i>
                        </span>
                        <input type="date" name="start" value="<?= $start ?>" 
                               class="form-control border-start-0 ps-0 fw-bold">
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">Sampai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar text-primary"></i>
                        </span>
                        <input type="date" name="end" value="<?= $end ?>" 
                               class="form-control border-start-0 ps-0 fw-bold">
                    </div>
                </div>
                
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-gradient shadow-sm w-100">
                        <i class="fas fa-search me-1"></i>Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if($selected_account): ?>
    <?php 
    // Gunakan normal_balance dari akun yang dipilih
    $accountNormalBalance = $selected_account->normal_balance ?? 'DEBIT';
    
    // Hitung total debit dan kredit
    $totalDebit = 0;
    $totalCredit = 0;
    $endingBalance = $opening_balance;

    foreach($mutations as $m) {
        $totalDebit += $m->debit;
        $totalCredit += $m->credit;
        if ($accountNormalBalance == 'DEBIT') {
            $endingBalance += ($m->debit - $m->credit);
        } else {
            $endingBalance += ($m->credit - $m->debit);
        }
    }
    
    $balanceColor = $endingBalance >= 0 ? 'text-primary' : 'text-danger';
    ?>
    
    <!-- Account Information Card -->
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-university me-2 text-primary"></i>Informasi Akun
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-hashtag me-1"></i><?= $selected_account->account_code ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-4">
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="fw-bold text-slate-800 mb-1"><?= $selected_account->account_name ?></h4>
                            <div class="row g-3">
                                <div class="col-auto">
                                    <span class="badge bg-slate bg-opacity-10 text-slate">
                                        <i class="fas fa-code me-1"></i>Kode: <?= $selected_account->account_code ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-balance-scale me-1"></i>Normal: <?= $accountNormalBalance ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-light p-4 rounded-3">
                        <div class="text-center">
                            <div class="text-muted small mb-1">Saldo Akhir</div>
                            <h3 class="fw-bold <?= $balanceColor ?> mb-0">
                                Rp <?= number_format($endingBalance, 0, ',', '.') ?>
                            </h3>
                            <small class="text-muted">Periode: <?= date('d M Y', strtotime($start)) ?> - <?= date('d M Y', strtotime($end)) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fas fa-wallet fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-primary">Rp <?= number_format($opening_balance, 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Saldo Awal</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-danger bg-opacity-10 text-danger rounded-circle">
                                <i class="fas fa-arrow-down fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-danger">Rp <?= number_format($totalDebit, 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Debit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-emerald bg-opacity-10 text-emerald rounded-circle">
                                <i class="fas fa-arrow-up fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-emerald-600">Rp <?= number_format($totalCredit, 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Kredit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg <?= $endingBalance >= 0 ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 <?= $endingBalance >= 0 ? 'text-success' : 'text-danger' ?> rounded-circle">
                                <i class="fas <?= $endingBalance >= 0 ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold <?= $balanceColor ?>">
                                Rp <?= number_format($endingBalance, 0, ',', '.') ?>
                            </h5>
                            <p class="text-muted mb-0">Saldo Akhir</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mutasi Table Card -->
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list-alt me-2 text-primary"></i>Detail Mutasi
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-exchange-alt me-1"></i>
                    <?= count($mutations) ?> Transaksi
                </span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0" style="width: 120px;">
                                <span class="text-muted small fw-bold text-uppercase">Tanggal</span>
                            </th>
                            <th class="py-3 border-0" style="width: 150px;">
                                <span class="text-muted small fw-bold text-uppercase">Referensi</span>
                            </th>
                            <th class="py-3 border-0">
                                <span class="text-muted small fw-bold text-uppercase">Deskripsi</span>
                            </th>
                            <th class="py-3 border-0 text-end" style="width: 150px;">
                                <span class="text-muted small fw-bold text-uppercase">Debit (Rp)</span>
                            </th>
                            <th class="py-3 border-0 text-end" style="width: 150px;">
                                <span class="text-muted small fw-bold text-uppercase">Kredit (Rp)</span>
                            </th>
                            <th class="pe-4 py-3 border-0 text-end" style="width: 150px;">
                                <span class="text-muted small fw-bold text-uppercase">Saldo Akhir</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mutations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon mb-3">
                                            <i class="fas fa-exchange-alt fa-3x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">Tidak ada transaksi</h5>
                                        <p class="text-muted mb-4">Tidak ada mutasi untuk akun ini pada periode yang dipilih</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $runningBalance = $opening_balance;
                            foreach($mutations as $m): 
                                if ($accountNormalBalance == 'DEBIT') {
                                    $runningBalance += ($m->debit - $m->credit);
                                } else {
                                    $runningBalance += ($m->credit - $m->debit);
                                }
                                $rowBalanceColor = $runningBalance >= 0 ? 'text-primary' : 'text-danger';
                            ?>
                                <tr class="mutation-row">
                                    <td class="ps-4 py-3">
                                        <div class="date-badge">
                                            <div class="fw-bold text-slate-800">
                                                <?= date('d/m/Y', strtotime($m->transaction_date)) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="reference-badge">
                                            <span class="badge bg-slate bg-opacity-10 text-slate px-3 py-2 rounded-pill">
                                                <?= $m->reference_no ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="description">
                                            <div class="fw-bold text-slate-800 small"><?= $m->reference_no ?></div>
                                            <div class="text-muted small"><?= $m->description ?></div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-end">
                                        <?php if($m->debit > 0): ?>
                                            <div class="debit-amount">
                                                <span class="fw-bold text-danger">
                                                    Rp <?= number_format($m->debit, 0, ',', '.') ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-end">
                                        <?php if($m->credit > 0): ?>
                                            <div class="credit-amount">
                                                <span class="fw-bold text-emerald-600">
                                                    Rp <?= number_format($m->credit, 0, ',', '.') ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <div class="balance-amount">
                                            <span class="fw-bold <?= $rowBalanceColor ?>">
                                                Rp <?= number_format($runningBalance, 0, ',', '.') ?>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    
                    <!-- Footer dengan total -->
                    <?php if(!empty($mutations)): ?>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td class="ps-4 py-3 border-top" colspan="3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                            <i class="fas fa-calculator"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-slate-700 text-uppercase small">Total Periode</div>
                                            <small class="text-muted">Ringkasan mutasi periode</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-end border-top">
                                    <div class="total-section">
                                        <div class="text-muted small mb-1">Total Debit</div>
                                        <h5 class="fw-bold text-danger mb-0">
                                            Rp <?= number_format($totalDebit, 0, ',', '.') ?>
                                        </h5>
                                    </div>
                                </td>
                                <td class="py-3 text-end border-top">
                                    <div class="total-section">
                                        <div class="text-muted small mb-1">Total Kredit</div>
                                        <h5 class="fw-bold text-emerald-600 mb-0">
                                            Rp <?= number_format($totalCredit, 0, ',', '.') ?>
                                        </h5>
                                    </div>
                                </td>
                                <td class="pe-4 py-3 text-end border-top">
                                    <div class="total-section">
                                        <div class="text-muted small mb-1">Saldo Akhir</div>
                                        <h5 class="fw-bold <?= $balanceColor ?> mb-0">
                                            Rp <?= number_format($endingBalance, 0, ',', '.') ?>
                                        </h5>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Empty State -->
    <div class="card border-0 shadow-lg">
        <div class="card-body">
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-4">
                    <i class="fas fa-search fa-4x text-muted opacity-25"></i>
                </div>
                <h5 class="text-muted mb-2">Pilih Akun untuk Melihat Mutasi</h5>
                <p class="text-muted mb-4">Silakan pilih akun dan rentang tanggal untuk melihat detail transaksi</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
 
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: "-- Pilih Akun COA --",
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5'
    });
    
    // Print functionality
    const printBtn = document.querySelector('button[onclick="window.print()"]');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Auto-set date inputs
    const startInput = document.querySelector('input[name="start"]');
    const endInput = document.querySelector('input[name="end"]');
    
    if (startInput && !startInput.value) {
        // Set default to first day of current month
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        startInput.value = firstDay.toISOString().split('T')[0];
    }
    
    if (endInput && !endInput.value) {
        // Set default to today
        endInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>

<?= $this->endSection() ?>