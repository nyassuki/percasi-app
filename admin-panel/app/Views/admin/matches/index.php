<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-chess-board me-2"></i>All Matches
            </h1>
            <p class="text-muted mb-0">View and manage all chess matches in the system</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('admin/matches/export') ?>" class="btn btn-success btn-gradient shadow-sm">
                <i class="fas fa-file-export me-2"></i>Export
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <?php if ($pager->getTotal() > 0): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fas fa-chess-board fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold"><?= $pager->getTotal() ?></h5>
                            <p class="text-muted mb-0">Total Matches</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success bg-opacity-10 text-success rounded-circle">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold"><?= count(array_filter($matches, fn($m) => $m->status === 'completed')) ?></h5>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-warning bg-opacity-10 text-warning rounded-circle">
                                <i class="fas fa-play-circle fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold"><?= count(array_filter($matches, fn($m) => $m->status === 'ongoing')) ?></h5>
                            <p class="text-muted mb-0">Ongoing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-info bg-opacity-10 text-info rounded-circle">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold"><?= count(array_filter($matches, fn($m) => $m->status === 'pending_start')) ?></h5>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Card dengan glass effect -->
    <div class="card glass-card border-0 shadow-lg mb-4">
        <div class="card-header bg-transparent py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-sliders-h me-2 text-primary"></i>Filters & Search
                </h5>
                <button class="btn btn-sm btn-outline-primary rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-1"></i>Toggle
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body pt-0">
                <form method="get" class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Status</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-flag text-primary"></i>
                            </span>
                            <select name="status" class="form-control form-control-sm border-start-0 ps-0">
                                <option value="">All Status</option>
                                <option value="pending_start" <?= ($filters['status'] ?? '') == 'pending_start' ? 'selected' : '' ?>>Pending Start</option>
                                <option value="ongoing" <?= ($filters['status'] ?? '') == 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="aborted" <?= ($filters['status'] ?? '') == 'aborted' ? 'selected' : '' ?>>Aborted</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Result</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-trophy text-warning"></i>
                            </span>
                            <select name="result" class="form-control form-control-sm border-start-0 ps-0">
                                <option value="">All Results</option>
                                <option value="1-0" <?= ($filters['result'] ?? '') == '1-0' ? 'selected' : '' ?>>White Wins (1-0)</option>
                                <option value="0-1" <?= ($filters['result'] ?? '') == '0-1' ? 'selected' : '' ?>>Black Wins (0-1)</option>
                                <option value="1/2-1/2" <?= ($filters['result'] ?? '') == '1/2-1/2' ? 'selected' : '' ?>>Draw (½-½)</option>
                                <option value="ongoing" <?= ($filters['result'] ?? '') == 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Tournament</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-trophy text-success"></i>
                            </span>
                            <select name="tournament_id" class="form-control form-control-sm border-start-0 ps-0">
                                <option value="">All Tournaments</option>
                                <?php foreach ($tournaments as $tournament): ?>
                                    <option value="<?= $tournament->id ?>" <?= ($filters['tournament_id'] ?? '') == $tournament->id ? 'selected' : '' ?>>
                                        <?= esc($tournament->title) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Player</label>
                        <div class="input-group input-group-sm select2-input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-user text-info"></i>
                            </span>
                            <select name="player_id" 
                                    class=" select2 js-select-player"
                                    data-placeholder="Select Player...">
                                <option value="">All Players</option>
                                <?php foreach ($players as $player): ?>
                                    <option value="<?= $player['id'] ?>" <?= ($filters['player_id'] ?? '') == $player['id'] ? 'selected' : '' ?>>
                                        <?= esc($player['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-search text-secondary"></i>
                            </span>
                            <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" 
                                   placeholder="Search by player, tournament, or match ID..." 
                                   value="<?= esc($filters['search'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <label class="form-label small text-uppercase fw-bold text-muted">Items per page</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-list-ol text-dark"></i>
                            </span>
                            <select name="per_page" class="form-control form-control-sm border-start-0 ps-0" onchange="this.form.submit()">
                                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10 items</option>
                                <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20 items</option>
                                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50 items</option>
                                <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100 items</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm">
                                <i class="fas fa-filter me-1"></i>Apply Filters
                            </button>
                            <a href="<?= site_url('matches') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2 text-primary"></i>Matches List
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-database me-1"></i>
                    <?= $pager->getTotal() ?> Total
                </span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0" style="width: 70px;">
                                <span class="text-muted small fw-bold">#ID</span>
                            </th>
                            <th class="py-3 border-0">
                                <span class="text-muted small fw-bold">Players</span>
                            </th>
                            <th class="py-3 border-0 text-center" style="width: 120px;">
                                <span class="text-muted small fw-bold">Result</span>
                            </th>
                            <th class="py-3 border-0 text-center" style="width: 130px;">
                                <span class="text-muted small fw-bold">Status</span>
                            </th>
                            <th class="py-3 border-0 text-center" style="width: 140px;">
                                <span class="text-muted small fw-bold">Tournament</span>
                            </th>
                            <th class="py-3 border-0 text-center" style="width: 100px;">
                                <span class="text-muted small fw-bold">Round</span>
                            </th>
                            <th class="py-3 border-0 text-center" style="width: 150px;">
                                <span class="text-muted small fw-bold">Start Time</span>
                            </th>
                            <th class="pe-4 py-3 border-0 text-center" style="width: 120px;">
                                <span class="text-muted small fw-bold">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($matches)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon mb-3">
                                            <i class="fas fa-chess-board fa-3x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">No matches found</h5>
                                        <p class="text-muted mb-4">Try adjusting your filters or create a new match</p>
                                        <a href="<?= site_url('matches/create') ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i>Create New Match
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($matches as $match): ?>
                                <tr class="match-row">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="match-id-badge">
                                                #<?= $match->match_id ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="player-chip bg-white border rounded-3 p-2 me-3 shadow-sm">
                                                <div class="d-flex align-items-center">
                                                    <div class="player-color-indicator bg-light border rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                                                        <i class="fas fa-chess-king fa-xs text-gray-700"></i>
                                                    </div>
                                                    <div>
                                                        <div class="small fw-bold">W#<?= $match->white_player_id ?><br><small><?= $match->white_username ?></small></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-muted mx-2">
                                                <i class="fas fa-swords"></i>
                                            </div>
                                            <div class="player-chip bg-dark border rounded-3 p-2 shadow-sm">
                                                <div class="d-flex align-items-center">
                                                    <div class="player-color-indicator bg-dark border rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                                                        <i class="fas fa-chess-king fa-xs text-white"></i>
                                                    </div>
                                                    <div>
                                                        <div class="small fw-bold text-white">B#<?= $match->black_player_id ?><br><small><?= $match->black_username ?></small></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="result-badge-container">
                                            <?php if ($match->result == '1-0'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">
                                                    <i class="fas fa-crown me-1"></i>1-0
                                                </span>
                                            <?php elseif ($match->result == '0-1'): ?>
                                                <span class="badge bg-dark bg-opacity-10 text-white border border-dark border-opacity-25 rounded-pill px-3 py-2">
                                                    <i class="fas fa-crown me-1"></i>0-1
                                                </span>
                                            <?php elseif ($match->result == '1/2-1/2'): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-2">
                                                    <i class="fas fa-handshake me-1"></i>½-½
                                                </span>
                                            <?php elseif ($match->result == 'ongoing'): ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-2">
                                                    <i class="fas fa-spinner fa-spin me-1"></i>Ongoing
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-2">
                                                    <i class="fas fa-times me-1"></i>Aborted
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($match->win_reason): ?>
                                                <div class="small text-muted mt-1">
                                                    <?= ucfirst(str_replace('_', ' ', $match->win_reason)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($match->status == 'ongoing'): ?>
                                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 rounded-pill px-3 py-2">
                                                <i class="fas fa-circle me-1 fa-xs"></i>Live
                                            </span>
                                        <?php elseif ($match->status == 'completed'): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 rounded-pill px-3 py-2">
                                                <i class="fas fa-check-circle me-1 fa-xs"></i>Completed
                                            </span>
                                        <?php elseif ($match->status == 'pending_start'): ?>
                                            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 rounded-pill px-3 py-2">
                                                <i class="fas fa-clock me-1 fa-xs"></i>Pending
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill px-3 py-2">
                                                <i class="fas fa-ban me-1 fa-xs"></i>Aborted
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($match->tournament_id): ?>
                                            <a href="<?= site_url('tournaments/' . $match->tournament_id) ?>" class="text-decoration-none">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">
                                                    <i class="fas fa-trophy me-1"></i>T#<?= $match->tournament_id ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($match->round_number): ?>
                                            <div class="round-badge">
                                                <span class="badge bg-light text-dark border rounded-circle p-2" style="width: 40px; height: 40px; line-height: 24px;">
                                                    <?= $match->round_number ?>
                                                </span>
                                                <div class="small text-muted mt-1">Round</div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="datetime-badge">
                                            <div class="fw-bold">
                                                <?= date('M d', strtotime($match->start_time ?? $match->created_at)) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?= date('H:i', strtotime($match->start_time ?? $match->created_at)) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="action-buttons">
                                            <a href="<?= site_url("admin/matches/{$match->match_id}") ?>" 
                                               class="btn btn-sm btn-outline-primary rounded-circle" 
                                               title="View Details"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (session()->get('is_admin')): ?>
                                                <a href="<?= site_url("admin/matches/{$match->id}/update-result") ?>" 
                                                   class="btn btn-sm btn-outline-warning rounded-circle ms-1"
                                                   title="Update Result"
                                                   data-bs-toggle="tooltip">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination dengan styling -->
        <?php if ($pager->getPageCount() > 1): ?>
            <?php
            $total = $pager->getTotal();
            $currentPage = $pager->getCurrentPage();
            $perPageValue = $pager->getPerPage();
            
            $firstItem = $total > 0 ? ($currentPage - 1) * $perPageValue + 1 : 0;
            $lastItem = min($currentPage * $perPageValue, $total);
            ?>
            <div class="card-footer bg-white py-3 border-top">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="mb-2 mb-md-0">
                        <span class="text-muted small">
                            Showing <span class="fw-bold text-dark"><?= $firstItem ?></span> to 
                            <span class="fw-bold text-dark"><?= $lastItem ?></span> of 
                            <span class="fw-bold text-dark"><?= $total ?></span> matches
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Previous Page Link -->
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-start" href="?page=<?= $currentPage - 1 ?>&<?= http_build_query($filters) ?>" aria-label="Previous">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link rounded-start">
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Page Numbers -->
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($pager->getPageCount(), $startPage + 4);
                                $startPage = max(1, $endPage - 4);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Next Page Link -->
                                <?php if ($currentPage < $pager->getPageCount()): ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-end" href="?page=<?= $currentPage + 1 ?>&<?= http_build_query($filters) ?>" aria-label="Next">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link rounded-end">
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <div class="ms-3">
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                Page <?= $currentPage ?> of <?= $pager->getPageCount() ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card-footer bg-white py-3 border-top text-center">
                <span class="text-muted small">
                    Showing <?= count($matches) ?> of <?= $pager->getTotal() ?> matches
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Custom Styles */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #2af598 0%, #009efd 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.page-title.text-gradient {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.btn-gradient {
    background: var(--primary-gradient);
    border: none;
    color: white;
    transition: all 0.3s ease;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}

.btn-success.btn-gradient {
    background: var(--success-gradient);
}

.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-hover {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.match-row {
    transition: all 0.2s ease;
}

.match-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
    transform: scale(1.002);
}

.match-id-badge {
    font-family: 'Courier New', monospace;
    background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
    color: #667eea;
    border: 1px solid rgba(102, 126, 234, 0.2);
}

.player-chip {
    transition: all 0.3s ease;
    min-width: 120px;
}

.player-chip:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.result-badge-container .badge {
    transition: all 0.3s ease;
    min-width: 80px;
}

.result-badge-container .badge:hover {
    transform: scale(1.05);
}

.round-badge .badge {
    font-size: 16px;
    font-weight: bold;
}

.datetime-badge {
    font-family: 'Segoe UI', system-ui, sans-serif;
}

.action-buttons .btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.action-buttons .btn:hover {
    transform: rotate(5deg) scale(1.1);
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state-icon {
    opacity: 0.5;
}

.pagination .page-item.active .page-link {
    background: var(--primary-gradient);
    border-color: transparent;
    color: white;
}

.pagination .page-link {
    margin: 0 2px;
    border-radius: 8px !important;
    border: 1px solid #e0e0e0;
    color: #667eea;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background-color: rgba(102, 126, 234, 0.1);
    border-color: #667eea;
}

.input-group-text {
    border-right: none;
    background-color: #f8f9fa !important;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    border-color: #667eea;
}

.border-bottom {
    border-bottom: 2px solid rgba(102, 126, 234, 0.1) !important;
}

.table th {
    font-weight: 600;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.table td {
    padding-top: 1rem;
    padding-bottom: 1rem;
    vertical-align: middle;
}

.table tbody tr {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.player-color-indicator {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.bg-dark {
    background-color: #212529 !important;
}
</style>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    
    // Auto refresh for live matches
    setInterval(function() {
        var liveMatches = document.querySelectorAll('.badge-warning');
        if (liveMatches.length > 0) {
            location.reload();
        }
    }, 30000); // Refresh every 30 seconds if there are live matches
});
</script>

<?= $this->endSection() ?>