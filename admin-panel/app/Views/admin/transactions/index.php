<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header Section dengan Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Laporan transaksi pengguna</li>
        </ol>
    </nav>

    <!-- Header dengan Judul dan Tombol -->
    <div class="header-section rounded-3 p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white">
            <div class="mb-3 mb-md-0">
                <h1 class="h2 fw-bold mb-1">Laporan transaksi pengguna</h1>
                <p class="mb-0 opacity-75">Monitoring transaksi pengguna</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-light text-primary fw-bold px-4 py-2 shadow-sm">
                    <i class="fas fa-print me-2"></i> Cetak Laporan
                </button>
                <button onclick="exportToExcel()" class="btn btn-success text-white fw-bold px-4 py-2 shadow-sm">
                    <i class="fas fa-file-excel me-2"></i> Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form action="" method="get" class="row g-3">
                <!-- Baris 1: Search dan Filter Dasar -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Cari Transaksi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" value="<?= esc($search ?? '') ?>" 
                               class="form-control" placeholder="Nama, username, atau kategori...">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tipe Transaksi</label>
                    <select name="type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <?php if (!empty($filter_options['types'])): ?>
                            <?php foreach ($filter_options['types'] as $typeOpt): ?>
                                <option value="<?= $typeOpt->type ?>" <?= ($type ?? '') == $typeOpt->type ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $typeOpt->type)) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <?php if (!empty($filter_options['statuses'])): ?>
                            <?php foreach ($filter_options['statuses'] as $statusOpt): ?>
                                <option value="<?= $statusOpt->status ?>" <?= ($status ?? '') == $statusOpt->status ? 'selected' : '' ?>>
                                    <?= ucfirst($statusOpt->status) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Baris 2: Filter Lainnya -->
                <div class="col-md-3">
                    <label class="form-label fw-bold">Arus</label>
                    <select name="flow" class="form-select">
                        <option value="">Semua</option>
                        <?php if (!empty($filter_options['flows'])): ?>
                            <?php foreach ($filter_options['flows'] as $flowOpt): ?>
                                <option value="<?= $flowOpt->flow ?>" <?= ($flow ?? '') == $flowOpt->flow ? 'selected' : '' ?>>
                                    <?= $flowOpt->flow == 'in' ? 'Pemasukan' : 'Pengeluaran' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Per Halaman</label>
                    <select name="per_page" class="form-select">
                        <option value="10" <?= ($per_page ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= ($per_page ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($per_page ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($per_page ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>

                <!-- Baris 3: Filter Tanggal -->
                <div class="col-md-3">
                    <label class="form-label fw-bold">Dari Tanggal</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" name="date_from" class="form-control" 
                               value="<?= esc($date_from ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Sampai Tanggal</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" name="date_to" class="form-control" 
                               value="<?= esc($date_to ?? '') ?>">
                    </div>
                </div>

                <!-- Baris 4: Tombol Aksi -->
                <div class="col-md-12">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-filter me-2"></i> Terapkan Filter
                        </button>
                        
                        <?php if($search || $type || $status || $flow || $date_from || $date_to): ?>
                            <a href="<?= base_url('admin/transactions') ?>" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-2"></i> Reset Filter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <div class="mb-2 mb-md-0">
                    <h5 class="mb-0 fw-bold">Daftar Transaksi</h5>
                </div>
                <div class="text-muted text-md-end">
                    <span>Total: <?= number_format($pager['total'] ?? 0) ?> transaksi</span>
                    <span class="mx-2">•</span>
                    <span>Halaman <?= $pager['current_page'] ?? 1 ?> dari <?= $pager['total_pages'] ?? 1 ?></span>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th class="ps-4">Reff</th>
                            <th>Pengguna</th>
                            <th>Kategori</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada transaksi</h5>
                                    <p class="text-muted mb-4">Tidak ada data transaksi yang ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= date('d M Y', strtotime($tx->created_at)) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($tx->created_at)) ?></small>
                                </td>
                                 <td class="ps-4">
                                    <small class="text-muted">
                                        <a href="transactions?search=<?= $tx->kode_transaksi;?>">
                                            <?= esc($tx->kode_transaksi ?? 'unknown') ?>
                                        </a>
                                        </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 36px; height: 36px;">
                                            <span class="fw-bold text-primary"><?= strtoupper(substr($tx->user_fullname ?? '?', 0, 1)) ?></span>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= esc($tx->user_fullname ?? 'Unknown') ?></div>
                                            <small class="text-muted">@<?= esc($tx->username ?? 'unknown') ?></small>
                                            
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info px-3">
                                        <?= esc($tx->type ?? 'Unknown') ?>
                                    </span>
                                    
                                </td>
                                <td class="text-center">
                                    <?php if(($tx->flow ?? '') == 'in'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3">
                                            <i class="fas fa-arrow-up me-1"></i> Masuk
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3">
                                            <i class="fas fa-arrow-down me-1"></i> Keluar
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold <?= ($tx->flow ?? '') == 'in' ? 'text-success' : 'text-danger' ?>">
                                    Rp <?= number_format($tx->amount ?? 0, 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        $status = $tx->status ?? 'pending';
                                        $status_class = [
                                            'success' => ['success', 'fas fa-check-circle', 'Berhasil'],
                                            'pending' => ['warning', 'fas fa-clock', 'Pending'],
                                            'failed' => ['danger', 'fas fa-times-circle', 'Gagal']
                                        ];
                                        $config = $status_class[$status] ?? ['secondary', 'fas fa-question', 'Unknown'];
                                    ?>
                                    <span class="badge bg-<?= $config[0] ?>-subtle text-<?= $config[0] ?> border border-<?= $config[0] ?> px-3 py-2">
                                        <i class="<?= $config[1] ?> me-1"></i>
                                        <?= $config[2] ?>
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="transactions/detail/<?= $tx->id ?>" 
                                           class="btn btn-sm btn-outline-info rounded-circle p-2"
                                           title="Detail"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-warning rounded-circle p-2"
                                                title="Edit"
                                                data-bs-toggle="tooltip"
                                                onclick="editTransaction(<?= $tx->id ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2"
                                                title="Hapus"
                                                data-bs-toggle="tooltip"
                                                onclick="deleteTransaction(<?= $tx->id ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if (!empty($transactions) && ($pager['total_pages'] ?? 1) > 1): ?>
        <div class="card-footer bg-white py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="text-muted small mb-2 mb-md-0">
                    Menampilkan <strong><?= count($transactions) ?></strong> dari <strong><?= $pager['total'] ?? 0 ?></strong> transaksi
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Previous Page -->
                        <li class="page-item <?= ($pager['current_page'] ?? 1) <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildPaginationLink($params ?? [], 1) ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item <?= ($pager['current_page'] ?? 1) <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildPaginationLink($params ?? [], ($pager['current_page'] ?? 1) - 1) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php
                        $current_page = $pager['current_page'] ?? 1;
                        $total_pages = $pager['total_pages'] ?? 1;
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?= $current_page == $i ? 'active' : '' ?>">
                                <a class="page-link" href="<?= buildPaginationLink($params ?? [], $i) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?= ($pager['current_page'] ?? 1) >= ($pager['total_pages'] ?? 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildPaginationLink($params ?? [], ($pager['current_page'] ?? 1) + 1) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <li class="page-item <?= ($pager['current_page'] ?? 1) >= ($pager['total_pages'] ?? 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildPaginationLink($params ?? [], $pager['total_pages'] ?? 1) ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-receipt text-primary me-2"></i>
                    Detail Transaksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionDetailContent">
                <!-- Detail akan diisi via JavaScript -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-2"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function untuk membangun link pagination
function buildPaginationLink($params, $page)
{
    $params['page'] = $page;
    return current_url() . '?' . http_build_query($params);
}
?>

<style>
    .header-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stat-card {
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    
    .table tbody tr {
        transition: background-color 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    
    .btn-circle {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Badge subtle colors */
    .bg-success-subtle {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }
    
    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    .bg-info-subtle {
        background-color: rgba(23, 162, 184, 0.1) !important;
    }
    
    /* Pagination active state */
    .page-item.active .page-link {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .header-section .d-flex {
            flex-direction: column;
            text-align: center;
        }
        
        .header-section .d-flex.gap-2 {
            margin-top: 1rem;
            width: 100%;
            justify-content: center;
        }
        
        .table {
            font-size: 0.875rem;
        }
        
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .card-header .text-md-end {
            text-align: left !important;
            margin-top: 0.5rem;
        }
    }
    
    /* Print styles */
    @media print {
        .no-print { display: none !important; }
        .container-fluid { padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table { font-size: 11pt !important; }
        .btn { display: none !important; }
        .pagination { display: none !important; }
        .form-control, .form-select, .input-group { display: none !important; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Inisialisasi tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    // Fungsi untuk edit transaksi
    function editTransaction(id) {
        // Redirect ke halaman edit transaksi
        window.location.href = `<?= base_url('admin/transactions/edit/') ?>${id}`;
    }

    // Fungsi untuk hapus transaksi
    function deleteTransaction(id) {
        Swal.fire({
            title: 'Hapus Transaksi?',
            text: "Data transaksi yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form hapus
                fetch(`<?= base_url('admin/transactions/delete/') ?>${id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', 'Transaksi telah dihapus.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Gagal menghapus transaksi', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus transaksi', 'error');
                });
            }
        });
    }

    // Fungsi export ke Excel
    function exportToExcel() {
        Swal.fire({
            title: 'Export Data',
            html: `
                <div class="text-start">
                    <p>Pilih format export:</p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="exportFormat" id="formatExcel" value="excel" checked>
                        <label class="form-check-label" for="formatExcel">
                            <i class="fas fa-file-excel text-success me-2"></i> Excel (.xlsx)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="exportFormat" id="formatCSV" value="csv">
                        <label class="form-check-label" for="formatCSV">
                            <i class="fas fa-file-csv text-primary me-2"></i> CSV (.csv)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportFormat" id="formatPDF" value="pdf">
                        <label class="form-check-label" for="formatPDF">
                            <i class="fas fa-file-pdf text-danger me-2"></i> PDF (.pdf)
                        </label>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Export',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
            preConfirm: () => {
                const format = document.querySelector('input[name="exportFormat"]:checked').value;
                return { format: format };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const format = result.value.format;
                // Get current filter parameters
                const params = new URLSearchParams(window.location.search);
                params.set('export', format);
                // Redirect ke export endpoint
                window.location.href = `<?= base_url('admin/transactions/export') ?>?${params.toString()}`;
            }
        });
    }

    // Fungsi cetak struk
    function printReceipt() {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Struk Transaksi</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .receipt { width: 300px; margin: 0 auto; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .item { margin-bottom: 10px; }
                        .total { font-weight: bold; margin-top: 20px; border-top: 2px solid #000; padding-top: 10px; }
                        @media print { .no-print { display: none; } }
                    </style>
                </head>
                <body>
                    <div class="receipt">
                        ${document.getElementById('transactionDetailContent').innerHTML}
                    </div>
                    <script>
                        window.onload = function() { window.print(); setTimeout(() => window.close(), 1000); }
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }

    // Quick filter by date range
    function quickFilterDate(days) {
        const today = new Date();
        const startDate = new Date();
        startDate.setDate(today.getDate() - days);
        
        const dateFromInput = document.querySelector('input[name="date_from"]');
        const dateToInput = document.querySelector('input[name="date_to"]');
        
        dateFromInput.value = startDate.toISOString().split('T')[0];
        dateToInput.value = today.toISOString().split('T')[0];
        
        dateFromInput.closest('form').submit();
    }
</script>

<?= $this->endSection() ?>