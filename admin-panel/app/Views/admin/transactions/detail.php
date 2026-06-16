<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('admin/transactions') ?>">Transaksi</a></li>
            <li class="breadcrumb-item active">Detail Transaksi</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="header-section rounded-3 p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white">
            <div class="mb-3 mb-md-0">
                <h1 class="h2 fw-bold mb-1">Detail Transaksi</h1>
                <p class="mb-0 opacity-75">ID: <?= $transaction['transaction_id'] ?></p>
                <p class="mb-0 opacity-75">Kode reff : <?= $transaction['kode_transaksi'] ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $previous_url ?>" class="btn btn-light text-primary fw-bold px-4 py-2 shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
                <a href="<?= base_url('admin/transactions/print/') . $transaction['id'] ?>" 
                   target="_blank"
                   class="btn btn-light text-primary fw-bold px-4 py-2 shadow-sm">
                    <i class="fas fa-print me-2"></i> Cetak
                </a>
            </div>
        </div>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Column: Transaction Info -->
        <div class="col-lg-8">
            <!-- Transaction Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-receipt text-primary me-2"></i>
                            Informasi Transaksi
                        </h5>
                        <div>
                            <?php 
                                $status = $transaction['status'];
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
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>ID Transaksi</strong></td>
                                    <td><?= $transaction['transaction_code'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal</strong></td>
                                    <td>
                                        <div><?= $transaction['created_date'] ?></div>
                                        <small class="text-muted"><?= $transaction['created_time'] ?> WIB</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tipe Transaksi</strong></td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info px-3">
                                            <?= strtoupper(str_replace('_', ' ', $transaction['type'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Arus</strong></td>
                                    <td>
                                        <?php if($transaction['flow'] == 'in'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-3">
                                                <i class="fas fa-arrow-up me-1"></i> Pemasukan
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3">
                                                <i class="fas fa-arrow-down me-1"></i> Pengeluaran
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Jumlah</strong></td>
                                    <td class="fw-bold <?= $transaction['flow'] == 'in' ? 'text-success' : 'text-danger' ?> fs-5">
                                        <?= $transaction['amount_formatted'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Saldo setelah transaksi</strong></td>
                                    <td class="fw-bold">Rp <?= number_format($transaction['current_balance'] ?? 0, 0, ',', '.') ?></td>
                                </tr>
                                <?php if($transaction['approved_by']): ?>
                                <tr>
                                    <td><strong>Disetujui Oleh</strong></td>
                                    <td>
                                        <?= $transaction['approved_by']['name'] ?>
                                        <small class="text-muted">(@<?= $transaction['approved_by']['username'] ?>)</small>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if($transaction['tournament']): ?>
                                <tr>
                                    <td><strong>Turnamen</strong></td>
                                    <td><?= $transaction['tournament'] ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold mb-2">Keterangan:</h6>
                        <div class="p-3 bg-light rounded">
                            <?= $transaction['description'] ? nl2br(esc($transaction['description'])) : '<span class="text-muted">Tidak ada keterangan</span>' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <?php if(!empty($payment_info)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-credit-card text-primary me-2"></i>
                        Detail Transaksi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Jenis Pembayaran</strong></td>
                                    <td><?= $payment_info['type'] ?></td>
                                </tr>
                                <?php if(isset($payment_info['bank'])): ?>
                                <tr>
                                    <td><strong>Bank</strong></td>
                                    <td><?= $payment_info['bank'] ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <?php if(isset($payment_info['account_number'])): ?>
                                <tr>
                                    <td width="40%"><strong>Nomor Rekening/VA</strong></td>
                                    <td class="fw-bold"><?= $payment_info['account_number'] ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if(isset($payment_info['account_name'])): ?>
                                <tr>
                                    <td><strong>Nama Rekening</strong></td>
                                    <td><?= $payment_info['account_name'] ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: User Info & Actions -->
        <div class="col-lg-4">
            <!-- User Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user text-primary me-2"></i>
                        Informasi Pengguna
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <span class="fw-bold text-primary" style="font-size: 2rem;">
                                <?= strtoupper(substr($transaction['user']['name'] ?? '?', 0, 1)) ?>
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1"><?= esc($transaction['user']['name'] ?? 'Unknown') ?></h5>
                        <p class="text-muted mb-0">@<?= esc($transaction['user']['username'] ?? 'unknown') ?></p>
                    </div>
                    
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Email</strong></td>
                            <td><?= esc($transaction['user']['email'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Telepon</strong></td>
                            <td><?= esc($transaction['user']['phone'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td><strong>ID Pengguna</strong></td>
                            <td>#<?= $transaction['id'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-cogs text-primary me-2"></i>
                        Aksi
                    </h5>
                </div>
                <div class="card-body">
                    <?php if($transaction['status'] == 'pending'): ?>
                        <!-- Status Update Form -->
                        <form action="<?= base_url('admin/transactions/update-status/' . $transaction['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ubah Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="">Pilih Status</option>
                                    <option value="success">Berhasil</option>
                                    <option value="failed">Gagal</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Catatan</label>
                                <textarea name="description" class="form-control" rows="3" 
                                          placeholder="Tambahkan catatan jika perlu..."></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('admin/transactions/edit/' . $transaction['id']) ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i> Edit Transaksi
                                </a>
                                
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="confirmDelete(<?= $transaction['id'] ?>)">
                                    <i class="fas fa-trash me-2"></i> Hapus Transaksi
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="fw-bold">Transaksi Selesai</h5>
                            <p class="text-muted">Status: 
                                <span class="badge bg-<?= $transaction['status'] == 'success' ? 'success' : 'danger' ?>">
                                    <?= $transaction['status'] == 'success' ? 'Berhasil' : 'Gagal' ?>
                                </span>
                            </p>
                            <div class="mt-3">
                                <a href="<?= base_url('admin/transactions') ?>" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i> Kembali ke Daftar
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Statistik
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="d-flex justify-content-around mb-3">
                            <div class="text-center">
                                <div class="text-muted small">Total Transaksi</div>
                                <div class="fw-bold fs-4"><?= number_format($transaction['id'] ?? 0) ?></div>
                            </div>
                            <div class="text-center">
                                <div class="text-muted small">Nominal</div>
                                <div class="fw-bold fs-4 <?= $transaction['flow'] == 'in' ? 'text-success' : 'text-danger' ?>">
                                    <?= $transaction['amount_formatted'] ?>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Transaksi ini adalah transaksi <?= $transaction['flow'] == 'in' ? 'pemasukan' : 'pengeluaran' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Transactions (if any) -->
    <?php if($transaction['related_user_id']): ?>
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-link text-primary me-2"></i>
                        Transaksi Terkait
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        Transaksi ini terkait dengan pengguna ID: <?= $transaction['related_user_full_name'] ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus transaksi ini?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Tindakan ini tidak dapat dibatalkan!
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="post" action="">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .header-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    
    .table-borderless td {
        padding: 0.5rem 0;
    }
    
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
    
    .card {
        border-radius: 10px;
    }
    
    @media print {
        .no-print { display: none !important; }
        .container-fluid { padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .btn { display: none !important; }
        .modal { display: none !important; }
        .breadcrumb { display: none !important; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Confirm delete function
    function confirmDelete(transactionId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const form = document.getElementById('deleteForm');
        form.action = `<?= base_url('admin/transactions/delete/') ?>${transactionId}`;
        modal.show();
    }
    
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        // Status update form validation
        const statusForm = document.querySelector('form[action*="update-status"]');
        if (statusForm) {
            statusForm.addEventListener('submit', function(e) {
                const status = this.querySelector('select[name="status"]').value;
                if (!status) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Perhatian',
                        text: 'Silakan pilih status terlebih dahulu',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
        
        // Print button handler
        document.querySelectorAll('.btn-print').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                window.print();
            });
        });
    });
    
    // Auto-close alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>

<?= $this->endSection() ?>