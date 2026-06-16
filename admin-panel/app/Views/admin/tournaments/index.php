<?= $this->extend('layout/template') ?> 
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="header-section rounded-3 p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white">
            <div class="mb-3 mb-md-0">
                <h1 class="h2 fw-bold mb-1">Manajemen Turnamen</h1>
                <p class="mb-0 opacity-75">Kelola semua turnamen catur dalam satu tempat</p>
            </div>
            <a href="<?= base_url('admin/tournaments/create') ?>" class="btn btn-light text-primary fw-bold px-4 py-2 shadow-sm">
                <i class="fas fa-plus me-2"></i> Tambah Turnamen
            </a>
        </div>
    </div>

    <!-- Flash Message -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <?php
        $statsCards = [
            [
                'title' => 'Total Turnamen',
                'count' => $stats['total_tournaments'] ?? 0,
                'color' => 'primary',
                'icon' => 'trophy'
            ],
            [
                'title' => 'Sedang Registrasi',
                'count' => $stats['registration_tournaments'] ?? 0,
                'color' => 'info',
                'icon' => 'user-plus'
            ],
            [
                'title' => 'Sedang Berjalan',
                'count' => $stats['active_tournaments'] ?? 0,
                'color' => 'success',
                'icon' => 'play-circle'
            ],
            [
                'title' => 'Selesai',
                'count' => $stats['completed_tournaments'] ?? 0,
                'color' => 'dark',
                'icon' => 'check-circle'
            ]
        ];
        
        foreach ($statsCards as $card):
        ?>
        <div class="col-6 col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1"><?= $card['title'] ?></h6>
                            <h3 class="mb-0 fw-bold text-<?= $card['color'] ?>"><?= number_format($card['count'], 0, ',', '.') ?></h3>
                        </div>
                        <div class="bg-<?= $card['color'] ?> bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-<?= $card['icon'] ?> text-<?= $card['color'] ?> fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Search and Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="get" action="<?= base_url('admin/tournaments') ?>" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0" 
                               name="search" 
                               value="<?= esc($filters['search'] ?? '') ?>"
                               placeholder="Cari judul turnamen...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="status">
                        <option value="all" <?= ($filters['status'] ?? 'all') == 'all' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="registration" <?= ($filters['status'] ?? '') == 'registration' ? 'selected' : '' ?>>Registrasi</option>
                        <option value="active" <?= ($filters['status'] ?? '') == 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Selesai</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                        <option value="waiting" <?= ($filters['status'] ?? '') == 'waiting' ? 'selected' : '' ?>>Menunggu</option>

                    </select>
                </div>
                
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="format">
                        <option value="all" <?= ($filters['format'] ?? 'all') == 'all' ? 'selected' : '' ?>>Semua Format</option>
                        <option value="swiss" <?= ($filters['format'] ?? '') == 'swiss' ? 'selected' : '' ?>>Swiss System</option>
                        <option value="round_robin" <?= ($filters['format'] ?? '') == 'round_robin' ? 'selected' : '' ?>>Round Robin</option>
                        <option value="knockout" <?= ($filters['format'] ?? '') == 'knockout' ? 'selected' : '' ?>>Knockout</option>
                        <option value="arena" <?= ($filters['format'] ?? '') == 'arena' ? 'selected' : '' ?>>Arena</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="time_control">
                        <option value="all" <?= ($filters['timeControl'] ?? 'all') == 'all' ? 'selected' : '' ?>>Semua Waktu</option>
                        <option value="standard" <?= ($filters['timeControl'] ?? '') == 'standard' ? 'selected' : '' ?>>Standard</option>
                        <option value="rapid" <?= ($filters['timeControl'] ?? '') == 'rapid' ? 'selected' : '' ?>>Rapid</option>
                        <option value="blitz" <?= ($filters['timeControl'] ?? '') == 'blitz' ? 'selected' : '' ?>>Blitz</option>
                        <option value="bullet" <?= ($filters['timeControl'] ?? '') == 'bullet' ? 'selected' : '' ?>>Bullet</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= base_url('admin/tournaments') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportTournaments()">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Daftar Turnamen</h5>
                <div class="d-flex align-items-center">
                    <span class="text-muted me-2 d-none d-md-block">
                        <?php if (isset($pager)): ?>
                        Menampilkan <?= $pager['offset'] ?>-<?= $pager['limit'] ?> dari <?= number_format($pager['totalTournaments'], 0, ',', '.') ?> turnamen
                        <?php else: ?>
                        Total: <?= count($tournaments) ?> turnamen
                        <?php endif; ?>
                    </span>
                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="printTable()">
                        <i class="fas fa-print me-1"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tournamentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Judul Turnamen</th>
                            <th class="text-center">Format & Waktu</th>
                            <th class="text-center">Jadwal</th>
                            <th class="text-center">Biaya & Hadiah</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tournaments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada turnamen</h5>
                                    <p class="text-muted mb-4">Mulai dengan membuat turnamen pertama Anda</p>
                                    <a href="<?= base_url('admin/tournaments/create') ?>" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Buat Turnamen
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tournaments as $t): ?>
                            <tr class="align-middle">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="tournament-icon bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="fas fa-trophy text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold"><?= esc($t->title) ?></h6>
                                            <div class="d-flex flex-wrap gap-1">
                                                <span class="badge bg-light text-dark border"><?= $t->format ?></span>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="fas fa-user-friends me-1"></i> 
                                                    <?= $t->total_participants ?? 0 ?> Peserta    <i class="fas fa-clock me-1"></i> Pendaftaran tutup: <?= date('d M Y H:i', strtotime($t->registration_close)) ?>
                                                </span>

                                                <?php if (isset($t->total_matches) && $t->total_matches > 0): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="fas fa-chess-board me-1"></i> 
                                                    <?= $t->total_matches ?> Pertandingan
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge bg-light text-dark border"><i class="fas fa-gift me-1"></i> RP <?= number_format($t->total_participants*$t->entry_fee,0)?></span>
                                            <?php if (!empty($t->description)): ?>
                                            <p class="text-muted small mb-0 mt-1"><?= esc(substr($t->description, 0, 100)) ?>...</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="time-control-box p-2 rounded bg-light">
                                        <div class="fw-bold mb-1"><?= ucfirst($t->time_control_type) ?></div>
                                        <div class="text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= $t->time_control_base ?>+<?= $t->time_control_increment ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="calendar-date">
                                        <div class="fw-bold text-primary">
                                            <?= date('d M Y', strtotime($t->start_time)) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('H:i', strtotime($t->start_time)) ?>
                                        </div>
                                        <?php if ($t->end_time): ?>
                                        <div class="text-muted small">
                                            s/d <?= date('d M Y H:i', strtotime($t->end_time)) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="finance-info">
                                        <div class="text-danger mb-1">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            Rp<?= number_format($t->entry_fee, 0, ',', '.') ?>
                                        </div>
                                        <div class="text-success fw-bold">
                                            <i class="fas fa-gift me-1"></i>
                                            Rp<?= number_format($t->prize_pool, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $status_config = [
                                            'waiting' => ['bg-info', 'fas fa-clock', 'Waiting'],
                                            'registration' => ['bg-info', 'fas fa-user-plus', 'Registration'],
                                            'active'       => ['bg-success', 'fas fa-play-circle', 'Active'],
                                            'completed'    => ['bg-dark', 'fas fa-check-circle', 'Selesai'],
                                            'cancelled'    => ['bg-danger', 'fas fa-times-circle', 'Dibatalkan']
                                        ];
                                        $config = $status_config[$t->status] ?? ['bg-secondary', 'fas fa-question', 'Unknown'];
                                    ?>
                                    <span class="badge <?= $config[0] ?> px-3 py-2 rounded-pill d-inline-flex align-items-center">
                                        <i class="<?= $config[1] ?> me-1"></i>
                                        <?= $config[2] ?>
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="<?= base_url('admin/tournaments/view/' . $t->id) ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-circle p-2" 
                                           title="Detail"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php
                                        // Cek jumlah peserta
                                        $total_participants = $t->total_participants ?? 0;
                                        $has_participants = ($total_participants > 0);
                                        ?>
                                        
                                        <!-- Tombol Edit -->
                                        <?php if($has_participants): ?>
                                            <!-- Jika ada peserta: tombol aktif dengan alert -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning rounded-circle p-2"
                                                    title="Edit Turnamen (<?= $total_participants ?> peserta terdaftar)"
                                                    data-bs-toggle="tooltip"
                                                    onclick="showEditAlert(<?= $t->id ?>, '<?= addslashes($t->title) ?>', <?= $total_participants ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php else: ?>
                                            <!-- Jika tidak ada peserta: link langsung ke edit -->
                                            <a href="<?= base_url('admin/tournaments/edit/' . $t->id) ?>" 
                                               class="btn btn-sm btn-outline-warning rounded-circle p-2"
                                               title="Edit Turnamen"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Tombol Hapus -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger rounded-circle p-2"
                                                title="Hapus Turnamen"
                                                data-bs-toggle="tooltip"
                                                onclick="<?php echo $has_participants ? "showDeleteAlert({$t->id}, '" . addslashes($t->title) . "', {$total_participants})" : "confirmDelete({$t->id}, '" . addslashes($t->title) . "')" ?>">
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
        
        <!-- Pagination Section -->
        <?php if (isset($pager) && $pager['totalPages'] > 1): ?>
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Halaman <?= $pager['currentPage'] ?> dari <?= $pager['totalPages'] ?>
                </div>
                
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Previous Button -->
                        <li class="page-item <?= !$pager['hasPrevious'] ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="<?= base_url('admin/tournaments?page=' . ($pager['currentPage'] - 1) . '&search=' . urlencode($filters['search'] ?? '') . '&status=' . ($filters['status'] ?? 'all') . '&format=' . ($filters['format'] ?? 'all') . '&time_control=' . ($filters['timeControl'] ?? 'all')) ?>"
                               aria-label="Previous">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php 
                        $startPage = max(1, $pager['currentPage'] - 2);
                        $endPage = min($pager['totalPages'], $pager['currentPage'] + 2);
                        
                        if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" 
                               href="<?= base_url('admin/tournaments?page=1&search=' . urlencode($filters['search'] ?? '') . '&status=' . ($filters['status'] ?? 'all') . '&format=' . ($filters['format'] ?? 'all') . '&time_control=' . ($filters['timeControl'] ?? 'all')) ?>">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $pager['currentPage'] == $i ? 'active' : '' ?>">
                            <a class="page-link" 
                               href="<?= base_url('admin/tournaments?page=' . $i . '&search=' . urlencode($filters['search'] ?? '') . '&status=' . ($filters['status'] ?? 'all') . '&format=' . ($filters['format'] ?? 'all') . '&time_control=' . ($filters['timeControl'] ?? 'all')) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($endPage < $pager['totalPages']): ?>
                        <?php if ($endPage < $pager['totalPages'] - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" 
                               href="<?= base_url('admin/tournaments?page=' . $pager['totalPages'] . '&search=' . urlencode($filters['search'] ?? '') . '&status=' . ($filters['status'] ?? 'all') . '&format=' . ($filters['format'] ?? 'all') . '&time_control=' . ($filters['timeControl'] ?? 'all')) ?>">
                                <?= $pager['totalPages'] ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <!-- Next Button -->
                        <li class="page-item <?= !$pager['hasNext'] ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="<?= base_url('admin/tournaments?page=' . ($pager['currentPage'] + 1) . '&search=' . urlencode($filters['search'] ?? '') . '&status=' . ($filters['status'] ?? 'all') . '&format=' . ($filters['format'] ?? 'all') . '&time_control=' . ($filters['timeControl'] ?? 'all')) ?>"
                               aria-label="Next">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <div class="text-muted small">
                    <?= $pager['perPage'] ?> per halaman
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-trash-alt fa-4x text-danger opacity-50"></i>
                </div>
                <h5 class="mb-3" id="deleteModalTitle">Yakin ingin menghapus turnamen ini?</h5>
                <p class="text-muted" id="deleteModalMessage">Data yang telah dihapus tidak dapat dikembalikan.</p>
                <div class="alert alert-info mt-3 d-none" id="deleteWarning">
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="warningText"></span>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <a id="deleteLink" href="#" class="btn btn-danger px-4">
                    <i class="fas fa-trash me-2"></i> Hapus
                </a>
            </div>
        </div>
    </div>
</div>

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
    
    .tournament-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .time-control-box {
        min-width: 100px;
        border-left: 3px solid #667eea;
    }
    
    .calendar-date {
        min-width: 120px;
    }
    
    .finance-info {
        min-width: 100px;
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
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Inisialisasi tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Auto-submit form ketika filter berubah (opsional)
        document.querySelectorAll('select[name="status"], select[name="format"], select[name="time_control"]').forEach(function(select) {
            select.addEventListener('change', function() {
                // Reset ke halaman 1 saat filter berubah
                const form = this.closest('form');
                const pageInput = document.createElement('input');
                pageInput.type = 'hidden';
                pageInput.name = 'page';
                pageInput.value = '1';
                form.appendChild(pageInput);
                form.submit();
            });
        });
        
        // Real-time search dengan debounce
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const form = this.closest('form');
                    const pageInput = document.createElement('input');
                    pageInput.type = 'hidden';
                    pageInput.name = 'page';
                    pageInput.value = '1';
                    form.appendChild(pageInput);
                    form.submit();
                }, 800);
            });
        }
    });

    // Fungsi konfirmasi hapus (hanya untuk turnamen tanpa peserta)
    function confirmDelete(id, title) {
        // Set judul modal
        document.getElementById('deleteModalTitle').textContent = `Hapus "${title}"?`;
        document.getElementById('deleteModalMessage').textContent = 'Data turnamen yang telah dihapus tidak dapat dikembalikan.';
        
        // Sembunyikan warning jika ada
        document.getElementById('deleteWarning').classList.add('d-none');
        
        // Set link hapus
        document.getElementById('deleteLink').href = '<?= base_url('admin/tournaments/delete/') ?>' + id;
        
        // Tampilkan modal
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Fungsi untuk alert ketika tidak boleh hapus (sudah ada peserta)
    function showDeleteAlert(tournamentId, title, totalParticipants) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Dapat Dihapus',
            html: `
                <div class="text-start">
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Turnamen tidak dapat dihapus!</strong>
                    </div>
                    <p><strong>"${title}"</strong> memiliki <strong class="text-danger">${totalParticipants} peserta</strong> yang sudah terdaftar.</p>
                    <p>Penghapusan turnamen akan mengganggu peserta yang sudah mendaftar dan tidak diperbolehkan.</p>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Alternatif yang tersedia:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Ubah status menjadi <strong>"Dibatalkan"</strong> jika turnamen tidak jadi dilaksanakan</li>
                            <li>Tunggu sampai turnamen <strong>"Selesai"</strong> terlebih dahulu</li>
                            <li>Hubungi peserta terlebih dahulu sebelum melakukan perubahan besar</li>
                        </ul>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Lihat Peserta',
            cancelButtonText: 'Ubah Status',
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#ffc107',
            reverseButtons: true,
            showDenyButton: true,
            denyButtonText: 'Tutup',
            denyButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect ke halaman detail turnamen untuk melihat peserta
                window.location.href = '<?= base_url('admin/tournaments/view/') ?>' + tournamentId;
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Redirect ke halaman edit untuk mengubah status
                window.location.href = '<?= base_url('admin/tournaments/edit/') ?>' + tournamentId + '#status';
            }
            // Jika deny/dismiss, tidak melakukan apa-apa
        });
    }

    // Fungsi untuk alert edit ketika ada peserta
    function showEditAlert(tournamentId, title, totalParticipants) {
        Swal.fire({
            icon: 'info',
            title: 'Edit Terbatas',
            html: `
                <div class="text-start">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Edit dengan hati-hati!</strong>
                    </div>
                    <p><strong>"${title}"</strong> memiliki <strong class="text-warning">${totalParticipants} peserta</strong> yang sudah terdaftar.</p>
                    <p>Beberapa pengaturan tidak dapat diubah untuk menjaga konsistensi kompetisi.</p>
                    
                    <div class="alert alert-light border mt-3">
                        <h6 class="mb-2"><strong>Pengaturan yang <span class="text-success">DAPAT</span> diubah:</strong></h6>
                        <ul class="mb-0">
                            <li>Deskripsi turnamen</li>
                            <li>Waktu mulai dan selesai (dengan batasan)</li>
                            <li>Status turnamen (Registrasi → Aktif → Selesai)</li>
                            <li>Informasi kontak dan administrasi</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-light border mt-3">
                        <h6 class="mb-2"><strong>Pengaturan yang <span class="text-danger">TIDAK</span> dapat diubah:</strong></h6>
                        <ul class="mb-0">
                            <li>Format turnamen (Round Robin, Swiss System, dll)</li>
                            <li>Kontrol waktu (standard, rapid, blitz)</li>
                            <li>Base time dan increment</li>
                            <li>Biaya pendaftaran dan total hadiah</li>
                        </ul>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Tetap Edit',
            cancelButtonText: 'Lihat Detail',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#667eea',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tetap lanjut ke halaman edit
                window.location.href = '<?= base_url('admin/tournaments/edit/') ?>' + tournamentId;
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Redirect ke halaman detail turnamen
                window.location.href = '<?= base_url('admin/tournaments/view/') ?>' + tournamentId;
            }
        });
    }

    // Fungsi export tournaments
    function exportTournaments() {
        const search = '<?= $filters['search'] ?? '' ?>';
        const status = '<?= $filters['status'] ?? '' ?>';
        const format = '<?= $filters['format'] ?? '' ?>';
        const timeControl = '<?= $filters['timeControl'] ?? '' ?>';
        
        let url = '<?= base_url('admin/tournaments/export') ?>';
        let params = [];
        
        if (search) params.push(`search=${encodeURIComponent(search)}`);
        if (status && status !== 'all') params.push(`status=${status}`);
        if (format && format !== 'all') params.push(`format=${format}`);
        if (timeControl && timeControl !== 'all') params.push(`time_control=${timeControl}`);
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    }

    // Fungsi cetak tabel
    function printTable() {
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Daftar Turnamen</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
        printWindow.document.write('th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }');
        printWindow.document.write('th { background-color: #f5f5f5; font-weight: bold; }');
        printWindow.document.write('tr:nth-child(even) { background-color: #f9f9f9; }');
        printWindow.document.write('.text-center { text-align: center; }');
        printWindow.document.write('.text-primary { color: #667eea; }');
        printWindow.document.write('.text-success { color: #28a745; }');
        printWindow.document.write('.text-danger { color: #dc3545; }');
        printWindow.document.write('.badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; }');
        printWindow.document.write('@media print { .no-print { display: none; } }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        
        printWindow.document.write('<h2>Daftar Turnamen</h2>');
        printWindow.document.write('<p>Dicetak pada: ' + new Date().toLocaleString('id-ID') + '</p>');
        
        // Clone tabel untuk dicetak
        var tableClone = document.getElementById('tournamentsTable').cloneNode(true);
        
        // Hapus kolom aksi dari tabel yang akan dicetak
        var rows = tableClone.getElementsByTagName('tr');
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].getElementsByTagName('td');
            if (cells.length > 0) {
                rows[i].deleteCell(cells.length - 1); // Hapus kolom terakhir (aksi)
            }
        }
        
        // Hapus header aksi
        var headerCells = tableClone.getElementsByTagName('th');
        if (headerCells.length > 0) {
            var headerRow = headerCells[0].parentNode;
            headerRow.deleteCell(headerCells.length - 1);
        }
        
        printWindow.document.write(tableClone.outerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
</script>

<?= $this->endSection() ?>