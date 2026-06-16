<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header dengan Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light p-3 rounded">
            <li class="breadcrumb-item">
                <a href="<?= base_url('admin') ?>" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= base_url('admin/tournaments') ?>" class="text-decoration-none">
                    <i class="fas fa-trophy me-1"></i> Turnamen
                </a>
            </li>
            <li class="breadcrumb-item active text-primary fw-bold">
                <i class="fas fa-info-circle me-1"></i> <?= substr($tournament->title, 0, 30) . (strlen($tournament->title) > 30 ? '...' : '') ?>
            </li>
        </ol>
    </nav>

    <!-- Header Utama -->
    <div class="header-section rounded-3 p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white">
            <div class="mb-3 mb-md-0">
                <h1 class="h3 fw-bold mb-2"><?= $tournament->title ?></h1>
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <?= date('d M Y', strtotime($tournament->start_time)) ?>
                    </span>
                    <span class="d-flex align-items-center">
                        <i class="fas fa-clock me-2"></i>
                        <?= date('H:i', strtotime($tournament->start_time)) ?>
                    </span>
                    <span class="badge bg-white text-primary px-3 py-1">
                        <?= strtoupper($tournament->format) ?>
                    </span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= base_url('admin/tournaments') ?>" class="btn btn-light text-primary fw-bold">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
                
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-info-circle text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Status</div>
                            <?php
                                $status_class = [
                                    'registration' => 'info',
                                    'active' => 'success',
                                    'completed' => 'dark',
                                    'cancelled' => 'danger'
                                ];
                                $status_label = [
                                    'registration' => 'Registrasi',
                                    'active' => 'Berlangsung',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan'
                                ];
                            ?>
                            <div class="h6 mb-0 fw-bold text-<?= $status_class[$tournament->status] ?>">
                                <?= $status_label[$tournament->status] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-chess text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small"><strong>Kontrol Waktu :  </strong><?= $tournament->time_control_base ?>+<?= $tournament->time_control_increment ?></div>
                            
                            <div class="text-muted small"><strong>Time control: </strong><?= ucfirst($tournament->time_control_type) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-users text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Peserta</div>
                            <div class="h4 mb-0 fw-bold text-primary"><?= count($participants) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-gift text-danger fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Hadiah</div>
                            <div class="h6 mb-0 fw-bold text-success">
                                Rp<?= number_format($tournament->prize_pool, 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Informasi Detail -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Detail
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="fas fa-file-alt text-muted me-2"></i>
                            Deskripsi Turnamen
                        </h6>
                        <div class="p-4 bg-light rounded">
                            <?= $tournament->description ? nl2br($tournament->description) : 
                                '<div class="text-center py-3 text-muted">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                                    Tidak ada deskripsi
                                </div>' 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peserta Terdaftar -->
        <div class="col-lg-8 mb-8">
             <div class="row g-3">
                        <!-- Waktu Pelaksanaan -->
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        Waktu Pelaksanaan
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="text-center p-2 border rounded">
                                                <div class="text-muted small">Mulai</div>
                                                <div class="fw-bold text-success"><?= date('d M Y', strtotime($tournament->start_time)) ?></div>
                                                <div class="text-muted small"><?= date('H:i', strtotime($tournament->start_time)) ?></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-2 border rounded">
                                                <div class="text-muted small">Selesai</div>
                                                <div class="fw-bold text-danger"><?= date('d M Y', strtotime($tournament->end_time)) ?></div>
                                                <div class="text-muted small"><?= date('H:i', strtotime($tournament->end_time)) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Finansial -->
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                                        <i class="fas fa-coins text-warning me-2"></i>
                                        Informasi Biaya
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="text-center p-2 border rounded">
                                                <div class="text-muted small">Biaya Pendaftaran</div>
                                                <div class="h5 fw-bold <?= $tournament->entry_fee == 0 ? 'text-success' : 'text-primary' ?>">
                                                    <?= $tournament->entry_fee == 0 ? 'GRATIS' : 'Rp' . number_format($tournament->entry_fee, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-2 border rounded">
                                                <div class="text-muted small">Total Hadiah</div>
                                                <div class="h5 fw-bold text-success">
                                                    Rp<?= number_format($tournament->prize_pool, 0, ',', '.') ?>
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

    <!-- Tabel Peserta Lengkap -->
    <?php if (!empty($participants)) : ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fas fa-list me-2"></i>
                Daftar Peserta Lengkap
            </h5>
            <div class="d-flex gap-2">
                
                <button class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Peserta</th>
                            <th>Username</th>
                            <th class="text-center">Rating (<?= $tournament->time_control_type ?>)</th>
                            <th class="text-center">Tanggal Daftar</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $index => $p) : ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted"><?= $index + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 36px; height: 36px;">
                                            <span class="fw-bold text-primary"><?= strtoupper(substr($p->full_name, 0, 1)) ?></span>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= $p->full_name ?></div>
                                            <small class="text-muted"><?= $p->email ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-medium">@<?= $p->username ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary px-3 py-1">
                                        <i class="fas fa-chess-knight me-1"></i>
                                        <?= $p->current_rating ?? '1200' ?>
                                    </span>
                                </td>
                                <td class="text-center text-muted">
                                    <?= isset($p->created_at) ? date('d M Y', strtotime($p->created_at)) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        $status_color = [
                                            'confirmed' => 'success',
                                            'pending'   => 'warning',
                                            'cancelled' => 'danger'
                                        ];
                                        $status_label = [
                                            'confirmed' => 'Konfirmasi',
                                            'pending'   => 'Menunggu',
                                            'cancelled' => 'Batal'
                                        ];
                                        $color = $status_color[$p->status] ?? 'secondary';
                                        $label = $status_label[$p->status] ?? ucfirst($p->status);
                                    ?>
                                    <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border border-<?= $color ?> px-3 py-1">
                                        <?= $label ?>
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/users/detail/<?=$p->user_id;?>">
                                        <button class="btn btn-outline-info" title="Lihat Profil">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan <?= count($participants) ?> peserta
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="fas fa-bolt me-2"></i>
                Aksi Cepat
            </h6>
        </div>
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                 
                <?php if ($tournament->status == 'registration') : ?>
                <button class="btn btn-success px-4">
                    <i class="fas fa-play me-2"></i> Mulai Turnamen
                </button>
                <?php endif; ?>
                <?php if ($tournament->status == 'active') : ?>
                <button class="btn btn-danger px-4">
                    <i class="fas fa-stop me-2"></i> Akhiri Turnamen
                </button>
                <?php endif; ?>
                <button class="btn btn-dark px-4">
                    <i class="fas fa-file-export me-2"></i> Export Data
                </button>
                <button class="btn btn-warning px-4">
                    <i class="fas fa-bullhorn me-2"></i> Kirim Pengumuman
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Styles */
    .header-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .breadcrumb {
        background: rgba(102, 126, 234, 0.1);
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    
    .card {
        transition: transform 0.2s;
        border-radius: 10px;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .table th {
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
    }
    
    .table td {
        vertical-align: middle;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    
    .badge {
        font-weight: 500;
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
    
    .participant-item {
        transition: background-color 0.2s;
    }
    
    .participant-item:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    
    /* Responsive adjustments */
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
        
        .btn-group {
            flex-wrap: wrap;
        }
    }
    
    /* Pagination customization */
    .pagination .page-link {
        border-radius: 5px;
        margin: 0 3px;
        border: none;
        color: #495057;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #667eea;
        border-color: #667eea;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animasi hover pada card
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
        });
        
        // Konfirmasi hapus peserta
        document.querySelectorAll('.btn-outline-danger').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin menghapus peserta ini dari turnamen?')) {
                    // Logic untuk menghapus peserta
                    console.log('Menghapus peserta...');
                }
            });
        });
        
        // Toggle peserta lengkap
        const showMoreBtn = document.querySelector('.btn-outline-primary');
        if (showMoreBtn && showMoreBtn.textContent.includes('Lihat')) {
            showMoreBtn.addEventListener('click', function() {
                const fullTable = document.querySelector('.card.border-0.shadow-sm.mb-4');
                if (fullTable) {
                    fullTable.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>