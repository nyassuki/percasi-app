<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
// Hitung persentase dengan penanganan pembagian dengan nol
$totalAmount = $totals['debit'] + $totals['credit'];
$debitPercent = $totalAmount > 0 ? round(($totals['debit'] / $totalAmount) * 100) : 0;
$creditPercent = $totalAmount > 0 ? round(($totals['credit'] / $totalAmount) * 100) : 0;

// Pastikan total persentase = 100 (jika ada pembulatan)
if ($totalAmount > 0 && ($debitPercent + $creditPercent) !== 100) {
    $debitPercent = 100 - $creditPercent;
}

// Tentukan status balance
$isBalanced = round($totals['debit']) === round($totals['credit']);
$balanceDifference = abs($totals['debit'] - $totals['credit']);
?>

<div class="container-fluid px-4 py-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-clipboard-check me-2"></i>Trial <span class="text-emerald-600">Balance</span>
            </h1>
            <p class="text-muted mb-0">Accounting verification and balance validation report</p>
            <small class="text-muted">ERP Tompak Accounting Verification</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
            <a href="<?= base_url('admin/accounting/reports') ?>" class="btn btn-outline-primary shadow-sm">
                <i class="fas fa-chart-bar me-2"></i>All Reports
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card glass-card border-0 shadow-lg mb-4">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>Select Trial Balance Date
            </h5>
        </div>
        <div class="card-body pt-0">
            <form method="get" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">Trial Date</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-calendar text-primary"></i>
                        </span>
                        <input type="date" name="date" value="<?= $date ?>" 
                               class="form-control form-control-lg border-start-0 ps-0 fw-bold">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-gradient shadow-sm w-100">
                        <i class="fas fa-filter me-1"></i>Generate
                    </button>
                </div>
                <div class="col-lg-6 d-flex align-items-end justify-content-end">
                    <div class="text-end">
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                            <i class="fas fa-calendar-day me-1"></i>
                            Trial as of <?= date('F d, Y', strtotime($date)) ?>
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill ms-2">
                            <i class="fas fa-database me-1"></i>
                            <?= count($data) ?> Accounts
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4" id="debitCard">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fas fa-arrow-down fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-primary">Rp <?= number_format($totals['debit'], 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Debit</p>
                            <?php if($totalAmount > 0): ?>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $debitPercent ?>%" 
                                         role="progressbar" aria-valuenow="<?= $debitPercent ?>" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?= $debitPercent ?>% of total</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4" id="creditCard">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-emerald bg-opacity-10 text-emerald rounded-circle">
                                <i class="fas fa-arrow-up fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-emerald-600">Rp <?= number_format($totals['credit'], 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Credit</p>
                            <?php if($totalAmount > 0): ?>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-emerald" style="width: <?= $creditPercent ?>%" 
                                         role="progressbar" aria-valuenow="<?= $creditPercent ?>" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?= $creditPercent ?>% of total</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4" id="balanceCard">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg <?= $isBalanced ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 <?= $isBalanced ? 'text-success' : 'text-danger' ?> rounded-circle">
                                <i class="fas <?= $isBalanced ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold <?= $isBalanced ? 'text-success' : 'text-danger' ?>">
                                <?= $isBalanced ? 'BALANCED' : 'UNBALANCED' ?>
                            </h5>
                            <p class="text-muted mb-0">
                                <?php if($isBalanced): ?>
                                    Debit = Credit
                                <?php else: ?>
                                    Difference: Rp <?= number_format($balanceDifference, 0, ',', '.') ?>
                                <?php endif; ?>
                            </p>
                            <?php if(!$isBalanced): ?>
                                <small class="<?= $totals['debit'] > $totals['credit'] ? 'text-primary' : 'text-emerald' ?>">
                                    <?= $totals['debit'] > $totals['credit'] ? 'Debit exceeds Credit' : 'Credit exceeds Debit' ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Visualization -->
    <?php if($totalAmount > 0): ?>
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-chart-pie me-2 text-primary"></i>Debit vs Credit Comparison
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <canvas id="balanceChart" height="200"></canvas>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-light p-4 rounded border-start border-primary border-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Debit Total</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary"><?= $debitPercent ?>%</span>
                                </div>
                                <h4 class="fw-bold text-primary mb-0">Rp <?= number_format($totals['debit'], 0, ',', '.') ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-4 rounded border-start border-emerald border-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Credit Total</span>
                                    <span class="badge bg-emerald bg-opacity-10 text-emerald"><?= $creditPercent ?>%</span>
                                </div>
                                <h4 class="fw-bold text-emerald-600 mb-0">Rp <?= number_format($totals['credit'], 0, ',', '.') ?></h4>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light p-4 rounded border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Balance Status</span>
                                    <span class="badge <?= $isBalanced ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $isBalanced ? 'Balanced' : 'Unbalanced' ?>
                                    </span>
                                </div>
                                <div class="fw-bold <?= $isBalanced ? 'text-success' : 'text-danger' ?>">
                                    <?php if($isBalanced): ?>
                                        <i class="fas fa-check-circle me-2"></i>Trial Balance is Correct
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Difference: Rp <?= number_format($balanceDifference, 0, ',', '.') ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2 text-primary"></i>Trial Balance Details
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-list-alt me-1"></i>
                    <?= count($data) ?> Accounts
                </span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0" style="width: 150px;">
                                <span class="text-muted small fw-bold text-uppercase">
                                    <i class="fas fa-hashtag me-1"></i>Account Code
                                </span>
                            </th>
                            <th class="py-3 border-0">
                                <span class="text-muted small fw-bold text-uppercase">
                                    <i class="fas fa-book me-1"></i>Account Description
                                </span>
                            </th>
                            <th class="py-3 border-0 text-end" style="width: 200px;">
                                <span class="text-muted small fw-bold text-uppercase">
                                    <i class="fas fa-arrow-down me-1"></i>Debit (Rp)
                                </span>
                            </th>
                            <th class="pe-4 py-3 border-0 text-end" style="width: 200px;">
                                <span class="text-muted small fw-bold text-uppercase">
                                    <i class="fas fa-arrow-up me-1"></i>Credit (Rp)
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon mb-3">
                                            <i class="fas fa-clipboard-list fa-3x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">No trial balance data found</h5>
                                        <p class="text-muted mb-4">Try adjusting your date filter or post some journal entries</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($data as $row): ?>
                                <tr class="account-row">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="account-icon me-3">
                                                <div class="avatar avatar-xs bg-slate bg-opacity-10 text-slate rounded-circle">
                                                    <i class="fas fa-hashtag fa-xs"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <code class="font-mono fw-bold text-slate-700"><?= $row['code'] ?></code>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-slate-800"><?= $row['name'] ?></div>
                                        <?php if(isset($row['type'])): ?>
                                            <small class="text-muted"><?= ucfirst($row['type']) ?> Account</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-end">
                                        <?php if($row['debit'] > 0): ?>
                                            <div class="debit-amount">
                                                <span class="fw-bold text-primary">
                                                    Rp <?= number_format($row['debit'], 0, ',', '.') ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <?php if($row['credit'] > 0): ?>
                                            <div class="credit-amount">
                                                <span class="fw-bold text-emerald-600">
                                                    Rp <?= number_format($row['credit'], 0, ',', '.') ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    
                    <!-- Footer dengan total -->
                    <?php if(!empty($data)): ?>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td class="ps-4 py-3 border-top" colspan="2">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                            <i class="fas fa-calculator"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-slate-700 text-uppercase small">Trial Balance Summary</div>
                                            <small class="text-muted">Totals must balance for valid accounting</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-end border-top">
                                    <div class="total-section">
                                        <div class="text-muted small mb-1">Total Debit</div>
                                        <h4 class="fw-bold text-primary mb-0">
                                            Rp <?= number_format($totals['debit'], 0, ',', '.') ?>
                                        </h4>
                                    </div>
                                </td>
                                <td class="pe-4 py-3 text-end border-top">
                                    <div class="total-section">
                                        <div class="text-muted small mb-1">Total Credit</div>
                                        <h4 class="fw-bold text-emerald-600 mb-0">
                                            Rp <?= number_format($totals['credit'], 0, ',', '.') ?>
                                        </h4>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?= $isBalanced ? 'bg-success bg-opacity-5' : 'bg-danger bg-opacity-5' ?>">
                                <td class="ps-4 py-3" colspan="2">
                                    <div class="fw-bold <?= $isBalanced ? 'text-success' : 'text-danger' ?>">
                                        <i class="fas <?= $isBalanced ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i>
                                        <?= $isBalanced ? 'Trial Balance Verified' : 'Trial Balance Unbalanced' ?>
                                    </div>
                                </td>
                                <td class="py-3 text-end" colspan="2">
                                    <span class="fw-bold <?= $isBalanced ? 'text-success' : 'text-danger' ?>">
                                        <?php if($isBalanced): ?>
                                            <i class="fas fa-check-double me-2"></i>DEBIT = CREDIT
                                        <?php else: ?>
                                            Difference: Rp <?= number_format($balanceDifference, 0, ',', '.') ?>
                                            <span class="text-muted small ms-2">
                                                (<?= $totals['debit'] > $totals['credit'] ? 'Debit > Credit' : 'Credit > Debit' ?>)
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Report Information -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>Report Information
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Trial Date:</span>
                            <span class="fw-bold"><?= date('F d, Y', strtotime($date)) ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Generated:</span>
                            <span class="fw-bold"><?= date('M d, Y H:i') ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Total Accounts:</span>
                            <span class="fw-bold"><?= count($data) ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Accounts with Debit:</span>
                            <span class="fw-bold">
                                <?= count(array_filter($data, function($row) { return $row['debit'] > 0; })) ?>
                            </span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Accounts with Credit:</span>
                            <span class="fw-bold">
                                <?= count(array_filter($data, function($row) { return $row['credit'] > 0; })) ?>
                            </span>
                        </li>
                        <li class="mb-0 d-flex justify-content-between">
                            <span class="text-muted">Balance Status:</span>
                            <span class="badge <?= $isBalanced ? 'bg-success' : 'bg-danger' ?>">
                                <?= $isBalanced ? 'Balanced' : 'Unbalanced' ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>About Trial Balance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded h-100">
                                <h6 class="fw-bold text-primary mb-2">
                                    <i class="fas fa-check-circle me-2"></i>Purpose
                                </h6>
                                <p class="small text-muted mb-0">
                                    The Trial Balance verifies that total debits equal total credits in the accounting system.
                                    It's a key step in preparing financial statements.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded h-100">
                                <h6 class="fw-bold text-emerald-600 mb-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>What if Unbalanced?
                                </h6>
                                <p class="small text-muted mb-0">
                                    An unbalanced trial balance indicates errors in journal entries, 
                                    such as incorrect amounts, missing entries, or wrong account postings.
                                </p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert <?= $isBalanced ? 'alert-success' : 'alert-danger' ?> mb-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas <?= $isBalanced ? 'fa-thumbs-up' : 'fa-thumbs-down' ?> fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">
                                            <?= $isBalanced ? 'Trial Balance is Correct!' : 'Trial Balance Requires Attention!' ?>
                                        </h6>
                                        <p class="mb-0 small">
                                            <?php if($isBalanced): ?>
                                                All debits and credits are properly balanced. You can proceed with financial statement preparation.
                                            <?php else: ?>
                                                Please review journal entries for the period ending <?= date('F d, Y', strtotime($date)) ?>.
                                                Check for missing entries or incorrect amounts.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
}

.page-title.text-gradient {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.btn-gradient {
    background: var(--primary-gradient);
    border: none;
    color: white;
    transition: all 0.3s ease;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}

.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-xs {
    width: 28px;
    height: 28px;
}

.avatar-sm {
    width: 36px;
    height: 36px;
}

.card-hover {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.account-row {
    transition: all 0.2s ease;
}

.account-row:hover {
    background-color: rgba(0,0,0,0.02);
}

.bg-emerald {
    background-color: #10b981 !important;
}

.text-emerald {
    color: #10b981 !important;
}

.debit-amount, .credit-amount {
    font-family: 'Courier New', monospace;
}

.tabular-nums {
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state-icon {
    opacity: 0.5;
}

.total-section {
    min-width: 150px;
}

.table tfoot tr:first-child td {
    border-top: 2px solid #e2e8f0 !important;
}

.border-4 {
    border-width: 4px !important;
}

.progress {
    border-radius: 3px;
}

@media print {
    .btn, .form-control, .input-group, .card-hover:hover {
        box-shadow: none !important;
        transform: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .no-print {
        display: none !important;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if($totalAmount > 0): ?>
    // Pie Chart untuk perbandingan Debit vs Credit
    const balanceCtx = document.getElementById('balanceChart').getContext('2d');
    const debitPercent = <?= $debitPercent ?>;
    const creditPercent = <?= $creditPercent ?>;
    
    new Chart(balanceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Debit', 'Credit'],
            datasets: [{
                data: [debitPercent, creditPercent],
                backgroundColor: ['#3b82f6', '#10b981'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12,
                            family: 'system-ui'
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += context.parsed + '%';
                            }
                            return label;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
    <?php endif; ?>
    
    // Print functionality
    document.querySelector('button[onclick="window.print()"]').addEventListener('click', function() {
        window.print();
    });
    
    // Auto-refresh date input
    const dateInput = document.querySelector('input[name="date"]');
    if (!dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Highlight rows based on amounts
    document.querySelectorAll('.account-row').forEach(row => {
        const debit = row.querySelector('.debit-amount');
        const credit = row.querySelector('.credit-amount');
        
        if (debit) {
            row.classList.add('has-debit');
        }
        if (credit) {
            row.classList.add('has-credit');
        }
    });
});
</script>

<?= $this->endSection() ?>