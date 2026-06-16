<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Sidebar Toggle -->
        <button type="button" id="sidebarCollapse" class="btn btn-light border rounded-circle p-2 me-3">
            <i class="fa-solid fa-bars fa-sm"></i>
        </button>

        <!-- Brand/Logo (Optional) -->
        <a class="navbar-brand d-none d-md-block fw-bold text-primary ms-2" href="<?= base_url('admin/dashboard') ?>">
            Admin Panel
        </a>

        <div class="ms-auto d-flex align-items-center">
            <!-- Notification Bell -->
            <div class="dropdown me-3">
                <a href="#" class="nav-link text-dark position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-regular fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.25em 0.4em">
                        3
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="min-width: 300px;" aria-labelledby="notificationDropdown">
                    <li class="dropdown-header bg-light py-3 px-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Notifikasi</h6>
                            <a href="#" class="small text-primary">Tandai semua terbaca</a>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item px-3 py-3 border-bottom" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                        <i class="fa-solid fa-user-check text-primary fa-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0 small">Pengguna baru mendaftar</p>
                                    <small class="text-muted">2 menit yang lalu</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item px-3 py-3" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                        <i class="fa-solid fa-check text-success fa-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0 small">Pesanan berhasil diproses</p>
                                    <small class="text-muted">1 jam yang lalu</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="dropdown-footer text-center py-3 bg-light">
                        <a href="#" class="small text-primary">Lihat semua notifikasi</a>
                    </li>
                </ul>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark p-1 rounded" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="transition: all 0.3s;">
                    <div class="position-relative me-2">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($auth->admin_name ?? 'Admin') ?>&background=0D8ABC&color=fff&bold=true" 
                             alt="User" width="40" height="40" class="rounded-circle border border-3 border-light shadow-sm">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                    </div>
                    <div class="d-none d-md-block text-start">
                        <span class="d-block fw-semibold small" style="line-height: 1.2;"><?= $auth->admin_name ?? 'Administrator' ?></span>
                        <small class="text-muted" style="font-size: 0.75rem;"><?= $auth->admin_email ?? 'admin@example.com' ?></small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="min-width: 220px;" aria-labelledby="userDropdown">
                    <li class="dropdown-header px-3 py-2">
                        <div class="fw-semibold small"><?= $auth->admin_name ?? 'admin';?></div>
                        <small class="text-muted"><?= $auth->admin_email?? 'admin@example.com' ?></small>
                    </li>
                    <li><hr class="dropdown-divider my-2"></li>
                    <li>
                        <a class="dropdown-item px-3 py-2" href="<?= base_url('admin/profile') ?>">
                            <i class="fa-solid fa-user-circle me-2 text-primary"></i>
                            <span>Profil Saya</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item px-3 py-2" href="<?= base_url('admin/settings') ?>">
                            <i class="fa-solid fa-sliders me-2 text-primary"></i>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-2"></li>
                    <li>
                        <a class="dropdown-item px-3 py-2 text-danger" href="<?= base_url('/logout') ?>">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>
                            <span>Keluar</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
