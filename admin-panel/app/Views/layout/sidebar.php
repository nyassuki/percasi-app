<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center justify-content-center">
            <i class="fa-solid fa-chess fa-2x me-2 text-primary"></i>
            <div class="logo-text">
                <h5 class="mb-0 fw-bold">Chess Admin</h5>
                <small style="font-size:0.6em; color:#94a3b8;">Management System</small>
            </div>
        </div>
        <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <div class="sidebar-content">
        <ul class="list-unstyled components mb-0">
            <?php
            // Mendapatkan current URL segment
            $current_url = uri_string();
            $segment = explode('/', $current_url);
            $main_section = isset($segment[1]) ? $segment[1] : 'dashboard';
            ?>
            
            <!-- Dashboard -->
            <li class="<?= ($main_section == 'dashboard') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/dashboard') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-gauge-high sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Dashboard</span>
                    <?php if($main_section == 'dashboard'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Master Data Section -->
            <li class="sidebar-heading">
                <span>Master Data</span>
            </li>
            
            <li class="<?= ($main_section == 'users' && !in_array('profile', $segment)) ? 'active' : '' ?>">
                <a href="<?= base_url('admin/users') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-user-group sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Data Atlet</span>
                    <?php if($main_section == 'users' && !in_array('profile', $segment)): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="<?= ($main_section == 'clubs') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/clubs') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-chess-queen sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Klub Catur</span>
                    <?php if($main_section == 'clubs'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="<?= ($main_section == 'officials') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/officials') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-user-tie sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Wasit & Pengurus</span>
                    <?php if($main_section == 'officials'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Kompetisi Section -->
            <li class="sidebar-heading">
                <span>Kompetisi</span>
            </li>
            
            <li class="<?= ($main_section == 'tournaments') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/tournaments') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-trophy sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Turnamen</span>
                    <?php if($main_section == 'tournaments'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="<?= ($main_section == 'matches') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/matches') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-chess-board sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Pertandingan</span>
                    <?php if($main_section == 'matches' && !isset($segment[2])): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="<?= ($main_section == 'matches' && isset($segment[2]) && $segment[2] == 'live') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/matches/live') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-broadcast-tower sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Pertandingan Live</span>
                    <?php if($main_section == 'matches' && isset($segment[2]) && $segment[2] == 'live'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="<?= ($main_section == 'ratings') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/ratings') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-chart-line sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Rating ELO</span>
                    <?php if($main_section == 'ratings'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Keuangan Section -->
            <li class="sidebar-heading">
                <span>Keuangan</span>
            </li>
            
            <li class="<?= ($main_section == 'transactions' && !isset($segment[2])) ? 'active' : '' ?>">
                <a href="<?= base_url('admin/transactions') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-receipt sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Transaksi</span>
                    <?php if($main_section == 'transactions' && !isset($segment[2])): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="<?= ($main_section == 'transactions' && isset($segment[2]) && $segment[2] == 'summary') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/transactions/summary') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-balance-scale sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Ringkasan Saldo</span>
                    <?php if($main_section == 'transactions' && isset($segment[2]) && $segment[2] == 'summary'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Accounting Dropdown -->
            <?php
            $is_accounting_active = ($main_section == 'accounting' || (isset($segment[2]) && in_array($segment[2], ['journal', 'posting', 'ics', 'tb', 'neraca', 'pl', 'acs', 'income-statement', 'trial-balance', 'account-statement'])));
            $is_accounting_open = $is_accounting_active ? 'show' : '';
            ?>
            <li class="has-submenu <?= $is_accounting_active ? 'active' : '' ?>">
                <a href="#accountingSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $is_accounting_active ? 'true' : 'false' ?>" class="sidebar-link dropdown-toggle <?= $is_accounting_active ? '' : 'collapsed' ?>">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-calculator sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Akuntansi</span>
                    <i class="fa-solid fa-chevron-right dropdown-arrow ms-auto"></i>
                    <?php if($is_accounting_active): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
                <ul class="collapse list-unstyled submenu <?= $is_accounting_open ? 'show' : '' ?>" id="accountingSubmenu">
                    <li class="<?= (isset($segment[2]) && $segment[2] == 'journal') ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/accounting') ?>">
                            <i class="fa-solid fa-book me-2"></i>
                            <span>General Ledger</span>
                        </a>
                    </li>
                    <li class="<?= (isset($segment[2]) && $segment[2] == 'posting') ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/accounting/posting') ?>">
                            <i class="fa-solid fa-file-invoice me-2"></i>
                            <span>Jurnal Umum</span>
                        </a>
                    </li>
                    <li class="<?= (isset($segment[2]) && $segment[2] == 'income-statement') ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/accounting/income-statement') ?>">
                            <i class="fa-solid fa-chart-column me-2"></i>
                            <span>Laba Rugi</span>
                        </a>
                    </li>
                    <li class="<?= (isset($segment[2]) && $segment[2] == 'trial-balance') ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/accounting/trial-balance') ?>">
                            <i class="fa-solid fa-scale-balanced me-2"></i>
                            <span>Neraca Saldo</span>
                        </a>
                    </li>
                    <li class="<?= (isset($segment[2]) && $segment[2] == 'neraca') ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/accounting/neraca') ?>">
                            <i class="fa-solid fa-scale-unbalanced me-2"></i>
                            <span>Neraca</span>
                        </a>
                    </li>
                    <li class="<?= (isset($segment[2]) && $segment[2] == 'pl') ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/accounting/pl') ?>">
                            <i class="fa-solid fa-money-bill-trend-up me-2"></i>
                            <span>Profit & Loss</span>
                        </a>
                    </li>
                    <li class="<?= (isset($segment[2]) && $segment[2] == 'account-statement') ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/accounting/account-statement') ?>">
                            <i class="fa-solid fa-file-contract me-2"></i>
                            <span>Laporan Akun</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="<?= ($main_section == 'transactions' && isset($segment[2]) && $segment[2] == 'finance') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/transactions/finance') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-chart-pie sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Laporan Keuangan</span>
                    <?php if($main_section == 'transactions' && isset($segment[2]) && $segment[2] == 'finance'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Konten Section -->
            <li class="sidebar-heading">
                <span>Konten</span>
            </li>
            
            <li class="<?= ($main_section == 'news') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/news') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-newspaper sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Berita</span>
                    <?php if($main_section == 'news'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- System Section -->
            <li class="sidebar-heading">
                <span>System</span>
            </li>
            
            <li class="<?= ($main_section == 'users' && in_array('profile', $segment)) ? 'active' : '' ?>">
                <a href="<?= base_url('admin/users/profile') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-user-gear sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Manajemen User</span>
                    <?php if($main_section == 'users' && in_array('profile', $segment)): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="<?= ($main_section == 'settings') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/settings') ?>" class="sidebar-link">
                    <div class="sidebar-icon-wrapper">
                        <i class="fa-solid fa-sliders sidebar-icon"></i>
                    </div>
                    <span class="sidebar-text">Pengaturan</span>
                    <?php if($main_section == 'settings'): ?>
                        <span class="active-indicator"></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
        
        <!-- User Profile -->
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    <?php
                    // Tampilkan inisial jika ada nama
                    if (!empty($auth->admin_name)) {
                        $name_parts = explode(' ', $auth->admin_name);
                        $initials = '';
                        foreach ($name_parts as $part) {
                            if (!empty($part)) {
                                $initials .= strtoupper(substr($part, 0, 1));
                                if (strlen($initials) >= 2) break;
                            }
                        }
                        if (empty($initials)) {
                            $initials = strtoupper(substr($auth->admin_email, 0, 1));
                        }
                        echo '<span class="avatar-initials">' . $initials . '</span>';
                    } else {
                        echo '<i class="fa-solid fa-circle-user"></i>';
                    }
                    ?>
                </div>
                <div class="user-info">
                    <h6><?= !empty($auth->admin_name) ? $auth->admin_name : 'Admin User'; ?></h6>
                    <small><?= $auth->admin_email; ?></small>
                </div>
                <a href="<?= base_url('admin/auth/logout') ?>" class="logout-btn" title="Logout">
                    <i class="fa-solid fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
/* Sidebar Base Styles */
.sidebar {
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    color: #cbd5e1;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    width: 260px;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

/* Header */
.sidebar-header {
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(15, 23, 42, 0.9);
    position: relative;
}

.sidebar-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    color: #94a3b8;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.sidebar-toggle:hover {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

/* Content */
.sidebar-content {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 80px);
    overflow-y: auto;
    padding: 0.5rem 0;
}

/* Menu Items */
.components {
    padding: 0;
}

.components li a.sidebar-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #cbd5e1;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    position: relative;
    margin: 0.15rem 0.5rem;
    border-radius: 8px;
}

.components li a.sidebar-link:hover {
    background: rgba(59, 130, 246, 0.1);
    color: #ffffff;
    border-left-color: rgba(59, 130, 246, 0.5);
    transform: translateX(2px);
}

/* Active State */
.components li.active > a.sidebar-link {
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.05) 100%);
    color: #3b82f6;
    border-left-color: #3b82f6;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
}

.components li.active > a.sidebar-link .sidebar-icon {
    color: #3b82f6;
}

.active-indicator {
    position: absolute;
    right: 1rem;
    width: 6px;
    height: 6px;
    background: #3b82f6;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
    }
    70% {
        box-shadow: 0 0 0 5px rgba(59, 130, 246, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
    }
}

/* Icons */
.sidebar-icon-wrapper {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(59, 130, 246, 0.08);
    margin-right: 12px;
    transition: all 0.3s ease;
}

.sidebar-icon {
    font-size: 1rem;
    color: #94a3b8;
    transition: all 0.3s ease;
}

.components li a.sidebar-link:hover .sidebar-icon-wrapper {
    background: rgba(59, 130, 246, 0.15);
    transform: scale(1.05);
}

.components li.active .sidebar-icon-wrapper {
    background: rgba(59, 130, 246, 0.2);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

/* Headings */
.sidebar-heading {
    padding: 1rem 1rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 0.5rem;
    opacity: 0.8;
}

.sidebar-heading span {
    padding-left: 0.5rem;
}

/* Submenu */
.has-submenu .dropdown-arrow {
    transition: transform 0.3s ease;
    font-size: 0.8rem;
    margin-left: auto;
}

.has-submenu.active .dropdown-arrow {
    transform: rotate(90deg);
    color: #3b82f6;
}

.submenu {
    background: rgba(30, 41, 59, 0.5);
    padding-left: 3rem;
    border-left: 1px solid rgba(59, 130, 246, 0.1);
    margin: 0.25rem 0;
}

.submenu li a {
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
    color: #94a3b8;
    text-decoration: none;
    display: block;
    transition: all 0.3s ease;
    border-radius: 6px;
    margin: 0.15rem 0.5rem;
}

.submenu li a:hover {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    padding-left: 1.2rem;
}

.submenu li.active a {
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.15);
    font-weight: 500;
}

.submenu li a i {
    width: 16px;
    text-align: center;
}

/* User Profile */
.sidebar-footer {
    margin-top: auto;
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(15, 23, 42, 0.9);
}

.user-profile {
    display: flex;
    align-items: center;
    position: relative;
}

.user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-weight: 600;
    color: white;
    flex-shrink: 0;
}

.user-avatar .avatar-initials {
    font-size: 1rem;
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-info h6 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #f1f5f9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-info small {
    font-size: 0.75rem;
    color: #94a3b8;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.logout-btn {
    color: #94a3b8;
    background: transparent;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.logout-btn:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Scrollbar Styling */
.sidebar-content::-webkit-scrollbar {
    width: 4px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: rgba(30, 41, 59, 0.3);
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 4px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Responsive */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .sidebar-header {
        padding: 1rem;
    }
    
    .logo-text {
        font-size: 0.9rem;
    }
}

/* Badge for notifications (optional) */
.sidebar-badge {
    position: absolute;
    right: 1rem;
    background: #ef4444;
    color: white;
    font-size: 0.6rem;
    padding: 0.1rem 0.4rem;
    border-radius: 10px;
    font-weight: 600;
}
</style>

<script>
// Toggle sidebar on mobile
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('show');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    if (window.innerWidth <= 992 && 
        sidebar.classList.contains('show') &&
        !sidebar.contains(event.target) &&
        !toggleBtn?.contains(event.target)) {
        sidebar.classList.remove('show');
    }
});

// Auto-close submenus on mobile when clicking elsewhere
document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && !this.classList.contains('dropdown-toggle')) {
            document.getElementById('sidebar').classList.remove('show');
        }
    });
});
</script>