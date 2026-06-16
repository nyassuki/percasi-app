<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Dashboard Overview</h3>
    <button class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i> Download Report</button>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-bold">Total Atlet</p>
                        <h4 class="mb-0 fw-bold">1,240</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="fa-solid fa-chess-knight text-primary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-bold">Turnamen Aktif</p>
                        <h4 class="mb-0 fw-bold">5</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="fa-solid fa-trophy text-success fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-bold">Klub Terdaftar</p>
                        <h4 class="mb-0 fw-bold">85</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="fa-solid fa-building-shield text-warning fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-bold">Live Matches</p>
                        <h4 class="mb-0 fw-bold">12</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="fa-solid fa-gamepad text-danger fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-calendar-check me-2 text-primary"></i> Turnamen Terbaru</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Nama Turnamen</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-4 fw-bold">Kejurnas Catur 2025</td>
                        <td>Klasik / Junior</td>
                        <td>20 Okt 2025</td>
                        <td>250 Atlet</td>
                        <td><span class="badge bg-success">Open</span></td>
                        <td><button class="btn btn-sm btn-outline-primary">Detail</button></td>
                    </tr>
                    <tr>
                        <td class="ps-4 fw-bold">Rapid Chess Online Monthly</td>
                        <td>Rapid / Umum</td>
                        <td>25 Okt 2025</td>
                        <td>120 Atlet</td>
                        <td><span class="badge bg-warning text-dark">Draft</span></td>
                        <td><button class="btn btn-sm btn-outline-primary">Detail</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
