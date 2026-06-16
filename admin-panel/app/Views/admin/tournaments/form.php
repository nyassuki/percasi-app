<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header dengan Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('admin/tournaments') ?>">Turnamen</a></li>
            <li class="breadcrumb-item active"><?= isset($tournament) ? 'Edit' : 'Tambah' ?> Turnamen</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-xl-12 col-lg-12">
            <!-- Progress Steps -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-3">
                    <div class="steps">
                        <div class="step-item active">
                            <div class="step-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="step-label">Informasi</div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="step-label">Pengaturan</div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="step-label">Hadiah</div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="step-label">Selesai</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="card shadow-lg border-0">
                <!-- Card Header -->
                <div class="card-header py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center text-white">
                        <div class="icon-wrapper bg-white bg-opacity-20 rounded-circle p-3 me-3">
                            <i class="fas fa-trophy fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-1 fw-bold"><?= isset($tournament) ? 'Edit Turnamen' : 'Buat Turnamen Baru' ?></h3>
                            <p class="mb-0 opacity-75"><?= isset($tournament) ? 'Perbarui informasi turnamen' : 'Isi formulir untuk membuat turnamen baru' ?></p>
                        </div>
                    </div>
                </div>

                <!-- Error Messages -->
                <?php if (session()->has('errors')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show mx-4 mt-4 mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                            <div>
                                <h6 class="mb-1">Terjadi Kesalahan</h6>
                                <ul class="mb-0 ps-3">
                                    <?php foreach (session('errors') as $error) : ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif ?>

                <!-- Form Content -->
                <div class="card-body p-4">
                    <form action="<?= isset($tournament) ? base_url('admin/tournaments/update/' . $tournament->id) : base_url('admin/tournaments/store') ?>" method="POST" id="tournamentForm">
                        <?= csrf_field() ?>

                        <!-- Section 1: Basic Information -->
                        <div class="form-section mb-5">
                            <h5 class="section-title mb-4">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Informasi Dasar
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Judul Turnamen <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control form-control-lg" 
                                               value="<?= old('title', $tournament->title ?? '') ?>" 
                                               placeholder="Contoh: Turnamen Catur Blitz Championship 2024" required>
                                        <div class="form-text">Buat judul yang menarik dan deskriptif</div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Deskripsi Turnamen</label>
                                        <textarea name="description" class="form-control" rows="4" 
                                                  placeholder="Jelaskan tentang turnamen ini, aturan khusus, dan informasi penting lainnya"><?= old('description', $tournament->description ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Time Settings -->
                        <div class="form-section mb-5">
                            <h5 class="section-title mb-4">
                                <i class="fas fa-clock text-warning me-2"></i>
                                Pengaturan Waktu
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Akhir pendaftaran <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="datetime-local" name="registration_close" class="form-control" 
                                                   value="<?= old('start_time', isset($tournament) ? date('Y-m-d\TH:i', strtotime($tournament->registration_close)) : date('Y-m-d\TH:i')) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Waktu Mulai <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="datetime-local" name="start_time" class="form-control" 
                                                   value="<?= old('start_time', isset($tournament) ? date('Y-m-d\TH:i', strtotime($tournament->start_time)) : date('Y-m-d\TH:i')) ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Waktu Selesai <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="datetime-local" name="end_time" class="form-control" 
                                                   value="<?= old('end_time', isset($tournament) ? date('Y-m-d\TH:i', strtotime($tournament->end_time)) : '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Game Settings -->
                        <div class="form-section mb-5">
                            <h5 class="section-title mb-4">
                                <i class="fas fa-chess text-success me-2"></i>
                                Pengaturan Permainan
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Tipe Kontrol Waktu <span class="text-danger">*</span></label>
                                        <div class="row g-2" id="timeControlType">
                                            <?php 
                                            $timeTypes = [
                                                'standard' => ['Standar', 'fas fa-hourglass-half', 'bg-primary'],
                                                'rapid' => ['Rapid', 'fas fa-bolt', 'bg-warning'],
                                                'blitz' => ['Blitz', 'fas fa-fire', 'bg-danger'],
                                                'bullet' => ['Bullet', 'fas fa-rocket', 'bg-dark']
                                            ];
                                            $selectedType = old('time_control_type', $tournament->time_control_type ?? 'rapid');
                                            ?>
                                            <?php foreach($timeTypes as $value => $data): ?>
                                                <div class="col-6">
                                                    <input type="radio" name="time_control_type" value="<?= $value ?>" 
                                                           id="type_<?= $value ?>" class="d-none" 
                                                           <?= $selectedType == $value ? 'checked' : '' ?>>
                                                    <label for="type_<?= $value ?>" class="time-type-card w-100 text-center p-3 rounded border cursor-pointer">
                                                        <div class="<?= $data[2] ?> rounded-circle p-3 d-inline-flex mb-2">
                                                            <i class="<?= $data[1] ?> fa-lg text-white"></i>
                                                        </div>
                                                        <div class="fw-bold"><?= $data[0] ?></div>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label fw-bold">Base Time (menit) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-stopwatch"></i></span>
                                                    <input type="number" name="time_control_base" class="form-control" 
                                                           value="<?= old('time_control_base', $tournament->time_control_base ?? '3') ?>" 
                                                           min="1" max="60" required>
                                                    <span class="input-group-text">min</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label fw-bold">Increment (detik) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-plus-circle"></i></span>
                                                    <input type="number" name="time_control_increment" class="form-control" 
                                                           value="<?= old('time_control_increment', $tournament->time_control_increment ?? '2') ?>" 
                                                           min="0" max="30" required>
                                                    <span class="input-group-text">sec</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="time-preview p-3 bg-light rounded text-center">
                                                <div class="text-muted small mb-1">Preview Kontrol Waktu</div>
                                                <div class="display-6 fw-bold" id="timePreview">3+2</div>
                                                <div class="text-muted small mt-1" id="timeTypeLabel">Rapid Chess</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Fees & Prizes -->
                        <div class="form-section mb-5">
                            <h5 class="section-title mb-4">
                                <i class="fas fa-coins text-warning me-2"></i>
                                Biaya & Hadiah
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Biaya Pendaftaran</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="entry_fee" class="form-control" 
                                                   value="<?= old('entry_fee', $tournament->entry_fee ?? '0') ?>" 
                                                   min="0">
                                            <span class="input-group-text bg-light">Gratis</span>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="freeEntry" onchange="toggleEntryFee()">
                                            <label class="form-check-label" for="freeEntry">
                                                Gratis untuk semua peserta
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Total Hadiah</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="prize_pool" class="form-control" 
                                                   value="<?= old('prize_pool', $tournament->prize_pool ?? '0') ?>" 
                                                   min="0">
                                        </div>
                                        <div class="prize-suggestions mt-2">
                                            <div class="text-muted small mb-1">Saran hadiah:</div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPrize(50000)">50K</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPrize(100000)">100K</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPrize(250000)">250K</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPrize(500000)">500K</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="finance-summary p-3 bg-gradient-light rounded">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="text-muted small mb-1">Biaya Masuk</div>
                                                <div class="h5 fw-bold" id="entryFeeDisplay">Rp0</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted small mb-1">Total Hadiah</div>
                                                <div class="h5 fw-bold text-success" id="prizeDisplay">Rp0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Format & Status -->
                        <div class="form-section mb-5">
                            <h5 class="section-title mb-4">
                                <i class="fas fa-list-ol text-info me-2"></i>
                                Format & Status
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Format Turnamen</label>
                                        <select name="format" class="form-select">
                                            <?php foreach(['swiss', 'round_robin', 'knockout', 'arena'] as $format): ?>
                                                <option value="<?= $format ?>" <?= old('format', $tournament->format ?? '') == $format ? 'selected' : '' ?>><?= $format ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                        <div class="row g-2">
                                            <?php 
                                            $statusOptions = [
                                                'registration' => ['Registrasi', 'info'],
                                                'active' => ['Aktif', 'success'],
                                                'completed' => ['Selesai', 'dark'],
                                                'cancelled' => ['Dibatalkan', 'danger'],
                                                'waiting' => ['Menunggu', 'warning']
                                            ];
                                            $selectedStatus = old('status', $tournament->status ?? 'registration');
                                            ?>
                                            <?php foreach($statusOptions as $value => $data): ?>
                                                <div class="col-6">
                                                    <input type="radio" name="status" value="<?= $value ?>" 
                                                           id="status_<?= $value ?>" class="status-radio d-none" 
                                                           <?= $selectedStatus == $value ? 'checked' : '' ?>>
                                                    <label for="status_<?= $value ?>" class="status-card w-100 p-2 rounded border text-center cursor-pointer">
                                                        <span class="badge bg-<?= $data[1] ?> me-1">●</span>
                                                        <?= $data[0] ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="border-top pt-4 mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="<?= base_url('admin/tournaments') ?>" class="btn btn-outline-secondary px-4">
                                        <i class="fas fa-arrow-left me-2"></i> Kembali
                                    </a>
                                </div>
                                <div>
                                    <button type="reset" class="btn btn-outline-danger me-2 px-4">
                                        <i class="fas fa-redo me-2"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary px-5 py-2">
                                        <i class="fas fa-save me-2"></i> 
                                        <?= isset($tournament) ? 'Perbarui Turnamen' : 'Buat Turnamen' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <div class="alert alert-info mt-4">
                <div class="d-flex">
                    <i class="fas fa-lightbulb fa-2x me-3 text-info"></i>
                    <div>
                        <h6 class="mb-1">Tips Membuat Turnamen</h6>
                        <p class="mb-0 small">Pastikan semua informasi sudah benar sebelum menyimpan. Anda dapat mengubah status turnamen kapan saja di halaman manajemen.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Breadcrumb */
    .breadcrumb {
        background: transparent;
        padding: 0;
    }

    /* Progress Steps */
    .steps {
        display: flex;
        justify-content: space-between;
        position: relative;
    }
    .steps:before {
        content: '';
        position: absolute;
        top: 24px;
        left: 10%;
        right: 10%;
        height: 2px;
        background: #e9ecef;
        z-index: 1;
    }
    .step-item {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }
    .step-item.active .step-icon {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    .step-icon {
        width: 48px;
        height: 48px;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        transition: all 0.3s;
    }
    .step-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* Form Sections */
    .form-section {
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    .section-title {
        color: #495057;
        font-weight: 600;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }

    /* Time Type Cards */
    .time-type-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background: white;
    }
    .time-type-card:hover {
        transform: translateY(-3px);
        border-color: #28a745;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    input[type="radio"]:checked + .time-type-card {
        border-color: #28a745;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    }

    /* Status Cards - PERBAIKAN PENTING */
    .status-card {
        transition: all 0.3s ease;
        background: white;
        border: 2px solid #dee2e6;
    }
    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    /* Status card yang dipilih */
    .status-card.active {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-color: #28a745 !important;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    }
    /* CSS untuk radio checked (fallback) */
    input[type="radio"]:checked + .status-card {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-color: #28a745 !important;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    }

    /* Custom Input Groups */
    .input-group-text {
        background: #f8f9fa;
        border-color: #dee2e6;
    }

    /* Preview Boxes */
    .time-preview {
        border: 1px dashed #adb5bd;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    .time-preview:hover {
        border-color: #28a745;
        background: #f1f8e9;
    }
    .finance-summary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
    }

    /* Cursor Pointer */
    .cursor-pointer {
        cursor: pointer;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .steps {
            flex-wrap: wrap;
        }
        .step-item {
            width: 50%;
            margin-bottom: 1rem;
        }
        .step-label {
            font-size: 0.75rem;
        }
        .time-type-card {
            padding: 1rem 0.5rem;
        }
        .time-preview .display-6 {
            font-size: 1.75rem;
        }
    }

    /* Animation for time preview */
    @keyframes pulse-green {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .time-preview-update {
        animation: pulse-green 0.5s ease;
    }
    
    /* Animation for status card */
    @keyframes fadeInStatus {
        from { opacity: 0.8; transform: scale(0.98); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .status-card.active {
        animation: fadeInStatus 0.3s ease;
    }
</style>

<script>
    // Update time preview dengan animasi
    function updateTimePreview() {
        const base = document.querySelector('input[name="time_control_base"]').value;
        const increment = document.querySelector('input[name="time_control_increment"]').value;
        const type = document.querySelector('input[name="time_control_type"]:checked').value;
        
        // Update teks
        document.getElementById('timePreview').textContent = `${base}+${increment}`;
        
        // Update label tipe
        const typeLabels = {
            'standard': 'Catur Standar',
            'rapid': 'Catur Rapid',
            'blitz': 'Catur Blitz',
            'bullet': 'Catur Bullet'
        };
        document.getElementById('timeTypeLabel').textContent = typeLabels[type] || 'Catur';
        
        // Tambahkan animasi
        const previewBox = document.querySelector('.time-preview');
        previewBox.classList.add('time-preview-update');
        setTimeout(() => {
            previewBox.classList.remove('time-preview-update');
        }, 500);
    }

    // Toggle free entry
    function toggleEntryFee() {
        const freeCheckbox = document.getElementById('freeEntry');
        const feeInput = document.querySelector('input[name="entry_fee"]');
        
        if (freeCheckbox.checked) {
            feeInput.value = '0';
            feeInput.disabled = true;
            feeInput.classList.add('bg-light');
        } else {
            feeInput.disabled = false;
            feeInput.classList.remove('bg-light');
        }
        updateFinanceDisplay();
    }

    // Set prize amount
    function setPrize(amount) {
        const prizeInput = document.querySelector('input[name="prize_pool"]');
        prizeInput.value = amount;
        
        // Animasi untuk feedback visual
        const prizeDisplay = document.getElementById('prizeDisplay');
        prizeDisplay.classList.add('text-bounce');
        setTimeout(() => {
            prizeDisplay.classList.remove('text-bounce');
        }, 300);
        
        updateFinanceDisplay();
    }

    // Update finance display
    function updateFinanceDisplay() {
        const fee = document.querySelector('input[name="entry_fee"]').value || 0;
        const prize = document.querySelector('input[name="prize_pool"]').value || 0;
        
        document.getElementById('entryFeeDisplay').textContent = formatCurrency(fee);
        document.getElementById('prizeDisplay').textContent = formatCurrency(prize);
    }

    // Format currency
    function formatCurrency(amount) {
        const num = parseInt(amount);
        if (num === 0) return 'Gratis';
        return 'Rp' + num.toLocaleString('id-ID');
    }

    // PERBAIKAN: Fungsi untuk mengupdate status card
    function updateStatusCards() {
        // Hapus kelas active dari semua status card
        document.querySelectorAll('.status-card').forEach(card => {
            card.classList.remove('active');
        });
        
        // Tambahkan kelas active ke status card yang dipilih
        const activeRadio = document.querySelector('input[name="status"]:checked');
        if (activeRadio) {
            const activeLabel = document.querySelector(`label[for="${activeRadio.id}"]`);
            if (activeLabel) {
                activeLabel.classList.add('active');
            }
        }
    }

    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Update previews on input change
        document.querySelectorAll('input[name="time_control_base"], input[name="time_control_increment"]').forEach(input => {
            input.addEventListener('input', function() {
                updateTimePreview();
            });
        });
        
        // Handle time control type selection
        document.querySelectorAll('input[name="time_control_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                updateTimePreview();
            });
        });
        
        // Update finance display
        document.querySelectorAll('input[name="entry_fee"], input[name="prize_pool"]').forEach(input => {
            input.addEventListener('input', updateFinanceDisplay);
        });

        // PERBAIKAN PENTING: Event listener untuk status cards
        document.querySelectorAll('input[name="status"]').forEach(radio => {
            radio.addEventListener('change', updateStatusCards);
        });

        // Initialize status cards saat halaman dimuat
        updateStatusCards();

        // Initialize displays
        updateTimePreview();
        updateFinanceDisplay();
        
        // Style untuk tombol saran hadiah
        document.querySelectorAll('.prize-suggestions button').forEach(btn => {
            btn.addEventListener('click', function() {
                // Reset semua tombol
                document.querySelectorAll('.prize-suggestions button').forEach(b => {
                    b.classList.remove('btn-success');
                    b.classList.add('btn-outline-secondary');
                });
                
                // Highlight tombol yang dipilih
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-success');
            });
        });
    });

    // Form validation
    document.getElementById('tournamentForm').addEventListener('submit', function(e) {
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;
        
        if (startTime && endTime) {
            const start = new Date(startTime);
            const end = new Date(endTime);
            
            if (end <= start) {
                e.preventDefault();
                // Sweet alert atau modal untuk error
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Waktu Tidak Valid',
                        text: 'Waktu selesai harus setelah waktu mulai',
                        confirmButtonColor: '#667eea',
                    });
                } else {
                    alert('Waktu selesai harus setelah waktu mulai');
                }
                return false;
            }
        }
        
        return true;
    });

    // CSS untuk animasi tambahan
    const style = document.createElement('style');
    style.textContent = `
        .text-bounce {
            animation: bounce 0.3s ease;
        }
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* Warna asli untuk badge status (tidak diubah) */
        .badge.bg-info { background-color: #17a2b8 !important; }
        .badge.bg-success { background-color: #28a745 !important; }
        .badge.bg-dark { background-color: #343a40 !important; }
        .badge.bg-danger { background-color: #dc3545 !important; }
        .badge.bg-warning { background-color: #ffc107 !important; }
        
        /* Warna asli untuk time control cards (tidak diubah) */
        .bg-primary { background-color: #007bff !important; }
        .bg-warning { background-color: #ffc107 !important; }
        .bg-danger { background-color: #dc3545 !important; }
        .bg-dark { background-color: #343a40 !important; }
        
        /* Highlight untuk tombol hadiah yang dipilih */
        .prize-suggestions .btn-success {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
        }
    `;
    document.head.appendChild(style);
</script>

<?= $this->endSection() ?>