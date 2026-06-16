<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-chart-line me-2"></i>System Profit & Loss
            </h1>
            <p class="text-muted mb-0">Analisis dampak finansial setiap tipe transaksi terhadap kas sistem</p>
        </div>
        
        <div class="net-profit-card">
            <div class="card border-0 shadow-sm <?= $netProfit >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' ?>">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-lg <?= $netProfit >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' ?> rounded-circle">
                                <i class="fas <?= $netProfit >= 0 ? 'fa-trend-up' : 'fa-trend-down' ?> fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <p class="small text-muted mb-1">Total Laba/Rugi Bersih</p>
                            <h3 class="mb-0 fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                Rp <?= number_format($netProfit, 0, ',', '.') ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Chart Section -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>Visualisasi Dampak Saldo
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-bolt me-1"></i>Real-time Data
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px; position: relative;">
                        <canvas id="impactChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Filter Periode
                    </h5>
                </div>
                <div class="card-body">
                    <form action="" method="get" class="h-100 d-flex flex-column">
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-bold text-muted mb-2">
                                <i class="fas fa-calendar-day me-1"></i>Dari Tanggal
                            </label>
                            <div class="input-group input-group-sm mb-3">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar text-primary"></i>
                                </span>
                                <input type="date" name="date_from" value="<?= $dateFrom ?>" 
                                       class="form-control form-control-sm border-start-0 ps-0">
                            </div>
                            
                            <label class="form-label small text-uppercase fw-bold text-muted mb-2">
                                <i class="fas fa-calendar-check me-1"></i>Sampai Tanggal
                            </label>
                            <div class="input-group input-group-sm mb-4">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar text-primary"></i>
                                </span>
                                <input type="date" name="date_to" value="<?= $dateTo ?>" 
                                       class="form-control form-control-sm border-start-0 ps-0">
                            </div>
                        </div>
                        
                        <div class="mt-auto">
                            <button type="submit" 
                                    class="btn btn-primary w-100 shadow-sm d-flex align-items-center justify-content-center gap-2 py-3">
                                <i class="fas fa-sync-alt"></i>
                                <span>Generate Report</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit/Loss Table -->
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2 text-primary"></i>Analisis Profit & Loss
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <?= count($report) ?> Jenis Transaksi
                </span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0" style="width: 40%;">
                                <span class="text-muted small fw-bold">Tipe Transaksi</span>
                            </th>
                            <th class="py-3 border-0 text-center" style="width: 20%;">
                                <span class="text-muted small fw-bold">Volume</span>
                            </th>
                            <th class="pe-4 py-3 border-0 text-end" style="width: 40%;">
                                <span class="text-muted small fw-bold">Dampak Saldo (Impact)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon mb-3">
                                            <i class="fas fa-chart-pie fa-3x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">Tidak ada data analisis</h5>
                                        <p class="text-muted mb-4">Tidak ada transaksi pada periode yang dipilih</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($report as $row): ?>
                            <?php 
                            $typeName = str_replace('_', ' ', $row->type);
                            $isPositive = $row->saldo_impact > 0;
                            $isNegative = $row->saldo_impact < 0;
                            $isNeutral = $row->saldo_impact == 0;
                            ?>
                            <tr class="profit-loss-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="transaction-icon me-3">
                                            <div class="p-2 rounded-circle <?= $isPositive ? 'bg-success bg-opacity-10' : ($isNegative ? 'bg-danger bg-opacity-10' : 'bg-secondary bg-opacity-10') ?>">
                                                <i class="fas <?= $isPositive ? 'fa-plus text-success' : ($isNegative ? 'fa-minus text-danger' : 'fa-equals text-secondary') ?> fa-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-capitalize">
                                                <?= $typeName ?>
                                            </div>
                                            <div class="small text-muted">
                                                ID: <?= $row->type ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="text-center">
                                    <div class="transaction-volume">
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                            <i class="fas fa-exchange-alt me-1"></i>
                                            <?= $row->total_transaksi ?> TX
                                        </span>
                                    </div>
                                </td>
                                
                                <td class="pe-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <div class="impact-amount me-3">
                                            <div class="fw-bold fs-5 <?= $isPositive ? 'text-success' : ($isNegative ? 'text-danger' : 'text-secondary') ?>">
                                                <?= $row->saldo_impact > 0 ? '+' : '' ?>Rp <?= number_format($row->saldo_impact, 0, ',', '.') ?>
                                            </div>
                                            <?php if ($row->total_transaksi > 0): ?>
                                                <div class="small text-muted">
                                                    Rata: Rp <?= number_format($row->saldo_impact / $row->total_transaksi, 0, ',', '.') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="impact-indicator">
                                            <div class="p-2 rounded-circle <?= $isPositive ? 'bg-success bg-opacity-10' : ($isNegative ? 'bg-danger bg-opacity-10' : 'bg-secondary bg-opacity-10') ?>">
                                                <i class="fas <?= $isPositive ? 'fa-arrow-up text-success' : ($isNegative ? 'fa-arrow-down text-danger' : 'fa-minus text-secondary') ?>"></i>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    
                    <?php if (!empty($report)): ?>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="2" class="ps-4 py-3 fw-bold text-muted">
                                Net Profit/Loss:
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="fw-bold fs-4 <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $netProfit >= 0 ? '+' : '' ?>Rp <?= number_format($netProfit, 0, ',', '.') ?>
                                </div>
                                <div class="small text-muted">
                                    <?= array_sum(array_column($report, 'total_transaksi')) ?> total transaksi
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-lg bg-success bg-opacity-10 text-success rounded-circle">
                                <i class="fas fa-plus fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <?php
                            $positiveCount = count(array_filter($report, function($row) {
                                return $row->saldo_impact > 0;
                            }));
                            $positiveTotal = array_sum(array_filter(array_column($report, 'saldo_impact'), function($impact) {
                                return $impact > 0;
                            }));
                            ?>
                            <h5 class="mb-0 fw-bold text-success">Rp <?= number_format($positiveTotal, 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Positive Impact</p>
                            <div class="mt-2">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <?= $positiveCount ?> kategori
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-lg bg-danger bg-opacity-10 text-danger rounded-circle">
                                <i class="fas fa-minus fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <?php
                            $negativeCount = count(array_filter($report, function($row) {
                                return $row->saldo_impact < 0;
                            }));
                            $negativeTotal = array_sum(array_filter(array_column($report, 'saldo_impact'), function($impact) {
                                return $impact < 0;
                            }));
                            ?>
                            <h5 class="mb-0 fw-bold text-danger">Rp <?= number_format($negativeTotal, 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total Negative Impact</p>
                            <div class="mt-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    <?= $negativeCount ?> kategori
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-lg bg-secondary bg-opacity-10 text-secondary rounded-circle">
                                <i class="fas fa-balance-scale fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $netProfit >= 0 ? '+' : '' ?>Rp <?= number_format($netProfit, 0, ',', '.') ?>
                            </h5>
                            <p class="text-muted mb-0">Net Balance Impact</p>
                            <div class="mt-2">
                                <span class="badge <?= $netProfit >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' ?>">
                                    <?= $netProfit >= 0 ? 'Laba' : 'Rugi' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Interpretasi Laporan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3">
                        <div class="d-flex">
                            <i class="fas fa-lightbulb fa-lg text-info mt-1 me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-2">Understanding Profit & Loss</h6>
                                <p class="mb-0 small">
                                    Laporan ini menunjukkan bagaimana setiap tipe transaksi mempengaruhi saldo sistem.
                                    <strong>Impact positif</strong> menambah kas sistem, sementara <strong>impact negatif</strong> mengurangi kas.
                                    Hasil akhir menentukan apakah sistem mengalami profit atau loss.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar me-2 text-primary"></i>Periode Analisis
                    </h6>
                </div>
                <div class="card-body">
                    <div class="period-info">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Rentang Waktu:</span>
                            <span class="fw-bold">
                                <?php if ($dateFrom && $dateTo): ?>
                                    <?= date('d M Y', strtotime($dateFrom)) ?> - <?= date('d M Y', strtotime($dateTo)) ?>
                                <?php else: ?>
                                    Semua waktu
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Analisis Terakhir:</span>
                            <span class="fw-bold">
                                <?= date('d M Y H:i') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart.js Implementation
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('impactChart').getContext('2d');
    
    // Data from PHP
    const labels = <?= $chartLabels ?>;
    const dataValues = <?= $chartData ?>;
    
    // Generate colors based on value (green for positive, red for negative)
    const backgroundColors = dataValues.map(value => {
        return value > 0 ? 'rgba(16, 185, 129, 0.8)' :  // emerald with opacity
               value < 0 ? 'rgba(244, 63, 94, 0.8)' :   // rose with opacity
                           'rgba(148, 163, 184, 0.8)';   // slate with opacity
    });
    
    const borderColors = dataValues.map(value => {
        return value > 0 ? 'rgba(16, 185, 129, 1)' :   // emerald
               value < 0 ? 'rgba(244, 63, 94, 1)' :    // rose
                           'rgba(148, 163, 184, 1)';   // slate
    });
    
    // Create gradient for chart area
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(102, 126, 234, 0.1)');
    gradient.addColorStop(1, 'rgba(102, 126, 234, 0)');
    
    // Create the chart
    const impactChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Dampak Saldo (Rp)',
                data: dataValues,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 40
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
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#e2e8f0',
                    borderColor: 'rgba(102, 126, 234, 0.5)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            const value = context.parsed.y;
                            const sign = value > 0 ? '+' : '';
                            label += `${sign}Rp ${value.toLocaleString('id-ID')}`;
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(241, 245, 249, 0.8)'
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '600'
                        },
                        color: '#64748b',
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    },
                    title: {
                        display: true,
                        text: 'Dampak Saldo (Rp)',
                        color: '#64748b',
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '600'
                        },
                        color: '#64748b',
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Add animation to chart bars on load
    impactChart.update();
});
</script>

<style>
/* Custom Styles - Konsisten dengan desain sebelumnya */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
    --danger-gradient: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
}

.page-title.text-gradient {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.net-profit-card .card {
    border-radius: 12px;
    transition: all 0.3s ease;
}

.net-profit-card .card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profit-loss-row {
    transition: all 0.2s ease;
}

.profit-loss-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
    transform: scale(1.002);
}

.transaction-icon .rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.transaction-volume .badge {
    transition: all 0.3s ease;
}

.profit-loss-row:hover .transaction-volume .badge {
    transform: scale(1.1);
    background: rgba(102, 126, 234, 0.1) !important;
}

.impact-amount {
    transition: all 0.3s ease;
}

.profit-loss-row:hover .impact-amount {
    transform: scale(1.05);
}

.impact-indicator .rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state-icon {
    opacity: 0.5;
}

.border-bottom {
    border-bottom: 2px solid rgba(102, 126, 234, 0.1) !important;
}

.shadow-lg {
    box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
}

.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
}

.rounded-3 {
    border-radius: 12px !important;
}

.rounded-pill {
    border-radius: 50px !important;
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.bg-opacity-25 {
    background-color: rgba(var(--bs-primary-rgb), 0.25) !important;
}

.fs-5 {
    font-size: 1.1rem !important;
}

.fs-4 {
    font-size: 1.5rem !important;
}

/* Chart customization */
.chart-container {
    position: relative;
}

/* Table styling */
.table th {
    font-weight: 600;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.table td {
    padding-top: 1rem;
    padding-bottom: 1rem;
    vertical-align: middle;
}

.table tbody tr {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.table-light {
    background-color: #f8f9fa !important;
}

/* Input group styling */
.input-group-text {
    border-right: none;
    background-color: #f8f9fa !important;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    border-color: #667eea;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .net-profit-card {
        margin-top: 1rem;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .chart-container {
        height: 300px !important;
    }
}
</style>

<?= $this->endSection() ?>