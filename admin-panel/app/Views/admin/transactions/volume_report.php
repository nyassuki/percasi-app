<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-chart-bar me-2"></i>Volume Perputaran
            </h1>
            <p class="text-muted mb-0">Akumulasi nominal transaksi berdasarkan kategori tipe</p>
        </div>
        
        <div class="total-volume-card">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-lg bg-white bg-opacity-10 text-white rounded-circle">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <p class="small text-white-50 mb-1">Total Perputaran (All)</p>
                            <h3 class="mb-0 fw-bold">Rp <?= number_format($grandTotal, 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card dengan glass effect -->
    <div class="card glass-card border-0 shadow-lg mb-4">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>Filter Periode
            </h5>
        </div>
        <div class="card-body">
            <form action="" method="get" class="row g-3 align-items-center">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">
                        Dari Tanggal
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-calendar text-primary"></i>
                        </span>
                        <input type="date" name="date_from" value="<?= $dateFrom ?>" 
                               class="form-control form-control-sm border-start-0 ps-0">
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-uppercase fw-bold text-muted">
                        Sampai Tanggal
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-calendar text-primary"></i>
                        </span>
                        <input type="date" name="date_to" value="<?= $dateTo ?>" 
                               class="form-control form-control-sm border-start-0 ps-0">
                    </div>
                </div>
                
                <div class="col-lg-4 d-flex align-items-end">
                    <button type="submit" 
                            class="btn btn-primary btn-sm w-100 shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-sync-alt"></i>Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2 text-primary"></i>Detail Volume Perputaran
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            <?= count($volumeData) ?> Kategori
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">
                                        <span class="text-muted small fw-bold">Kategori Tipe</span>
                                    </th>
                                    <th class="py-3 border-0 text-center" style="width: 120px;">
                                        <span class="text-muted small fw-bold">Progress</span>
                                    </th>
                                    <th class="py-3 border-0 text-center" style="width: 120px;">
                                        <span class="text-muted small fw-bold">Frekuensi</span>
                                    </th>
                                    <th class="pe-4 py-3 border-0 text-end" style="width: 200px;">
                                        <span class="text-muted small fw-bold">Total Akumulasi</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($volumeData)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="empty-state">
                                                <div class="empty-state-icon mb-3">
                                                    <i class="fas fa-chart-pie fa-3x text-muted opacity-25"></i>
                                                </div>
                                                <h5 class="text-muted mb-2">Tidak ada data</h5>
                                                <p class="text-muted mb-4">Tidak ada transaksi pada periode yang dipilih</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($volumeData as $row): ?>
                                    <?php 
                                    $percent = $grandTotal > 0 ? ($row->total_akumulasi / $grandTotal) * 100 : 0;
                                    $categoryName = str_replace('_', ' ', $row->type);
                                    ?>
                                    <tr class="volume-row">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="category-icon me-3">
                                                    <div class="p-2 rounded-circle bg-primary bg-opacity-10">
                                                        <i class="fas fa-chart-line text-primary fa-lg"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-capitalize">
                                                        <?= $categoryName ?>
                                                    </div>
                                                    <div class="small text-muted">
                                                        ID: <?= $row->type ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="progress-container">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted"><?= round($percent, 1) ?>%</span>
                                                    <span class="text-muted">Kontribusi</span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-gradient" 
                                                         role="progressbar" 
                                                         style="width: <?= $percent ?>%"
                                                         aria-valuenow="<?= $percent ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="frequency-badge">
                                                <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                                    <i class="fas fa-exchange-alt me-1"></i>
                                                    <?= $row->jumlah_transaksi ?> TX
                                                </span>
                                            </div>
                                        </td>
                                        
                                        <td class="pe-4 text-end">
                                            <div class="volume-amount">
                                                <div class="fw-bold fs-5 text-dark">
                                                    Rp <?= number_format($row->total_akumulasi, 0, ',', '.') ?>
                                                </div>
                                                <?php if ($row->jumlah_transaksi > 0): ?>
                                                    <div class="small text-muted">
                                                        Rata: Rp <?= number_format($row->total_akumulasi / $row->jumlah_transaksi, 0, ',', '.') ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            
                            <?php if (!empty($volumeData)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="ps-4 py-3 fw-bold text-muted">
                                        Grand Total:
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <div class="fw-bold fs-4 text-dark">
                                            Rp <?= number_format($grandTotal, 0, ',', '.') ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= array_sum(array_column($volumeData, 'jumlah_transaksi')) ?> total transaksi
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Information Card -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Informasi Laporan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-content">
                        <div class="alert alert-info bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3 mb-4">
                            <div class="d-flex">
                                <i class="fas fa-lightbulb fa-lg text-info mt-1 me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-2">Volume Perputaran</h6>
                                    <p class="mb-0 small">
                                        Laporan ini menghitung volume perputaran uang secara <strong>absolut</strong>. 
                                        Angka yang muncul adalah total nominal yang diproses oleh sistem untuk setiap kategori, 
                                        baik sebagai dana masuk maupun keluar.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-list">
                            <div class="info-item d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Status Transaksi:</span>
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-check-circle me-1"></i>Hanya SUCCESS
                                </span>
                            </div>
                            <div class="info-item d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Jenis Perhitungan:</span>
                                <span class="fw-bold">Absolut</span>
                            </div>
                            <div class="info-item d-flex justify-content-between py-2">
                                <span class="text-muted">Update Terakhir:</span>
                                <span class="fw-bold">
                                    <?= date('d M Y H:i') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>Statistik Ringkas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="stats-content">
                        <?php if (!empty($volumeData)): ?>
                            <?php 
                            // Hitung rata-rata per transaksi
                            $avgTransaction = array_sum(array_column($volumeData, 'total_akumulasi')) / array_sum(array_column($volumeData, 'jumlah_transaksi'));
                            
                            // Cari kategori terbesar
                            $maxCategory = null;
                            $maxAmount = 0;
                            foreach ($volumeData as $row) {
                                if ($row->total_akumulasi > $maxAmount) {
                                    $maxAmount = $row->total_akumulasi;
                                    $maxCategory = $row->type;
                                }
                            }
                            ?>
                            <div class="row text-center mb-4">
                                <div class="col-6">
                                    <div class="p-3">
                                        <div class="fw-bold fs-3 text-primary"><?= count($volumeData) ?></div>
                                        <div class="small text-muted">Kategori</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3">
                                        <div class="fw-bold fs-3 text-success">
                                            Rp <?= number_format($avgTransaction, 0, ',', '.') ?>
                                        </div>
                                        <div class="small text-muted">Rata per TX</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="category-breakdown">
                                <h6 class="small text-uppercase text-muted mb-3">Kategori Terbesar</h6>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold text-capitalize">
                                        <?= str_replace('_', ' ', $maxCategory) ?>
                                    </span>
                                    <span class="badge bg-primary rounded-pill px-3">
                                        <?= round(($maxAmount / $grandTotal) * 100, 1) ?>%
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" 
                                         style="width: <?= ($maxAmount / $grandTotal) * 100 ?>%"></div>
                                </div>
                                <div class="text-end mt-2 small text-muted">
                                    Rp <?= number_format($maxAmount, 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-pie fa-2x text-muted opacity-25 mb-3"></i>
                                <p class="text-muted small">Tidak ada data statistik</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar me-2 text-primary"></i>Periode Laporan
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
                            <span class="text-muted">Lama Periode:</span>
                            <span class="fw-bold">
                                <?php if ($dateFrom && $dateTo): 
                                    $start = new DateTime($dateFrom);
                                    $end = new DateTime($dateTo);
                                    $interval = $start->diff($end);
                                    echo $interval->days . ' hari';
                                else: ?>
                                    N/A
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2 text-primary"></i>Analisis Ringkas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="analysis-info">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Total Transaksi:</span>
                            <span class="fw-bold">
                                <?= array_sum(array_column($volumeData, 'jumlah_transaksi')) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Rata Perputaran:</span>
                            <span class="fw-bold">
                                <?php 
                                $totalTx = array_sum(array_column($volumeData, 'jumlah_transaksi'));
                                echo $totalTx > 0 ? 'Rp ' . number_format($grandTotal / $totalTx, 0, ',', '.') : 'N/A';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Styles - Konsisten dengan desain sebelumnya */
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

.total-volume-card .card {
    border-radius: 12px;
    background: linear-gradient(135deg, #212529 0%, #343a40 100%);
}

.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items-center;
    justify-content: center;
}

.volume-row {
    transition: all 0.2s ease;
}

.volume-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
    transform: scale(1.002);
}

.category-icon .rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-container .progress-bar.bg-gradient {
    background: var(--primary-gradient) !important;
}

.frequency-badge .badge {
    transition: all 0.3s ease;
}

.volume-row:hover .frequency-badge .badge {
    transform: scale(1.1);
    background: rgba(102, 126, 234, 0.1) !important;
}

.volume-amount {
    transition: all 0.3s ease;
}

.volume-row:hover .volume-amount {
    transform: scale(1.05);
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state-icon {
    opacity: 0.5;
}

.info-list .info-item {
    font-size: 0.9rem;
}

.info-list .info-item:last-child {
    border-bottom: none !important;
}

.alert {
    border-radius: 12px !important;
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

.fs-3 {
    font-size: 1.8rem !important;
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

/* Progress bar customization */
.progress {
    border-radius: 10px;
    overflow: hidden;
    background-color: rgba(0,0,0,0.05);
}

.progress-bar {
    border-radius: 10px;
    transition: width 1s ease-in-out;
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
    .total-volume-card {
        margin-top: 1rem;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
// Inisialisasi tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});

// Auto refresh progress bars dengan animasi
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 300);
    });
}

// Jalankan animasi saat halaman dimuat
window.addEventListener('load', animateProgressBars);

// Quick date range buttons (optional enhancement)
function setQuickDateRange(range) {
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
</script>

<?= $this->endSection() ?>