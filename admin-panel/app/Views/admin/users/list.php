<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>
  
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark">
        <i class="fa-solid fa-users-gear me-2"></i> <?= $title ?>
    </h4>
    <div class="d-flex gap-2">
        <!-- Search Form -->
        
        <button class="btn btn-primary btn-sm d-flex align-items-center" id="broadcastBtn">
            <i class="fa-solid fa-message"></i> Broadcast message
        </button>
    </div>
</div>

<!-- Status Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Users</h6>
                        <h4 class="fw-bold mb-0" id="totalUsers">
                            <?= number_format($stats['total_user'], 0, ',', '.') ?>
                        </h4>
                    </div>
                    <div class="bg-primary rounded-circle p-3">
                        <i class="fa-solid fa-users text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">KYC Verified</h6>
                        <h4 class="fw-bold mb-0" id="verifiedCount">
                            <?= number_format($stats['kyc_verified'],0) ?>
                        </h4>
                    </div>
                    <div class="bg-success rounded-circle p-3">
                        <i class="fa-solid fa-check text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Active Users</h6>
                        <h4 class="fw-bold mb-0" id="activeCount">
                            <?= number_format($stats['active_user'],0) ?>
                        </h4>
                    </div>
                    <div class="bg-info rounded-circle p-3">
                        <i class="fa-solid fa-user-check text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pending KYC</h6>
                        <h4 class="fw-bold mb-0" id="pendingCount">
                           <?= number_format($stats['kyc_pending'],0) ?>
                        </h4>
                       
                    </div>
                    <div class="bg-warning rounded-circle p-3">
                        <i class="fa-solid fa-clock text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="get" action="<?= base_url('admin/users') ?>" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fa-solid fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           name="search" 
                           value="<?= esc($search ?? '') ?>"
                           placeholder="Cari nama, username, email, atau ID...">
                </div>
            </div>
            
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="user_status" id="userStatusFilter">
                    <option value="all" <?= ($userStatus ?? 'all') == 'all' ? 'selected' : '' ?>>Semua Status User</option>
                    <option value="ACT" <?= ($userStatus ?? '') == 'ACT' ? 'selected' : '' ?>>Active</option>
                    <option value="NCT" <?= ($userStatus ?? '') == 'NCT' ? 'selected' : '' ?>>Inactive</option>
                    <option value="BND" <?= ($userStatus ?? '') == 'BND' ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="kyc_status" id="kycStatusFilter">
                    <option value="all" <?= ($kycStatus ?? 'all') == 'all' ? 'selected' : '' ?>>Semua Status KYC</option>
                    <option value="none" <?= ($kycStatus ?? '') == 'none' ? 'selected' : '' ?>>Unverified</option>
                    <option value="pending" <?= ($kycStatus ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="verified" <?= ($kycStatus ?? '') == 'verified' ? 'selected' : '' ?>>Verified</option>
                    <option value="rejected" <?= ($kycStatus ?? '') == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- User List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Daftar User & Atlet</h6>
            <div class="text-muted small">
                <?php if (isset($pager)): ?>
                Menampilkan <?= $pager['offset'] ?>-<?= $pager['limit'] ?> dari <?= number_format($pager['totalUsers'], 0, ',', '.') ?> user
                <?php else: ?>
                Menampilkan <?= count($users) ?> data dari total <?= number_format($stats['total_user'], 0, ',', '.') ?> data
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3 fw-semibold">USER</th>
                        <th class="py-3 fw-semibold">RATING</th>
                        <th class="py-3 fw-semibold">FINANSIAL</th>
                        <th class="py-3 fw-semibold">LOKASI</th>
                        <th class="py-3 fw-semibold">KYC</th>
                        <th class="py-3 fw-semibold">STATUS</th>
                        <th class="text-center py-3 fw-semibold">AKSI</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php if (empty($users)): ?>
                    <tr id="noDataRow">
                        <td colspan="7" class="text-center py-5">
                            <div class="py-5">
                                <i class="fa-solid fa-users-slash fa-2x text-muted mb-3"></i>
                                <h6 class="text-muted">Tidak ada data user terdaftar</h6>
                                <?php if (!empty($search) || $userStatus !== 'all' || $kycStatus !== 'all'): ?>
                                <p class="text-muted small">Coba ubah kriteria pencarian atau filter</p>
                                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Reset Filter
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr class="border-bottom" 
                            data-user-status="<?= $u['user_status'] ?>" 
                            data-kyc-status="<?= $u['kyc_status'] ?>">
                            
                            <!-- User Column -->
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative">
                                        <?php
                                        $avatarUrl = !empty($u['avatar_url']) 
                                            ? base_url(str_replace("public", "", $u['avatar_url']))
                                            : 'https://ui-avatars.com/api/?name=' . urlencode($u['username'] ?? 'User') . '&length=2&rounded=true&background=0D8ABC&color=fff';
                                        $fallbackUrl = 'https://ui-avatars.com/api/?name=' . urlencode($u['username'] ?? 'User') . '&length=1&rounded=true';
                                        ?>
                                        <img src="<?= $avatarUrl ?>" 
                                             class="rounded-circle me-3 border" 
                                             width="48" 
                                             height="48"
                                             alt="<?= esc($u['full_name']) ?>"
                                             onerror="this.src='<?= $fallbackUrl ?>'">
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h6 class="fw-bold mb-0"><?= esc($u['full_name']) ?></h6>
                                            <?php if ($u['kyc_status'] === 'verified'): ?>
                                            <i class="fa-solid fa-badge-check text-success" title="KYC Verified"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-at me-1"></i><?= esc($u['username']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-envelope me-1"></i><?= esc($u['email']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-id-card me-1"></i>ID: <?= $u['id'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Rating Column -->
                            <td class="py-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fa-solid fa-chess-knight text-primary me-2"></i>
                                    <div>
                                        <div class="fw-bold text-dark"><small class="text-muted">Standard</small> <?= $u['standard_rating'] ?: '1200' ?></div>
                                        <div class="fw-bold text-dark"><small class="text-muted">Rapid</small> <?= $u['rapid_rating'] ?: '1200' ?></div>
                                        <div class="fw-bold text-dark"><small class="text-muted">Blitz</small> <?= $u['blitz_rating'] ?: '1200' ?></div>
                                        <div class="fw-bold text-dark"><small class="text-muted">Bullet</small> <?= $u['bullet_rating'] ?: '1200' ?></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Financial Column -->
                            <td class="py-3">
                                <div class="mb-1">
                                    <div class="fw-bold text-success">
                                        <i class="fa-solid fa-wallet me-1"></i>
                                        Rp <?= number_format($u['balance'], 0, ',', '.') ?>
                                    </div>
                                    <small class="text-muted">Total Saldo</small>
                                </div>
                            </td>

                            <!-- Location Column -->
                            <td class="py-3">
                                <div class="d-flex align-items-start mb-1">
                                    <i class="fa-solid fa-location-dot text-muted me-2 mt-1"></i>
                                    <div>
                                        <div class="fw-medium"><?= $u['regency_name'] ?: 'Belum diisi' ?></div>
                                        <small class="text-muted"><?= $u['province_name'] ?: 'Indonesia' ?></small>
                                    </div>
                                </div>
                            </td>

                            <!-- KYC Status Column -->
                            <td class="py-3">
                                <?php 
                                $kyc_config = [
                                    'none'      => ['bg-light text-dark', 'fa-circle-dot', 'Unverified'],
                                    'pending'   => ['bg-warning text-dark', 'fa-clock', 'Pending'],
                                    'verified'  => ['bg-success text-white', 'fa-badge-check', 'Verified'],
                                    'rejected'  => ['bg-danger text-white', 'fa-circle-xmark', 'Rejected']
                                ];
                                [$kyc_class, $kyc_icon] = $kyc_config[$u['kyc_status']];
                                ?>
                                <div class="d-flex align-items-center">
                                    <span class="badge <?= $kyc_class ?> d-flex align-items-center gap-1 px-3 py-2">
                                        <i class="fa-solid <?= $kyc_icon ?> me-1"></i>
                                        <?= $kyc_config[$u['kyc_status']][2] ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Status Column -->
                            <td class="py-3">
                                <div class="d-flex flex-column gap-2">
                                    <div>
                                        <span class="badge <?= $u['user_status'] == 'ACT' ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-danger bg-opacity-10 text-danger border border-danger' ?> px-3 py-1">
                                            <i class="fa-solid <?= $u['user_status'] == 'ACT' ? 'fa-check-circle' : 'fa-ban' ?> me-1"></i>
                                            <?= $u['user_status'] == 'ACT' ? 'ACTIVE' : 'BANNED' ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="fa-solid fa-chess-board me-1"></i>
                                        <span>Open Match: <strong><?= $u['open_match'] ?></strong></span>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="fa-solid fa-shield-alt me-1"></i>
                                        <span>2FA: <strong><?= $u['is_2fa_active'] ? 'Aktif' : 'Nonaktif' ?></strong></span>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="fa-solid fa-user-lock me-1"></i>
                                        <span>Single Login: <strong><?= $u['is_single_login'] ? 'YA' : 'TIDAK' ?></strong></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Actions Column -->
                            <td class="text-center pe-4 py-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= base_url('admin/users/detail/' . $u['id']) ?>" 
                                       class="btn btn-outline-primary border d-flex align-items-center gap-1"
                                       title="Lihat Detail">
                                        <i class="fa-solid fa-eye"></i>
                                        <span class="d-none d-md-inline">Detail</span>
                                    </a>
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
    <div class="card-footer bg-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Halaman <?= $pager['currentPage'] ?> dari <?= $pager['totalPages'] ?>
            </div>
            
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Previous Button -->
                    <li class="page-item <?= !$pager['hasPrevious'] ? 'disabled' : '' ?>">
                        <a class="page-link" 
                           href="<?= base_url('admin/users?page=' . ($pager['currentPage'] - 1) . '&search=' . urlencode($search ?? '') . '&user_status=' . ($userStatus ?? 'all') . '&kyc_status=' . ($kycStatus ?? 'all')) ?>"
                           aria-label="Previous">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php 
                    $startPage = max(1, $pager['currentPage'] - 2);
                    $endPage = min($pager['totalPages'], $pager['currentPage'] + 2);
                    
                    if ($startPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="<?= base_url('admin/users?page=1&search=' . urlencode($search ?? '') . '&user_status=' . ($userStatus ?? 'all') . '&kyc_status=' . ($kycStatus ?? 'all')) ?>">1</a>
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
                           href="<?= base_url('admin/users?page=' . $i . '&search=' . urlencode($search ?? '') . '&user_status=' . ($userStatus ?? 'all') . '&kyc_status=' . ($kycStatus ?? 'all')) ?>">
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
                           href="<?= base_url('admin/users?page=' . $pager['totalPages'] . '&search=' . urlencode($search ?? '') . '&user_status=' . ($userStatus ?? 'all') . '&kyc_status=' . ($kycStatus ?? 'all')) ?>">
                            <?= $pager['totalPages'] ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Next Button -->
                    <li class="page-item <?= !$pager['hasNext'] ? 'disabled' : '' ?>">
                        <a class="page-link" 
                           href="<?= base_url('admin/users?page=' . ($pager['currentPage'] + 1) . '&search=' . urlencode($search ?? '') . '&user_status=' . ($userStatus ?? 'all') . '&kyc_status=' . ($kycStatus ?? 'all')) ?>"
                           aria-label="Next">
                            <i class="fa-solid fa-chevron-right"></i>
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

<!-- Broadcast Message Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1" aria-labelledby="broadcastModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="broadcastModalLabel">
                    <i class="fa-solid fa-megaphone text-primary me-2"></i> Broadcast Message
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body pt-0">
                <form id="broadcastForm" action="<?= base_url('admin/broadcast-message') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <!-- Broadcast Title -->
                    <div class="mb-4">
                        <label for="broadcastTitle" class="form-label fw-semibold">
                            <i class="fa-solid fa-heading me-1 text-muted"></i> Judul Broadcast
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control border-1 border-light rounded-3 py-2" 
                               id="broadcastTitle" 
                               name="broadcastTitle" 
                               placeholder="Masukkan judul broadcast"
                               required>
                        <div class="form-text text-muted small">Maksimal 100 karakter</div>
                    </div>

                    <!-- Broadcast Message -->
                    <div class="mb-4">
                        <label for="broadcastMessage" class="form-label fw-semibold">
                            <i class="fa-solid fa-message me-1 text-muted"></i> Pesan Broadcast
                            <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control border-1 border-light rounded-3 py-2" 
                                  id="broadcastMessage" 
                                  name="broadcastMessage"
                                  rows="4" 
                                  placeholder="Masukkan pesan yang akan dikirim ke semua user"
                                  required></textarea>
                        <div class="form-text text-muted small">Gunakan format yang jelas dan informatif</div>
                    </div>

                    <!-- Broadcast Type -->
                    <div class="mb-4">
                        <label for="broadcastType" class="form-label fw-semibold">
                            <i class="fa-solid fa-tag me-1 text-muted"></i> Tipe Broadcast
                        </label>
                        <div class="row g-2" id="broadcastTypeOptions">
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="broadcastType" id="typeInfo" value="info" checked>
                                <label class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-2" for="typeInfo">
                                    <i class="fa-solid fa-circle-info fa-lg mb-1"></i>
                                    <span class="small">Info</span>
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="broadcastType" id="typeSuccess" value="success">
                                <label class="btn btn-outline-success w-100 d-flex flex-column align-items-center py-2" for="typeSuccess">
                                    <i class="fa-solid fa-circle-check fa-lg mb-1"></i>
                                    <span class="small">Success</span>
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="broadcastType" id="typeWarning" value="warning">
                                <label class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-2" for="typeWarning">
                                    <i class="fa-solid fa-triangle-exclamation fa-lg mb-1"></i>
                                    <span class="small">Warning</span>
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="broadcastType" id="typeError" value="error">
                                <label class="btn btn-outline-danger w-100 d-flex flex-column align-items-center py-2" for="typeError">
                                    <i class="fa-solid fa-circle-xmark fa-lg mb-1"></i>
                                    <span class="small">Error</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="fa-solid fa-eye me-1 text-muted"></i> Preview
                        </label>
                        <div class="border rounded-3 p-3 bg-light" id="broadcastPreview">
                            <div class="d-flex align-items-start gap-2">
                                <div id="previewIcon" class="text-info">
                                    <i class="fa-solid fa-circle-info fa-lg"></i>
                                </div>
                                <div>
                                    <h6 id="previewTitle" class="fw-bold mb-1 text-info">Judul Broadcast</h6>
                                    <p id="previewMessage" class="mb-0 text-muted">Pesan broadcast akan muncul di sini...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4" id="sendBroadcastBtn">
                            <i class="fa-solid fa-paper-plane me-1"></i> Kirim Broadcast
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-3 text-white fw-semibold">Mengirim broadcast...</div>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white border-0">
            <i class="fa-solid fa-circle-check me-2"></i>
            <strong class="me-auto">Berhasil!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">Broadcast berhasil dikirim ke semua user!</div>
    </div>
</div>

<!-- Error Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white border-0">
            <i class="fa-solid fa-circle-xmark me-2"></i>
            <strong class="me-auto">Error!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <span id="errorMessage">Terjadi kesalahan saat mengirim broadcast</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Broadcast elements
    const broadcastBtn = document.getElementById('broadcastBtn');
    const broadcastModal = new bootstrap.Modal(document.getElementById('broadcastModal'));
    const broadcastForm = document.getElementById('broadcastForm');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    const successToast = new bootstrap.Toast(document.getElementById('successToast'));
    const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
    
    // Preview elements
    const previewIcon = document.getElementById('previewIcon');
    const previewTitle = document.getElementById('previewTitle');
    const previewMessage = document.getElementById('previewMessage');
    const broadcastPreview = document.getElementById('broadcastPreview');
    
    // Type color mapping
    const typeColors = {
        'info': { color: 'info', icon: 'fa-circle-info' },
        'success': { color: 'success', icon: 'fa-circle-check' },
        'warning': { color: 'warning', icon: 'fa-triangle-exclamation' },
        'error': { color: 'danger', icon: 'fa-circle-xmark' }
    };
    
    // Update preview function
    function updatePreview() {
        const title = document.getElementById('broadcastTitle').value || 'Judul Broadcast';
        const message = document.getElementById('broadcastMessage').value || 'Pesan broadcast akan muncul di sini...';
        const type = document.querySelector('input[name="broadcastType"]:checked').value;
        
        const color = typeColors[type].color;
        const icon = typeColors[type].icon;
        
        // Update preview content
        previewIcon.innerHTML = `<i class="fa-solid ${icon} fa-lg"></i>`;
        previewTitle.textContent = title;
        previewMessage.textContent = message;
        
        // Update preview styling
        previewIcon.className = `text-${color}`;
        previewTitle.className = `fw-bold mb-1 text-${color}`;
        
        // Update border color
        broadcastPreview.className = `border border-${color} border-2 rounded-3 p-3 bg-${color} bg-opacity-10`;
    }
    
    // Event listeners for preview
    document.getElementById('broadcastTitle').addEventListener('input', updatePreview);
    document.getElementById('broadcastMessage').addEventListener('input', updatePreview);
    document.querySelectorAll('input[name="broadcastType"]').forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });
    
    // Initialize preview
    updatePreview();
    
    // Open modal
    broadcastBtn.addEventListener('click', function() {
        broadcastModal.show();
    });
    
    // Auto-submit form when filter dropdowns change
    document.getElementById('userStatusFilter')?.addEventListener('change', function() {
        this.closest('form').submit();
    });
    
    document.getElementById('kycStatusFilter')?.addEventListener('change', function() {
        this.closest('form').submit();
    });
    
    // Real-time search with debounce (optional)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.closest('form').submit();
            }, 800);
        });
    }
});
</script>

<?= $this->endSection() ?>