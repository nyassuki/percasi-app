<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-chart-pie me-2"></i>Rekap Transaksi
            </h1>
            <p class="text-muted mb-0">Laporan akumulasi dana berdasarkan tipe dan periode waktu</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" 
                    class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm">
                <i class="fas fa-print me-1"></i>Cetak
            </button>
            <button onclick="exportToExcel()" 
                    class="btn btn-primary btn-sm rounded-pill shadow-sm">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </button>
        </div>
    </div>

    <!-- Filter Card dengan glass effect -->
    <div class="card glass-card border-0 shadow-lg mb-4">
        <div class="card-header bg-transparent py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2 text-primary"></i>Filter Periode
                </h5>
                <div class="btn-group">
                    <button onclick="setDateRange('today')" 
                            class="btn btn-sm btn-outline-secondary rounded-start">
                        Hari Ini
                    </button>
                    <button onclick="setDateRange('week')" 
                            class="btn btn-sm btn-outline-secondary">
                        7 Hari
                    </button>
                    <button onclick="setDateRange('month')" 
                            class="btn btn-sm btn-outline-secondary rounded-end">
                        30 Hari
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>Dari Tanggal
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-calendar text-primary"></i>
                        </span>
                        <input type="date" name="date_from" value="<?= $dateFrom ?>" 
                               class="form-control form-control-sm border-start-0 ps-0">
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">
                        <i class="fas fa-calendar-check me-1"></i>Sampai Tanggal
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-calendar text-primary"></i>
                        </span>
                        <input type="date" name="date_to" value="<?= $dateTo ?>" 
                               class="form-control form-control-sm border-start-0 ps-0">
                    </div>
                </div>
                
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="submit" 
                            class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fas fa-filter me-1"></i>Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-hover border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success bg-opacity-10 text-success rounded-circle">
                                <i class="fas fa-arrow-down fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold">Rp <?= number_format($totalIn, 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total masuk</p>
                            <div class="mt-2">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    +<?= $summaryInCount ?? 0 ?> transaksi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-hover border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-danger bg-opacity-10 text-danger rounded-circle">
                                <i class="fas fa-arrow-up fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold">Rp <?= number_format($totalOut, 0, ',', '.') ?></h5>
                            <p class="text-muted mb-0">Total saldo user</p>
                            <div class="mt-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    -<?= $summaryOutCount ?? 0 ?> transaksi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card card-hover border-0 shadow-sm bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-white bg-opacity-10 text-white rounded-circle">
                                <i class="fas fa-chart-line fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold">Rp <?= number_format($netProfit, 0, ',', '.') ?></h5>
                            <p class="text-white-50 mb-0">User withdraw</p>
                            <div class="mt-2">
                                <span class="badge bg-white bg-opacity-10 text-white">
                                    <?= $summaryTotalCount ?? 0 ?> total transaksi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2 text-primary"></i>Rincian Transaksi
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <?= count($summary) ?> jenis transaksi
                </span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">
                                <span class="text-muted small fw-bold">Tipe Transaksi</span>
                            </th>
                            <th class="py-3 border-0 text-center" style="width: 120px;">
                                <span class="text-muted small fw-bold">Status</span>
                            </th>
                            <th class="py-3 border-0 text-center" style="width: 120px;">
                                <span class="text-muted small fw-bold">Volume</span>
                            </th>
                            <th class="pe-4 py-3 border-0 text-end" style="width: 200px;">
                                <span class="text-muted small fw-bold">Total Nominal</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summary)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon mb-3">
                                            <i class="fas fa-chart-pie fa-3x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">Tidak ada data transaksi</h5>
                                        <p class="text-muted mb-4">Tidak ada transaksi pada periode yang dipilih</p>
                                        <button onclick="resetFilter()" 
                                                class="btn btn-primary btn-sm">
                                            <i class="fas fa-redo me-1"></i>Reset Filter
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($summary as $row): ?>
                                <tr class="transaction-row">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="transaction-type-icon me-3">
                                                <div class="p-2 rounded-circle <?= $row->flow === 'in' ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' ?>">
                                                    <i class="fas <?= $row->flow === 'in' ? 'fa-arrow-down text-success' : 'fa-arrow-up text-danger' ?> fa-lg"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-capitalize">
                                                    <?= str_replace('_', ' ', $row->type) ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?= $row->description ?? 'Tidak ada deskripsi' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="badge <?= $row->flow === 'in' ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' ?> rounded-pill px-3 py-2">
                                            <i class="fas <?= $row->flow === 'in' ? 'fa-sign-in-alt' : 'fa-sign-out-alt' ?> me-1"></i>
                                            <?= $row->flow === 'in' ? 'Pemasukan' : 'Pengeluaran' ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="transaction-volume">
                                            <div class="fw-bold fs-5"><?= $row->jumlah_transaksi ?></div>
                                            <div class="small text-muted">transaksi</div>
                                        </div>
                                    </td>
                                    
                                    <td class="pe-4 text-end">
                                        <div class="transaction-amount">
                                            <div class="fw-bold fs-5 <?= $row->flow === 'in' ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($row->total_nominal, 0, ',', '.') ?>
                                            </div>
                                            <?php if ($row->jumlah_transaksi > 0): ?>
                                                <div class="small text-muted">
                                                    Rata-rata: Rp <?= number_format($row->total_nominal / $row->jumlah_transaksi, 0, ',', '.') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    
                    <?php if (!empty($summary)): ?>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="ps-4 py-3 fw-bold text-muted">
                                Total Keseluruhan:
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="fw-bold fs-4 text-dark">
                                    Rp <?= number_format($totalIn + $totalOut, 0, ',', '.') ?>
                                </div>
                                <div class="small text-muted">
                                    <?= ($summaryInCount ?? 0) + ($summaryOutCount ?? 0) ?> total transaksi
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Informasi Periode
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Periode Laporan:</span>
                            <span class="fw-bold">
                                <?php if ($dateFrom && $dateTo): ?>
                                    <?= date('d M Y', strtotime($dateFrom)) ?> - <?= date('d M Y', strtotime($dateTo)) ?>
                                <?php else: ?>
                                    Semua waktu
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Status Laporan:</span>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check-circle me-1"></i>Siap
                            </span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2">
                            <span class="text-muted">Diakses pada:</span>
                            <span class="fw-bold">
                                <?= date('d M Y H:i:s') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-5">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Statistik Ringkas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="p-3">
                                <div class="fw-bold fs-2 text-success"><?= $summaryInCount ?? 0 ?></div>
                                <div class="small text-muted">Transaksi Masuk</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3">
                                <div class="fw-bold fs-2 text-danger"><?= $summaryOutCount ?? 0 ?></div>
                                <div class="small text-muted">Transaksi Keluar</div>
                            </div>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 10px;">
                        <?php
                        $totalTrans = ($summaryInCount ?? 0) + ($summaryOutCount ?? 0);
                        $inPercent = $totalTrans > 0 ? (($summaryInCount ?? 0) / $totalTrans * 100) : 0;
                        $outPercent = $totalTrans > 0 ? (($summaryOutCount ?? 0) / $totalTrans * 100) : 0;
                        ?>
                        <div class="progress-bar bg-success" style="width: <?= $inPercent ?>%"></div>
                        <div class="progress-bar bg-danger" style="width: <?= $outPercent ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="small text-muted"><?= round($inPercent, 1) ?>% Masuk</span>
                        <span class="small text-muted"><?= round($outPercent, 1) ?>% Keluar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Styles - Konsisten dengan desain match */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.page-title.text-gradient {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.card-hover {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.transaction-row {
    transition: all 0.2s ease;
}

.transaction-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
    transform: scale(1.002);
}

.transaction-type-icon .rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.transaction-volume, .transaction-amount {
    transition: all 0.3s ease;
}

.transaction-row:hover .transaction-volume,
.transaction-row:hover .transaction-amount {
    transform: scale(1.05);
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state-icon {
    opacity: 0.5;
}

.info-item {
    font-size: 0.9rem;
}

.info-item:last-child {
    border-bottom: none !important;
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

.input-group-text {
    border-right: none;
    background-color: #f8f9fa !important;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    border-color: #667eea;
}

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

.fs-5 {
    font-size: 1.1rem !important;
}

.fs-4 {
    font-size: 1.5rem !important;
}

.fs-2 {
    font-size: 2rem !important;
}

/* Print styles */
@media print {
    .btn, .page-header .btn, .glass-card .btn-group {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .text-success, .text-danger, .text-primary {
        color: #000 !important;
    }
    
    .bg-opacity-10, .bg-opacity-25, .bg-dark {
        background: #f8f9fa !important;
        color: #000 !important;
    }
    
    .border-bottom {
        border-bottom: 1px solid #ddd !important;
    }
}
</style>

<script>
function setDateRange(range) {
    const today = new Date();
    const dateFrom = document.querySelector('input[name="date_from"]');
    const dateTo = document.querySelector('input[name="date_to"]');
    
    switch(range) {
        case 'today':
            const todayStr = today.toISOString().split('T')[0];
            dateFrom.value = todayStr;
            dateTo.value = todayStr;
            break;
        case 'week':
            const weekAgo = new Date();
            weekAgo.setDate(today.getDate() - 7);
            dateFrom.value = weekAgo.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'month':
            const monthAgo = new Date();
            monthAgo.setDate(today.getDate() - 30);
            dateFrom.value = monthAgo.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
    }
    
    // Auto submit form
    document.querySelector('form').submit();
}

function exportToExcel() {
    // In a real application, this would generate and download an Excel file
    // For now, we'll show a success message
    alert('File Excel sedang diproses... Data akan diunduh dalam beberapa detik.');
    
    // Simulate download (in real app, this would be a proper Excel export)
    setTimeout(() => {
        const link = document.createElement('a');
        link.href = '#';
        link.download = `rekap-transaksi-${new Date().toISOString().split('T')[0]}.xlsx`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show success toast/notification in real app
        console.log('Excel export completed');
    }, 1000);
}

function resetFilter() {
    window.location.href = window.location.pathname;
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>

<?= $this->endSection() ?>