<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php 
// Hitung persentase dengan penanganan pembagian dengan nol
$totalAll = $totals['assets'] + $totals['liabilities'] + $totals['equity'];
$assetsPercent = $totalAll > 0 ? round(($totals['assets'] / $totalAll) * 100) : 0;
$liabilitiesPercent = $totalAll > 0 ? round(($totals['liabilities'] / $totalAll) * 100) : 0;
$equityPercent = $totalAll > 0 ? round(($totals['equity'] / $totalAll) * 100) : 0;

// Pastikan total persentase = 100 (jika ada pembulatan)
if ($totalAll > 0 && ($assetsPercent + $liabilitiesPercent + $equityPercent) !== 100) {
    $assetsPercent = 100 - $liabilitiesPercent - $equityPercent; // Adjust untuk memastikan total 100%
}
?>

<div class="container-fluid px-4 py-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-balance-scale me-2"></i>Balance <span class="text-emerald-600">Sheet</span>
            </h1>
            <p class="text-muted mb-0">Financial position statement as of selected date</p>
            <small class="text-muted">ERP Tompak Financial Reporting</small>
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
                <i class="fas fa-calendar-alt me-2 text-primary"></i>Select Reporting Date
            </h5>
        </div>
        <div class="card-body pt-0">
            <form method="get" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">Report Date</label>
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
                            As of <?= date('F d, Y', strtotime($date)) ?>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards untuk Balance Summary -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fas fa-building fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-primary">Rp <?= number_format($totals['assets'], 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Assets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-warning bg-opacity-10 text-warning rounded-circle">
                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-warning">Rp <?= number_format($totals['liabilities'], 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Liabilities</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-emerald bg-opacity-10 text-emerald rounded-circle">
                                <i class="fas fa-chart-line fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-emerald-600">Rp <?= number_format($totals['equity'], 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Equity</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Sheet Main Card -->
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-contract me-2 text-primary"></i>Balance Sheet
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-file-alt me-1"></i>
                    As of <?= date('M d, Y', strtotime($date)) ?>
                </span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Assets Column -->
                <div class="col-lg-6 border-end">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0 text-primary fw-bold">
                                <i class="fas fa-building me-2"></i>Assets / Aktiva
                            </h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                <?php
                                $assetCount = 0;
                                foreach($report['assets'] as $accounts) {
                                    $assetCount += count($accounts);
                                }
                                echo $assetCount . ' Accounts';
                                ?>
                            </span>
                        </div>
                        
                        <?php foreach($report['assets'] as $subgroup => $accounts): ?>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <h6 class="mb-0 text-uppercase fw-bold text-slate-700 small">
                                        <i class="fas fa-folder me-2 text-primary"></i><?= $subgroup ?>
                                    </h6>
                                    <span class="badge bg-light text-dark">
                                        <?= count($accounts) ?> items
                                    </span>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <?php foreach($accounts as $acc): ?>
                                                <tr class="account-row">
                                                    <td class="ps-3 py-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="account-icon me-3">
                                                                <div class="avatar avatar-xs bg-primary bg-opacity-10 text-primary rounded-circle">
                                                                    <i class="fas fa-<?= isset($acc['type']) && $acc['type'] == 'current' ? 'money-bill-wave' : 'building' ?> fa-xs"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-slate-800 small"><?= $acc['name'] ?></div>
                                                                <?php if(isset($acc['code'])): ?>
                                                                    <div class="text-muted" style="font-size: 0.7rem;"><?= $acc['code'] ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-3 py-2">
                                                        <div class="fw-bold tabular-nums text-primary">
                                                            Rp <?= number_format($acc['balance'], 0, ',', '.') ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Total Assets -->
                        <div class="mt-4 pt-4 border-top border-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold text-slate-800">Total Assets</h5>
                                    <small class="text-muted">Total semua aktiva</small>
                                </div>
                                <div class="text-end">
                                    <h3 class="mb-0 fw-bold text-primary">Rp <?= number_format($totals['assets'], 0, ',', '.') ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Liabilities & Equity Column -->
                <div class="col-lg-6">
                    <div class="p-4 bg-slate-50 min-h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0 text-warning fw-bold">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Liabilities & Equity / Pasiva
                            </h5>
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                <?php
                                $liabCount = 0;
                                $equityCount = 0;
                                foreach($report['liabilities'] as $accounts) $liabCount += count($accounts);
                                foreach($report['equity'] as $accounts) $equityCount += count($accounts);
                                echo ($liabCount + $equityCount) . ' Accounts';
                                ?>
                            </span>
                        </div>
                        
                        <!-- Liabilities Section -->
                        <div class="mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="mb-0 text-uppercase fw-bold text-slate-700 small">
                                    <i class="fas fa-hand-holding-usd me-2 text-warning"></i>Liabilities / Kewajiban
                                </h6>
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    Rp <?= number_format($totals['liabilities'], 0, ',', '.') ?>
                                </span>
                            </div>
                            
                            <?php foreach($report['liabilities'] as $subgroup => $accounts): ?>
                                <div class="mb-3">
                                    <h6 class="mb-2 text-slate-700 fw-bold small"><?= $subgroup ?></h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                <?php foreach($accounts as $acc): ?>
                                                    <tr class="account-row">
                                                        <td class="ps-3 py-2">
                                                            <div class="d-flex align-items-center">
                                                                <div class="account-icon me-3">
                                                                    <div class="avatar avatar-xs bg-warning bg-opacity-10 text-warning rounded-circle">
                                                                        <i class="fas fa-file-invoice fa-xs"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="fw-bold text-slate-800 small"><?= $acc['name'] ?></div>
                                                            </div>
                                                        </td>
                                                        <td class="text-end pe-3 py-2">
                                                            <div class="fw-bold tabular-nums text-warning">
                                                                Rp <?= number_format($acc['balance'], 0, ',', '.') ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Equity Section -->
                        <div class="mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="mb-0 text-uppercase fw-bold text-slate-700 small">
                                    <i class="fas fa-user-tie me-2 text-emerald"></i>Equity / Ekuitas
                                </h6>
                                <span class="badge bg-emerald bg-opacity-10 text-emerald">
                                    Rp <?= number_format($totals['equity'], 0, ',', '.') ?>
                                </span>
                            </div>
                            
                            <?php foreach($report['equity'] as $subgroup => $accounts): ?>
                                <div class="mb-3">
                                    <h6 class="mb-2 text-slate-700 fw-bold small"><?= $subgroup ?></h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                <?php foreach($accounts as $acc): ?>
                                                    <tr class="account-row">
                                                        <td class="ps-3 py-2">
                                                            <div class="d-flex align-items-center">
                                                                <div class="account-icon me-3">
                                                                    <div class="avatar avatar-xs bg-emerald bg-opacity-10 text-emerald rounded-circle">
                                                                        <i class="fas fa-chart-line fa-xs"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="fw-bold text-slate-800 small"><?= $acc['name'] ?></div>
                                                            </div>
                                                        </td>
                                                        <td class="text-end pe-3 py-2">
                                                            <div class="fw-bold tabular-nums text-emerald-600">
                                                                Rp <?= number_format($acc['balance'], 0, ',', '.') ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Total Liabilities & Equity -->
                        <div class="mt-4 pt-4 border-top border-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold text-slate-800">Total Liabilities & Equity</h5>
                                    <small class="text-muted">Total kewajiban dan ekuitas</small>
                                </div>
                                <div class="text-end">
                                    <h3 class="mb-0 fw-bold text-slate-900">
                                        Rp <?= number_format($totals['liabilities'] + $totals['equity'], 0, ',', '.') ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Balance Status Footer -->
        <?php 
        $balanceDifference = $totals['assets'] - ($totals['liabilities'] + $totals['equity']);
        $isBalanced = round($balanceDifference) === 0;
        ?>
        <div class="card-footer bg-white py-3 border-top <?= $isBalanced ? 'bg-success bg-opacity-5' : 'bg-danger bg-opacity-5' ?>">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-lg <?= $isBalanced ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 <?= $isBalanced ? 'text-success' : 'text-danger' ?> rounded-circle">
                                <i class="fas <?= $isBalanced ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 <?= $isBalanced ? 'text-success' : 'text-danger' ?> fw-bold">
                                <?= $isBalanced ? 'BALANCE SHEET IS BALANCED' : 'BALANCE SHEET IS UNBALANCED' ?>
                            </h5>
                            <p class="mb-0 text-muted">
                                <?php if($isBalanced): ?>
                                    Assets = Liabilities + Equity (Rp <?= number_format($totals['assets'], 0, ',', '.') ?>)
                                <?php else: ?>
                                    Difference: Rp <?= number_format(abs($balanceDifference), 0, ',', '.') ?>
                                    (<?= $balanceDifference > 0 ? 'Assets exceed Liabilities & Equity' : 'Liabilities & Equity exceed Assets' ?>)
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="balance-summary">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded text-center">
                                    <div class="small text-muted">Assets</div>
                                    <div class="fw-bold">Rp <?= number_format($totals['assets'], 0, ',', '.') ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded text-center">
                                    <div class="small text-muted">Liabilities + Equity</div>
                                    <div class="fw-bold">Rp <?= number_format($totals['liabilities'] + $totals['equity'], 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row mb-4">
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
                            <span class="text-muted">Report Date:</span>
                            <span class="fw-bold"><?= date('F d, Y', strtotime($date)) ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Generated:</span>
                            <span class="fw-bold"><?= date('M d, Y H:i') ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Total Accounts:</span>
                            <span class="fw-bold">
                                <?= $assetCount + $liabCount + $equityCount ?>
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
                        <i class="fas fa-chart-pie me-2 text-primary"></i>Balance Composition
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="position-relative d-inline-block mb-3">
                                    <canvas id="assetsChart" width="120" height="120"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <div class="fw-bold text-primary"><?= $assetsPercent ?>%</div>
                                    </div>
                                </div>
                                <h6 class="mb-1">Assets</h6>
                                <p class="text-muted small mb-0">Rp <?= number_format($totals['assets'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="position-relative d-inline-block mb-3">
                                    <canvas id="liabilitiesChart" width="120" height="120"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <div class="fw-bold text-warning"><?= $liabilitiesPercent ?>%</div>
                                    </div>
                                </div>
                                <h6 class="mb-1">Liabilities</h6>
                                <p class="text-muted small mb-0">Rp <?= number_format($totals['liabilities'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="position-relative d-inline-block mb-3">
                                    <canvas id="equityChart" width="120" height="120"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <div class="fw-bold text-emerald"><?= $equityPercent ?>%</div>
                                    </div>
                                </div>
                                <h6 class="mb-1">Equity</h6>
                                <p class="text-muted small mb-0">Rp <?= number_format($totals['equity'], 0, ',', '.') ?></p>
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
    transform: translateX(5px);
}

.bg-emerald {
    background-color: #10b981 !important;
}

.text-emerald {
    color: #10b981 !important;
}

.border-top-2 {
    border-top-width: 2px !important;
}

.min-h-100 {
    min-height: 100%;
}

.table-sm td {
    padding: 0.5rem 0.25rem;
}

.tabular-nums {
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
}

.balance-summary .col-6:nth-child(1) .bg-light {
    border-left: 4px solid #3b82f6;
}

.balance-summary .col-6:nth-child(2) .bg-light {
    border-left: 4px solid #f59e0b;
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
    // Donut Charts for Balance Composition
    const assetsCtx = document.getElementById('assetsChart').getContext('2d');
    const liabilitiesCtx = document.getElementById('liabilitiesChart').getContext('2d');
    const equityCtx = document.getElementById('equityChart').getContext('2d');
    
    const assetsPercent = <?= $assetsPercent ?>;
    const liabilitiesPercent = <?= $liabilitiesPercent ?>;
    const equityPercent = <?= $equityPercent ?>;
    
    const chartOptions = {
        cutout: '70%',
        responsive: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                enabled: false
            }
        }
    };
    
    // Assets Chart
    if (assetsPercent >= 0) {
        new Chart(assetsCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [assetsPercent, Math.max(0, 100 - assetsPercent)],
                    backgroundColor: ['#3b82f6', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: chartOptions
        });
    }
    
    // Liabilities Chart
    if (liabilitiesPercent >= 0) {
        new Chart(liabilitiesCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [liabilitiesPercent, Math.max(0, 100 - liabilitiesPercent)],
                    backgroundColor: ['#f59e0b', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: chartOptions
        });
    }
    
    // Equity Chart
    if (equityPercent >= 0) {
        new Chart(equityCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [equityPercent, Math.max(0, 100 - equityPercent)],
                    backgroundColor: ['#10b981', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: chartOptions
        });
    }
    
    // Print functionality
    document.querySelector('button[onclick="window.print()"]').addEventListener('click', function() {
        window.print();
    });
    
    // Auto-refresh date input
    const dateInput = document.querySelector('input[name="date"]');
    if (!dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>

<?= $this->endSection() ?>