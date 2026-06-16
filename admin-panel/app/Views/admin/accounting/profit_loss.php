<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
// Hitung metrik utama dengan penanganan null
$revenue = $totals['revenue'] ?? 0;
$cogs = $totals['cogs'] ?? 0;
$opex = $totals['opex'] ?? 0;
$other = $totals['other'] ?? 0;

// Hitung profit metrics
$grossProfit = $revenue - $cogs;
$ebitda = $grossProfit - $opex;
$netProfit = $ebitda + ($other ?? 0);

// Hitung margin persentase dengan penanganan division by zero
$grossMargin = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0;
$operatingMargin = $revenue > 0 ? round(($ebitda / $revenue) * 100, 1) : 0;
$netMargin = $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0;

// Tentukan apakah profit atau loss
$isProfitable = $netProfit >= 0;
?>

<div class="container-fluid px-4 py-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-chart-line me-2"></i>Laporan <span class="text-emerald-600">Laba Rugi</span>
            </h1>
            <p class="text-muted mb-0">Laporan profit & loss untuk periode yang dipilih</p>
            <small class="text-muted">ERP Tompak Financial Reporting</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
            <a href="<?= base_url('admin/accounting/reports') ?>" class="btn btn-outline-primary shadow-sm">
                <i class="fas fa-chart-bar me-2"></i>Semua Laporan
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card glass-card border-0 shadow-lg mb-4">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0">
                <i class="fas fa-calendar-range me-2 text-primary"></i>Pilih Periode Laporan
            </h5>
        </div>
        <div class="card-body pt-0">
            <form method="get" class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">Tanggal Mulai</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-calendar-alt text-primary"></i>
                        </span>
                        <input type="date" name="start" value="<?= $start ?>" 
                               class="form-control form-control-lg border-start-0 ps-0 fw-bold">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">Tanggal Akhir</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-calendar-alt text-primary"></i>
                        </span>
                        <input type="date" name="end" value="<?= $end ?>" 
                               class="form-control form-control-lg border-start-0 ps-0 fw-bold">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-gradient shadow-sm w-100">
                        <i class="fas fa-filter me-1"></i>Generate
                    </button>
                </div>
                <div class="col-lg-4 d-flex align-items-end justify-content-end">
                    <div class="text-end">
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                            <i class="fas fa-calendar-week me-1"></i>
                            <?= date('d M Y', strtotime($start)) ?> - <?= date('d M Y', strtotime($end)) ?>
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill ms-2">
                            <i class="fas fa-clock me-1"></i>
                            <?= round((strtotime($end) - strtotime($start)) / (60 * 60 * 24)) ?> Hari
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-primary">Rp <?= number_format($revenue, 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Pendapatan</p>
                            <?php if($revenue > 0): ?>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 100%" 
                                         role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">100% baseline</small>
                            <?php endif; ?>
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
                            <div class="avatar avatar-lg bg-success bg-opacity-10 text-success rounded-circle">
                                <i class="fas fa-chart-bar fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-success"><?= $grossMargin ?>%</h5>
                            <p class="text-muted mb-0">Margin Kotor</p>
                            <?php if($revenue > 0): ?>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: <?= min($grossMargin, 100) ?>%" 
                                         role="progressbar" aria-valuenow="<?= $grossMargin ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">Gross Profit Margin</small>
                            <?php endif; ?>
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
                            <div class="avatar avatar-lg bg-warning bg-opacity-10 text-warning rounded-circle">
                                <i class="fas fa-cogs fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-warning"><?= $operatingMargin ?>%</h5>
                            <p class="text-muted mb-0">Margin Operasional</p>
                            <?php if($revenue > 0): ?>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: <?= min(abs($operatingMargin), 100) ?>%" 
                                         role="progressbar" aria-valuenow="<?= $operatingMargin ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">EBITDA Margin</small>
                            <?php endif; ?>
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
                            <div class="avatar avatar-lg <?= $isProfitable ? 'bg-emerald' : 'bg-danger' ?> bg-opacity-10 <?= $isProfitable ? 'text-emerald' : 'text-danger' ?> rounded-circle">
                                <i class="fas <?= $isProfitable ? 'fa-trophy' : 'fa-exclamation-triangle' ?> fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold <?= $isProfitable ? 'text-emerald-600' : 'text-danger' ?>">
                                <?= $isProfitable ? '+' : '-' ?>Rp <?= number_format(abs($netProfit), 0, ',', '.') ?>
                            </h5>
                            <p class="text-muted mb-0">Laba <?= $isProfitable ? 'Bersih' : 'Rugi' ?></p>
                            <?php if($revenue > 0): ?>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar <?= $isProfitable ? 'bg-emerald' : 'bg-danger' ?>" 
                                         style="width: <?= min(abs($netMargin), 100) ?>%" 
                                         role="progressbar" aria-valuenow="<?= $netMargin ?>" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?= $netMargin ?>% margin</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Statement Main Card -->
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Laporan Laba Rugi (Profit & Loss)
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-chart-line me-1"></i>
                    Periode: <?= date('d M Y', strtotime($start)) ?> - <?= date('d M Y', strtotime($end)) ?>
                </span>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Pendapatan Operasional Section -->
            <div class="income-section mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="mb-1 text-primary fw-bold">
                            <i class="fas fa-money-bill-wave me-2"></i>Pendapatan Operasional
                        </h5>
                        <small class="text-muted">Pendapatan dari aktivitas bisnis operasional</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                        Rp <?= number_format($revenue, 0, ',', '.') ?>
                    </span>
                </div>
                
                <?php if(!empty($report['revenue'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <?php foreach($report['revenue'] as $item): 
                                    $itemAmount = $item['val'] ?? 0;
                                    $itemPercent = $revenue > 0 ? round(($itemAmount / $revenue) * 100, 1) : 0;
                                ?>
                                    <tr class="income-row">
                                        <td class="ps-3 py-2" style="width: 60%;">
                                            <div class="d-flex align-items-center">
                                                <div class="account-icon me-3">
                                                    <div class="avatar avatar-xs bg-primary bg-opacity-10 text-primary rounded-circle">
                                                        <i class="fas fa-arrow-up fa-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="fw-bold text-slate-800 small"><?= $item['name'] ?></div>
                                            </div>
                                        </td>
                                        <td class="py-2 text-center" style="width: 20%;">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-primary" style="width: <?= $itemPercent ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $itemPercent ?>%</small>
                                        </td>
                                        <td class="pe-3 py-2 text-end" style="width: 20%;">
                                            <div class="fw-bold text-primary">
                                                Rp <?= number_format($itemAmount, 0, ',', '.') ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-chart-line fa-2x mb-3 opacity-25"></i>
                        <p>Tidak ada data pendapatan untuk periode ini</p>
                    </div>
                <?php endif; ?>
                
                <!-- Total Pendapatan -->
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold text-slate-800">TOTAL PENDAPATAN</h5>
                            <small class="text-muted">Jumlah semua pendapatan operasional</small>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 fw-bold text-primary">Rp <?= number_format($revenue, 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HPP Section -->
            <div class="income-section mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="mb-1 text-danger fw-bold">
                            <i class="fas fa-box-open me-2"></i>Harga Pokok Penjualan (HPP)
                        </h5>
                        <small class="text-muted">Biaya langsung yang terkait dengan produksi</small>
                    </div>
                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                        Rp <?= number_format($cogs, 0, ',', '.') ?>
                    </span>
                </div>
                
                <?php if(!empty($report['cogs'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <?php foreach($report['cogs'] as $item): 
                                    $itemAmount = $item['val'] ?? 0;
                                    $itemPercent = $revenue > 0 ? round(($itemAmount / $revenue) * 100, 1) : 0;
                                ?>
                                    <tr class="income-row">
                                        <td class="ps-3 py-2" style="width: 60%;">
                                            <div class="d-flex align-items-center">
                                                <div class="account-icon me-3">
                                                    <div class="avatar avatar-xs bg-danger bg-opacity-10 text-danger rounded-circle">
                                                        <i class="fas fa-box fa-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="fw-bold text-slate-800 small"><?= $item['name'] ?></div>
                                            </div>
                                        </td>
                                        <td class="py-2 text-center" style="width: 20%;">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-danger" style="width: <?= $itemPercent ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $itemPercent ?>% dari pendapatan</small>
                                        </td>
                                        <td class="pe-3 py-2 text-end" style="width: 20%;">
                                            <div class="fw-bold text-danger">
                                                (Rp <?= number_format($itemAmount, 0, ',', '.') ?>)
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Gross Profit -->
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center bg-slate-50 p-4 rounded-3">
                        <div>
                            <h5 class="mb-0 fw-bold text-slate-800">LABA KOTOR (GROSS PROFIT)</h5>
                            <small class="text-muted">Pendapatan - HPP</small>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 fw-bold text-success">Rp <?= number_format($grossProfit, 0, ',', '.') ?></h3>
                            <small class="text-success">
                                <i class="fas fa-percentage me-1"></i><?= $grossMargin ?>% Margin Kotor
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Beban Operasional Section -->
            <div class="income-section mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="mb-1 text-warning fw-bold">
                            <i class="fas fa-cogs me-2"></i>Beban Operasional (OPEX)
                        </h5>
                        <small class="text-muted">Biaya operasional sehari-hari bisnis</small>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                        Rp <?= number_format($opex, 0, ',', '.') ?>
                    </span>
                </div>
                
                <?php if(!empty($report['operating_exp'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <?php foreach($report['operating_exp'] as $item): 
                                    $itemAmount = $item['val'] ?? 0;
                                    $itemPercent = $revenue > 0 ? round(($itemAmount / $revenue) * 100, 1) : 0;
                                ?>
                                    <tr class="income-row">
                                        <td class="ps-3 py-2" style="width: 60%;">
                                            <div class="d-flex align-items-center">
                                                <div class="account-icon me-3">
                                                    <div class="avatar avatar-xs bg-warning bg-opacity-10 text-warning rounded-circle">
                                                        <i class="fas fa-receipt fa-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="fw-bold text-slate-800 small"><?= $item['name'] ?></div>
                                            </div>
                                        </td>
                                        <td class="py-2 text-center" style="width: 20%;">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: <?= $itemPercent ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $itemPercent ?>% dari pendapatan</small>
                                        </td>
                                        <td class="pe-3 py-2 text-end" style="width: 20%;">
                                            <div class="fw-bold text-warning">
                                                (Rp <?= number_format($itemAmount, 0, ',', '.') ?>)
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- EBITDA -->
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center bg-emerald-600 text-white p-4 rounded-3 shadow-lg">
                        <div>
                            <h5 class="mb-0 fw-bold">EBITDA</h5>
                            <small>Earnings Before Interest, Taxes, Depreciation & Amortization</small>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 fw-bold">Rp <?= number_format($ebitda, 0, ',', '.') ?></h3>
                            <small>
                                <i class="fas fa-percentage me-1"></i><?= $operatingMargin ?>% Margin Operasional
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Items Section -->
            <?php if(isset($totals['other']) && $totals['other'] != 0): ?>
            <div class="income-section mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="mb-1 text-info fw-bold">
                            <i class="fas fa-exchange-alt me-2"></i>Pendapatan/Beban Lainnya
                        </h5>
                        <small class="text-muted">Pendapatan atau beban non-operasional</small>
                    </div>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                        <?= $other >= 0 ? '+' : '' ?>Rp <?= number_format(abs($other), 0, ',', '.') ?>
                    </span>
                </div>
                
                <div class="bg-light p-4 rounded">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="fw-bold text-slate-700">
                                <?= $other >= 0 ? 'Pendapatan' : 'Beban' ?> Lainnya
                            </div>
                            <small class="text-muted">
                                <?= $other >= 0 ? 'Pendapatan tambahan di luar operasional utama' : 'Beban tambahan di luar operasional utama' ?>
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <h4 class="mb-0 fw-bold <?= $other >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $other >= 0 ? '+' : '-' ?>Rp <?= number_format(abs($other), 0, ',', '.') ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Net Profit/Loss Section -->
            <div class="income-section">
                <div class="<?= $isProfitable ? 'bg-emerald bg-opacity-10' : 'bg-danger bg-opacity-10' ?> p-5 rounded-3 border <?= $isProfitable ? 'border-emerald border-opacity-25' : 'border-danger border-opacity-25' ?>">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-4">
                                    <div class="avatar avatar-lg <?= $isProfitable ? 'bg-emerald' : 'bg-danger' ?> bg-opacity-25 <?= $isProfitable ? 'text-emerald' : 'text-danger' ?> rounded-circle">
                                        <i class="fas <?= $isProfitable ? 'fa-trophy' : 'fa-exclamation-triangle' ?> fa-2x"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 <?= $isProfitable ? 'text-emerald-600' : 'text-danger' ?> fw-bold">
                                        LABA / (RUGI) BERSIH
                                    </h5>
                                    <p class="mb-0 text-muted">
                                        <?= $isProfitable ? 'Periode menguntungkan!' : 'Periode membutuhkan perhatian!' ?>
                                        <?php if($revenue > 0): ?>
                                            Margin bersih: <?= $netMargin ?>%
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <h1 class="display-5 fw-bold <?= $isProfitable ? 'text-emerald-600' : 'text-danger' ?> mb-0">
                                <?= $isProfitable ? '+' : '-' ?>Rp <?= number_format(abs($netProfit), 0, ',', '.') ?>
                            </h1>
                            <small class="<?= $isProfitable ? 'text-emerald-600' : 'text-danger' ?>">
                                <?= $isProfitable ? 'Laba' : 'Rugi' ?> untuk periode ini
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary Footer -->
        <div class="card-footer bg-white py-3 border-top">
            <div class="row">
                <div class="col-md-6">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        <strong>Ringkasan Laporan:</strong> 
                        <?= $isProfitable ? 'Periode menguntungkan' : 'Periode tidak menguntungkan' ?> 
                        dengan margin bersih <?= $netMargin >= 0 ? '+' : '' ?><?= $netMargin ?>%
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="small">
                        <span class="text-muted me-3">Dibuat: <?= date('d M Y H:i') ?></span>
                        <span class="badge <?= $isProfitable ? 'bg-success' : 'bg-danger' ?>">
                            <?= $isProfitable ? 'Menguntungkan' : 'Tidak Menguntungkan' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Visualization -->
    <?php if($revenue > 0): ?>
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-chart-pie me-2 text-primary"></i>Breakdown Laporan Laba Rugi
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <canvas id="profitLossChart" height="250"></canvas>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-light p-3 rounded border-start border-primary border-4">
                                <div class="text-muted small mb-1">Pendapatan</div>
                                <h5 class="fw-bold text-primary mb-0">Rp <?= number_format($revenue, 0, ',', '.') ?></h5>
                                <small class="text-muted">100% baseline</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-3 rounded border-start border-success border-4">
                                <div class="text-muted small mb-1">Laba Kotor</div>
                                <h5 class="fw-bold text-success mb-0">Rp <?= number_format($grossProfit, 0, ',', '.') ?></h5>
                                <small class="text-success"><?= $grossMargin ?>% margin</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-3 rounded border-start border-danger border-4">
                                <div class="text-muted small mb-1">Total Beban</div>
                                <h5 class="fw-bold text-danger mb-0">Rp <?= number_format($cogs + $opex, 0, ',', '.') ?></h5>
                                <small class="text-muted"><?= round((($cogs + $opex) / $revenue) * 100, 1) ?>% dari pendapatan</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-3 rounded border-start <?= $isProfitable ? 'border-success' : 'border-danger' ?> border-4">
                                <div class="text-muted small mb-1">Laba/Rugi Bersih</div>
                                <h5 class="fw-bold <?= $isProfitable ? 'text-success' : 'text-danger' ?> mb-0">
                                    <?= $isProfitable ? '+' : '-' ?>Rp <?= number_format(abs($netProfit), 0, ',', '.') ?>
                                </h5>
                                <small class="<?= $isProfitable ? 'text-success' : 'text-danger' ?>">
                                    <?= $netMargin ?>% margin
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
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

.avatar-lg {
    width: 64px;
    height: 64px;
}

.card-hover {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.income-row {
    transition: all 0.2s ease;
}

.income-row:hover {
    background-color: rgba(0,0,0,0.02);
}

.income-section {
    padding: 1.5rem;
    border-radius: 12px;
    background-color: #fff;
    margin-bottom: 1.5rem;
    border: 1px solid #e2e8f0;
}

.income-section:last-of-type {
    margin-bottom: 0;
}

.bg-emerald {
    background-color: #10b981 !important;
}

.text-emerald {
    color: #10b981 !important;
}

.border-4 {
    border-width: 4px !important;
}

.progress {
    border-radius: 4px;
}

.display-5 {
    font-size: 2.5rem;
    font-weight: 700;
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
    <?php if($revenue > 0): ?>
    // Bar Chart untuk breakdown Laporan Laba Rugi
    const chartCtx = document.getElementById('profitLossChart').getContext('2d');
    
    // Data untuk chart
    const revenue = <?= $revenue ?>;
    const cogs = <?= $cogs ?>;
    const opex = <?= $opex ?>;
    const other = <?= $other ?? 0 ?>;
    const netProfit = <?= $netProfit ?>;
    
    new Chart(chartCtx, {
        type: 'bar',
        data: {
            labels: ['Pendapatan', 'HPP', 'OPEX', 'Lainnya', 'Laba/Rugi Bersih'],
            datasets: [{
                label: 'Jumlah (Rp)',
                data: [revenue, -cogs, -opex, other, netProfit],
                backgroundColor: [
                    '#3b82f6',  // Pendapatan - blue
                    '#ef4444',  // HPP - red
                    '#f59e0b',  // OPEX - amber
                    '#06b6d4',  // Lainnya - cyan
                    netProfit >= 0 ? '#10b981' : '#ef4444'  // Laba/Rugi Bersih
                ],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                const value = Math.abs(context.parsed.y);
                                const sign = context.parsed.y >= 0 ? 'Rp ' : '-Rp ';
                                label += sign + value.toLocaleString('id-ID');
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + Math.abs(value).toLocaleString('id-ID');
                        }
                    },
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    // Print functionality
    document.querySelector('button[onclick="window.print()"]').addEventListener('click', function() {
        window.print();
    });
    
    // Auto-format date inputs
    const startInput = document.querySelector('input[name="start"]');
    const endInput = document.querySelector('input[name="end"]');
    
    if (!startInput.value) {
        // Set default to first day of current month
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        startInput.value = firstDay.toISOString().split('T')[0];
    }
    
    if (!endInput.value) {
        // Set default to today
        endInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>

<?= $this->endSection() ?>