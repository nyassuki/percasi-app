<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
/* Custom Styles for User Detail */
.profile-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
}

.avatar-container {
    position: relative;
    display: inline-block;
}

.avatar-container img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.status-badge {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 20px;
    height: 20px;
    border: 3px solid #fff;
}

.quick-stats-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 16px;
    transition: all 0.3s ease;
}

.quick-stats-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.stats-number {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.tab-header-custom {
    border-bottom: 1px solid #e2e8f0;
}

.tab-header-custom .nav-pills .nav-link {
    border-radius: 12px 12px 0 0;
    padding: 12px 24px;
    transition: all 0.3s ease;
    background: transparent;
    color: #64748b;
    border: none;
    position: relative;
}

.tab-header-custom .nav-pills .nav-link.active {
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
    border-bottom: 3px solid #3b82f6;
}

.tab-header-custom .nav-pills .nav-link::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    background: #3b82f6;
    transition: width 0.3s ease;
}

.tab-header-custom .nav-pills .nav-link:hover::after {
    width: 100%;
}

.info-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-4px);
    border-color: rgba(59, 130, 246, 0.3);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.balance-display {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #10b981, #3b82f6);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.document-preview {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.document-preview:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.document-preview-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.history-table-row {
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
}

.history-table-row:hover {
    transform: translateX(4px);
    border-left-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.rating-change {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
}

.rating-change.positive {
    background: rgba(34, 197, 94, 0.1);
    color: #10b981;
}

.rating-change.negative {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.rating-change.neutral {
    background: rgba(251, 191, 36, 0.1);
    color: #f59e0b;
}

.action-button {
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.loading-placeholder {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.status-indicator.active {
    background: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.status-indicator.inactive {
    background: #64748b;
    box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.2);
}

.status-indicator.banned {
    background: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
}
</style>

<!-- Header -->
<!-- Header -->
 
<div class="profile-header mb-4 text-white">
    <div class="d-flex justify-content-between align-items-start">
        <div class="d-flex align-items-center gap-4">
            <div class="avatar-container">
                <?php
                    $avatarUrl = !empty($u['avatar_url']) 
                        ? str_replace("public", "", $u['avatar_url']) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode($u['username'] ?? 'User') . '&length=2&rounded=true';
                        $fallbackUrl = 'https://ui-avatars.com/api/?name=' . urlencode($u['username'] ?? 'User') . '&length=1&rounded=true';
                        ?>
                <img src="<?=  $avatarUrl;?>" 
                     class="rounded-circle"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['full_name']) ?>&background=3b82f6&color=fff&size=120'">
                <span class="status-badge rounded-circle bg-<?= $u['user_status']=='ACT'?'success':($u['user_status']=='BND'?'danger':'secondary') ?>"></span>
            </div>
            <div>
                <h3 class="fw-bold mb-1"><?= esc($u['full_name']) ?></h3>
                <p class="text-light mb-2">
                    <i class="fa-solid fa-at me-2"></i>@<?= esc($u['username']) ?>
                    <span class="mx-3">•</span>
                    <i class="fa-solid fa-id-card me-2"></i>ID: #<?= $u['id'] ?>
                </p>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="fa-solid fa-chess-king me-2"></i>
                        Rating ELO: <strong class="ms-2"><?= $u['standard_rating'] ?: '1200' ?></strong>
                    </span>
                    <span class="badge bg-primary bg-opacity-20 text-white px-3 py-2">
                        <i class="fa-solid fa-calendar me-2"></i>
                        Bergabung: <?= date('d M Y', strtotime($u['created_at'] ?? 'now')) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?php if($u['user_status'] == 'ACT'): ?>
                <form action="<?= base_url('admin/users/updateStatus/'.$u['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_status" value="BND">
                    <button type="submit" class="action-button btn btn-outline-danger">
                        <i class="fa-solid fa-ban"></i>
                        Ban User
                    </button>
                </form>
            <?php elseif($u['user_status'] == 'BND'): ?>
                <form action="<?= base_url('admin/users/updateStatus/'.$u['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_status" value="ACT">
                    <button type="submit" class="action-button btn btn-outline-success">
                        <i class="fa-solid fa-check-circle"></i>
                        Activate User
                    </button>
                </form>
            <?php endif; ?>
            <!-- Tombol untuk membuka modal -->
            <button class="action-button btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                <i class="fa-solid fa-message"></i>
                Kirim pesan
            </button>
        </div>
    </div>
</div>

<!-- Modal untuk mengirim notifikasi -->
<div class="modal fade modal-notification" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-20 p-3 rounded-circle">
                        <i class="fa-solid fa-bell fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="sendNotificationModalLabel">
                            Kirim Notifikasi ke <?= esc($u['full_name']) ?>
                        </h5>
                        <p class="mb-0 text-white text-opacity-75 small">@<?= esc($u['username']) ?> • ID: #<?= $u['id'] ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/user-notification/'.$u['id']) ?>" method="POST" id="notificationForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <!-- Form Input -->
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label for="notificationTitle" class="form-label fw-semibold">
                                    <i class="fa-solid fa-heading me-2 text-primary"></i>
                                    Judul Notifikasi
                                </label>
                                <input type="text" 
                                       class="form-control py-3 border-2" 
                                       id="notificationTitle" 
                                       name="title" 
                                       placeholder="Masukkan judul notifikasi..."
                                       maxlength="100"
                                       required>
                                <div class="character-count" id="titleCount">0/100 karakter</div>
                            </div>

                            <div class="mb-4">
                                <label for="notificationMessage" class="form-label fw-semibold">
                                    <i class="fa-solid fa-message me-2 text-primary"></i>
                                    Pesan Notifikasi
                                </label>
                                <textarea class="form-control border-2" 
                                          id="notificationMessage" 
                                          name="message" 
                                          rows="6" 
                                          placeholder="Tulis pesan notifikasi di sini..."
                                          maxlength="500"
                                          style="resize: none;"
                                          required></textarea>
                                <div class="character-count" id="messageCount">0/500 karakter</div>
                            </div>

                        <div class="mb-4">
                        <label for="notificationType" class="form-label fw-semibold">
                            <i class="fa-solid fa-tag me-1 text-muted"></i> Tipe Notifikasi
                        </label>
                        <div class="row g-2" id="notificationTypeOptions">
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="notificationType" id="typeInfo" value="info" checked>
                                <label class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-2" for="typeInfo">
                                    <i class="fa-solid fa-circle-info fa-lg mb-1"></i>
                                    <span class="small">Info</span>
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="notificationType" id="typeSuccess" value="success">
                                <label class="btn btn-outline-success w-100 d-flex flex-column align-items-center py-2" for="typeSuccess">
                                    <i class="fa-solid fa-circle-check fa-lg mb-1"></i>
                                    <span class="small">Success</span>
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="notificationType" id="typeWarning" value="warning">
                                <label class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-2" for="typeWarning">
                                    <i class="fa-solid fa-triangle-exclamation fa-lg mb-1"></i>
                                    <span class="small">Warning</span>
                                </label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="notificationType" id="typeError" value="error">
                                <label class="btn btn-outline-danger w-100 d-flex flex-column align-items-center py-2" for="typeError">
                                    <i class="fa-solid fa-circle-xmark fa-lg mb-1"></i>
                                    <span class="small">Error</span>
                                </label>
                            </div>
                            
                        </div>
                    </div>

                            <!-- Additional Options -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold mb-3">
                                    <i class="fa-solid fa-gear me-2 text-primary"></i>
                                    Opsi Tambahan
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sendEmail" name="send_email">
                                    <label class="form-check-label" for="sendEmail">
                                        Kirim juga melalui email
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="saveTemplate" name="save_template">
                                    <label class="form-check-label" for="saveTemplate">
                                        Simpan sebagai template
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="col-md-4">
                            <div class="preview-card sticky-top" style="top: 20px;">
                                <h6 class="fw-bold mb-3 text-center">
                                    <i class="fa-solid fa-eye me-2"></i>
                                    Preview Notifikasi
                                </h6>
                                
                                <div class="notification-icon info" id="previewIcon">
                                    <i class="fa-solid fa-circle-info"></i>
                                </div>
                                
                                <h6 class="fw-bold mb-2" id="previewTitle">
                                    Judul Notifikasi
                                </h6>
                                
                                <p class="text-muted small mb-3" id="previewMessage">
                                    Pesan notifikasi akan muncul di sini...
                                </p>
                                
                                <div class="border-top pt-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="fa-solid fa-user text-muted"></i>
                                        <small class="text-muted">Penerima:</small>
                                        <small class="fw-semibold"><?= esc($u['full_name']) ?></small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-clock text-muted"></i>
                                        <small class="text-muted">Waktu:</small>
                                        <small class="fw-semibold" id="previewTime">Sekarang</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-paper-plane me-2"></i>
                        Kirim Notifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="row g-4">
    <!-- Left Column -->
    <div class="col-xl-4">
        <!-- Quick Stats -->
        <div class="info-card mb-4">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2 text-primary">
                <i class="fa-solid fa-chart-column"></i>
                Statistik Pertandingan [<?= $stats['rankTitle'] ?? 0 ?>]
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="quick-stats-card text-center">
                        <div class="stats-number text-success mb-1"><?= $stats['totalGames'] ?? 0 ?></div>
                        <small class="text-muted">Total game</small>
                    </div>
                </div>
                 <div class="col-6">
                    <div class="quick-stats-card text-center">
                        <div class="stats-number text-success mb-1"><?= $stats['ratingTrend'] ?? 0 ?></div>
                        <small class="text-muted">Rating trend</small>
                    </div>
                </div>
                 <div class="col-6">
                    <div class="quick-stats-card text-center">
                        <div class="stats-number text-success mb-1"><?= $stats['wins'] ?? 0 ?></div>
                        <small class="text-muted">Kemenangan</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="quick-stats-card text-center">
                        <div class="stats-number text-warning mb-1"><?= $stats['draws'] ?? 0 ?></div>
                        <small class="text-muted">Seri</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="quick-stats-card text-center">
                        <div class="stats-number text-danger mb-1"><?= $stats['losses'] ?? 0 ?></div>
                        <small class="text-muted">Kekalahan</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="quick-stats-card text-center">
                    
                        <div class="stats-number text-info mb-1"><?= $stats['winrate'] ?? 0 ?>%</div>
                        <small class="text-muted">Win Rate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Controls -->
        <div class="info-card mb-4">
         
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2 text-primary">
                <i class="fa-solid fa-address-card"></i>
                Informasi Kontak
            </h6>
            <div class="list-group list-group-flush bg-transparent">
                <div class="list-group-item bg-transparent border-0 d-flex align-items-center gap-3 py-2 px-0">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                        <i class="fa-solid fa-envelope text-primary"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Email</small>
                        <div class="fw-medium"><?= esc($u['email']) ?></div>
                    </div>
                </div>
                <div class="list-group-item bg-transparent border-0 d-flex align-items-center gap-3 py-2 px-0">
                    <div class="rounded-circle bg-success bg-opacity-10 p-2">
                        <i class="fa-solid fa-phone text-success"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Telepon</small>
                        <div class="fw-medium"><?= $u['phone_number'] ? esc($u['phone_number']) : '<span class="text-muted">Belum diisi</span>' ?></div>
                    </div>
                </div>
         
        </div>

        </div>

        <!-- Contact Info -->
        

        <!-- Financial Card -->
        <div class="info-card">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2 text-primary">
                <i class="fa-solid fa-wallet"></i>
                Finansial & Bank
            </h6>
            
            <!-- Balance -->
            <div class="mb-4">
                <small class="text-muted d-block mb-2">Total Saldo</small>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fa-solid fa-money-bill-wave fa-lg text-success"></i>
                    </div>
                    <div>
                        <div class="balance-display">Rp <?= number_format($u['balance'], 0, ',', '.') ?></div>
                        <small class="text-muted">Saldo tersedia</small>
                    </div>
                </div>
            </div>

            <!-- Bank Accounts -->
            <div class="mb-4">
                <small class="text-muted d-block mb-2">Rekening Bank</small>
                <div class="bg-dark bg-opacity-25 rounded-3 p-3 border border-dark border-opacity-25">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-medium"><?= $u['bank_name'] ?: 'Belum diisi' ?></div>
                        <span class="badge bg-primary bg-opacity-25 text-primary">Primary</span>
                    </div>
                    <small class="text-muted d-block">No. Rekening</small>
                    <div class="fw-bold"><?= $u['account_number'] ?: '-' ?></div>
                </div>
            </div>

            <!-- Virtual Account -->
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Virtual Account</small>
                    <span class="badge bg-info bg-opacity-10 text-info"><?= count($va) ?> Akun</span>
                </div>
                <div class="list-group list-group-flush bg-transparent">
                    <?php $n=0; foreach($va as $virtual_account): $n++; ?>
                    <div class="list-group-item bg-transparent border-0 d-flex align-items-center gap-3 py-2 px-0">
                        <div class="rounded-circle bg-info bg-opacity-10 p-2">
                            <span class="fw-bold"><?= $n ?></span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium"><?= $virtual_account['bank_code'] ?: '-' ?></div>
                            <small class="text-muted"><?= $virtual_account['va_number'] ?: '-' ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($va)): ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fa-solid fa-building-columns fa-2x mb-2"></i>
                        <div>Belum ada Virtual Account</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-xl-8">
        <div class="card border-0 shadow-lg overflow-hidden">
            <!-- Tab Header -->
            <div class="card-header bg-white border-0 px-4 pt-4 tab-header-custom">
                <ul class="nav nav-pills" id="userTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active d-flex align-items-center gap-2" 
                                id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">
                            <i class="fa-solid fa-user"></i>
                            <span>Profil & Alamat</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-2" 
                                id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc" type="button">
                            <i class="fa-solid fa-id-card"></i>
                            <span>Verifikasi KYC</span>
                            <?php if($u['kyc_status'] == 'pending'): ?>
                            <span class="badge bg-warning rounded-pill pulse-animation">!</span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-2" 
                                id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">
                            <i class="fa-solid fa-history"></i>
                            <span>Riwayat Pertandingan</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="card-body p-4">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <h6 class="fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-map-location-dot text-primary"></i>
                            Informasi Alamat Lengkap
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Provinsi</small>
                                    <div class="fw-medium"><?= $u['prov'] ?: 'Belum diisi' ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Kabupaten/Kota</small>
                                    <div class="fw-medium"><?= $u['reg'] ?: 'Belum diisi' ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Kecamatan</small>
                                    <div class="fw-medium"><?= $u['dist'] ?: 'Belum diisi' ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Kelurahan</small>
                                    <div class="fw-medium"><?= $u['subdist'] ?: 'Belum diisi' ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Kode Pos</small>
                                    <div class="fw-medium"><?= $u['postal_code'] ?: '-' ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 h-100">
                                    <small class="text-muted d-block mb-1">Status</small>
                                    <div class="fw-medium">
                                        <span class="badge bg-<?= $u['user_status']=='ACT'?'success':($u['user_status']=='BND'?'danger':'secondary') ?>">
                                            <?= $u['user_status']=='ACT'?'Active':($u['user_status']=='BND'?'Banned':'Inactive') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block mb-1">Alamat Lengkap</small>
                                    <div class="fw-medium"><?= $u['address_line'] ?: 'Belum diisi' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KYC Tab -->
                    <div class="tab-pane fade" id="kyc" role="tabpanel">
                        <!-- Status Banner -->
                        <div class="alert alert-<?= $u['kyc_status']=='verified'?'success':($u['kyc_status']=='rejected'?'danger':'warning') ?> d-flex align-items-center gap-3 border-0 shadow-sm">
                            <i class="fa-solid fa-<?= $u['kyc_status']=='verified'?'check-circle':($u['kyc_status']=='rejected'?'xmark-circle':'clock') ?> fa-2x"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Status KYC: <?= strtoupper($u['kyc_status']) ?></h6>
                                <?php if($u['kyc_verified_at']): ?>
                                <small class="d-block">Terverifikasi pada <?= date('d M Y H:i', strtotime($u['kyc_verified_at'])) ?></small>
                                <?php else: ?>
                                <small class="d-block">Menunggu verifikasi admin</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Document Images -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="document-preview">
                                    <div class="document-preview-header">
                                        <i class="fa-solid fa-id-card me-2"></i>
                                        <span class="fw-bold">Foto KTP/ID</span>
                                    </div>
                                    <?php
                                    $kyc_document_url = str_replace("public","", $u['kyc_document_url']);
                                    $kyc_selfie_url = str_replace("public","", $u['kyc_selfie_url']);
                                    ?>
                                    <?php if($kyc_document_url) : ?>
                                    <img src="<?= $kyc_document_url;?>" 
                                         class="img-fluid w-100"
                                         style="height: 300px; object-fit: cover;"
                                         onerror="">
                                    <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-image fa-2x mb-3"></i>
                                        <div class="fw-medium">Belum diupload</div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="document-preview">
                                    <div class="document-preview-header">
                                        <i class="fa-solid fa-user me-2"></i>
                                        <span class="fw-bold">Foto Selfie dengan KTP</span>
                                    </div>
                                    <?php if($kyc_selfie_url): ?>
                                    <img src="<?= $kyc_selfie_url; ?>" 
                                         class="img-fluid w-100"
                                         style="height: 300px; object-fit: cover;"
                                         onerror="">
                                    <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-image fa-2x mb-3"></i>
                                        <div class="fw-medium">Belum diupload</div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Approval Actions -->
                        <?php if($u['kyc_status'] == 'pending'): ?>
                        <div class="border-top pt-4 mt-4">
                            <h6 class="fw-bold mb-3">Tindakan Verifikasi</h6>
                            <form action="<?= base_url('admin/users/approveKyc/'.$u['id']) ?>" method="post">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <button name="status" value="verified" 
                                                class="action-button btn btn-success w-100">
                                            <i class="fa-solid fa-check"></i>
                                            Approve KYC
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" 
                                                class="action-button btn btn-outline-danger w-100"
                                                data-bs-toggle="collapse" data-bs-target="#rejectBox">
                                            <i class="fa-solid fa-xmark"></i>
                                            Reject KYC
                                        </button>
                                    </div>
                                    <div class="col-12 collapse" id="rejectBox">
                                        <div class="bg-light rounded-3 p-4 mt-3">
                                            <label class="form-label fw-bold mb-3">Alasan Penolakan</label>
                                            <textarea name="reason" class="form-control" rows="4" 
                                                      placeholder="Berikan alasan penolakan verifikasi KYC..." 
                                                      style="resize: none;"></textarea>
                                            <div class="mt-3 text-end">
                                                <button name="status" value="rejected" 
                                                        class="action-button btn btn-danger px-4">
                                                    Kirim Penolakan
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- History Tab -->
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-trophy text-primary"></i>
                                Riwayat Pertandingan
                            </h6>
                            <a href="#" class="action-button btn btn-outline-primary btn-sm">
                                Lihat Semua
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>

                        <?php if(!empty($matches)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless pagingTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Tanggal</th>
                                        <th class="py-3 ps-4">Opponent</th>
                                        <th class="py-3">Role</th>
                                        <th class="py-3">Opponent Rating</th>
                                        <th class="py-3 pe-4 text-end">result</th>
                                        <th class="py-3">Reason</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($matches as $m): ?>
                                        <?php
                                            if($m['result'] == "1/2-1/2" || $m['result'] == "0-0") {
                                                $win_result = "DRAW";
                                            } else if($m['user_role'] == "white") {
                                                if($m['result'] == "1-0") {
                                                    $win_result = "WIN";
                                                } else if($m['result'] == "0-1") {
                                                    $win_result = "LOSSES";
                                                }
                                            } else if($m['user_role'] == "black") {
                                                if($m['result'] == "1-0") {
                                                    $win_result = "LOSSES";
                                                } else if($m['result'] == "0-1") {
                                                    $win_result = "WIN";
                                                }
                                            }

                                        ?>
                                    <tr class="history-table-row">
                                         <td><?=$m['date'];?></td>
                                         <td><?=$m['opponent_username'];?></td>
                                         <td><?=$m['user_role'];?></td>
                                         <td><?=$m['opponent_rating'];?></td>
                                         <td class="text-end"><?=$m['result'];?> (<?=$win_result;?>)</td>
                                         <td><?=$m['win_reason'];?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-light p-5 d-inline-block mb-3">
                                <i class="fa-solid fa-chess fa-3x text-muted"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Belum ada riwayat pertandingan</h6>
                            <p class="text-muted">User ini belum pernah bertanding</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Auto-expand reject reason textarea when showing
document.getElementById('rejectBox')?.addEventListener('show.bs.collapse', function () {
    setTimeout(() => {
        this.querySelector('textarea').focus();
    }, 300);
});

// Add animation class to tabs
document.querySelectorAll('#userTab .nav-link').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('#userTab .nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Image error handling
document.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', function() {
        this.src = 'https://ui-avatars.com/api/?name=User&background=3b82f6&color=fff';
    });
});
</script>
<?= $this->endSection() ?>