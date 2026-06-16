<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-chess-board me-2"></i>Match Details
            </h1>
            <p class="text-muted mb-0">Match ID: #<?= $match->match_id ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/matches" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Matches
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Match Info -->
        <div class="col-lg-8">
            <!-- Match Card -->
            <div class="card border-0 shadow-lg overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Match Information
                        </h5>
                        <div class="match-status">
                            <?php if ($match->status == 'ongoing'): ?>
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 rounded-pill px-3 py-2">
                                    <i class="fas fa-circle fa-xs me-1"></i>Live Match
                                </span>
                            <?php elseif ($match->status == 'completed'): ?>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 rounded-pill px-3 py-2">
                                    <i class="fas fa-check-circle fa-xs me-1"></i>Completed
                                </span>
                            <?php elseif ($match->status == 'pending_start'): ?>
                                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 rounded-pill px-3 py-2">
                                    <i class="fas fa-clock fa-xs me-1"></i>Pending Start
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill px-3 py-2">
                                    <i class="fas fa-ban fa-xs me-1"></i>Aborted
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Player Comparison -->
                    <div class="player-comparison mb-5">
                        <div class="row align-items-center">
                            <!-- White Player -->
                            <div class="col-md-5 text-end">
                                <div class="player-card player-white text-end">
                                    <div class="player-header mb-3">
                                        <div class="player-color-badge bg-white border rounded-pill d-inline-flex align-items-center px-3 py-2 mb-2 shadow-sm">
                                            <div class="color-indicator bg-light border rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                            <span class="fw-bold">White</span>
                                        </div>
                                        <h4 class="fw-bold mb-0">Player #<?= $match->white_player_id ?></h4>
                                        <?php if ($whitePlayer): ?>
                                            <p class="text-muted mb-0">@<?= esc($whitePlayer['username']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="player-avatar mb-3">
                                        <div class="avatar-lg bg-white border rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                                            <i class="fas fa-chess-king fa-2x text-gray-700"></i>
                                        </div>
                                    </div>
                                    <div class="player-stats">
                                        <div class="stat-item d-flex justify-content-end align-items-center mb-2">
                                            <span class="text-muted small me-2">Rating:</span>
                                            <span class="fw-bold">-</span>
                                        </div>
                                        <div class="stat-item d-flex justify-content-end align-items-center">
                                            <span class="text-muted small me-2">Wins:</span>
                                            <span class="fw-bold">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- VS Center -->
                            <div class="col-md-2 text-center">
                                <div class="vs-section position-relative">
                                    <div class="vs-circle mx-auto mb-3">
                                        <span class="vs-text">VS</span>
                                    </div>
                                    <div class="result-badge mb-3">
                                        <?php if ($match->result == '1-0'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-4 py-2">
                                                <i class="fas fa-crown me-1"></i>1-0
                                            </span>
                                        <?php elseif ($match->result == '0-1'): ?>
                                            <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 rounded-pill px-4 py-2">
                                                <i class="fas fa-crown me-1"></i>0-1
                                            </span>
                                        <?php elseif ($match->result == '1/2-1/2'): ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-4 py-2">
                                                <i class="fas fa-handshake me-1"></i>½-½
                                            </span>
                                        <?php elseif ($match->result == 'ongoing'): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-4 py-2">
                                                <i class="fas fa-spinner fa-spin me-1"></i>Ongoing
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-4 py-2">
                                                <i class="fas fa-times me-1"></i>Aborted
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($match->win_reason): ?>
                                        <div class="win-reason small text-muted">
                                            <?= ucfirst(str_replace('_', ' ', $match->win_reason)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Black Player -->
                            <div class="col-md-5">
                                <div class="player-card player-black">
                                    <div class="player-header mb-3">
                                        <div class="player-color-badge bg-dark border rounded-pill d-inline-flex align-items-center px-3 py-2 mb-2 shadow-sm">
                                            <div class="color-indicator bg-dark border rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                            <span class="fw-bold text-white">Black</span>
                                        </div>
                                        <h4 class="fw-bold mb-0">Player #<?= $match->black_player_id ?></h4>
                                        <?php if ($blackPlayer): ?>
                                            <p class="text-white mb-0">@<?= esc($blackPlayer['username']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="player-avatar mb-3">
                                        <div class="avatar-lg bg-dark border rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                                            <i class="fas fa-chess-king fa-2x text-white"></i>
                                        </div>
                                    </div>
                                    <div class="player-stats">
                                        <div class="stat-item d-flex align-items-center mb-2">
                                            <span class="text-muted small me-2">Rating:</span>
                                            <span class="fw-bold">-</span>
                                        </div>
                                        <div class="stat-item d-flex align-items-center">
                                            <span class="text-muted small me-2">Wins:</span>
                                            <span class="fw-bold">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Match Details Grid -->
                    <div class="match-details-grid">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="detail-card border rounded-3 p-4 h-100 bg-white shadow-sm">
                                    <h6 class="detail-title mb-3">
                                        <i class="fas fa-chess me-2 text-primary"></i>Match Settings
                                    </h6>
                                    <div class="detail-list">
                                        <div class="detail-item d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">Tournament:</span>
                                            <span class="fw-bold">
                                                <?php if ($tournament): ?>
                                                    <a href="<?= site_url("tournaments/{$tournament->id}") ?>" class="text-decoration-none">
                                                        <?= esc($tournament->name) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Not in tournament</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="detail-item d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">Round:</span>
                                            <span class="fw-bold">
                                                <?= $match->round_number ? "Round {$match->round_number}" : 'N/A' ?>
                                            </span>
                                        </div>
                                        <div class="detail-item d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">Status:</span>
                                            <span>
                                                <?php if ($match->status == 'ongoing'): ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Ongoing</span>
                                                <?php elseif ($match->status == 'completed'): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Completed</span>
                                                <?php elseif ($match->status == 'pending_start'): ?>
                                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Aborted</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="detail-item d-flex justify-content-between py-2">
                                            <span class="text-muted">Result:</span>
                                            <span>
                                                <?php if ($match->result == '1-0'): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">White Wins</span>
                                                <?php elseif ($match->result == '0-1'): ?>
                                                    <span class="badge bg-dark bg-opacity-10 text-dark rounded-pill px-3">Black Wins</span>
                                                <?php elseif ($match->result == '1/2-1/2'): ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Draw</span>
                                                <?php elseif ($match->result == 'ongoing'): ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">In Progress</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Aborted</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="detail-card border rounded-3 p-4 h-100 bg-white shadow-sm">
                                    <h6 class="detail-title mb-3">
                                        <i class="fas fa-clock me-2 text-primary"></i>Timeline
                                    </h6>
                                    <div class="detail-list">
                                        <div class="detail-item d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">Start Time:</span>
                                            <span class="fw-bold">
                                                <?= $match->start_time ? date('M d, Y H:i', strtotime($match->start_time)) : 'N/A' ?>
                                            </span>
                                        </div>
                                        <div class="detail-item d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">End Time:</span>
                                            <span class="fw-bold">
                                                <?= $match->end_time ? date('M d, Y H:i', strtotime($match->end_time)) : 'N/A' ?>
                                            </span>
                                        </div>
                                        <div class="detail-item d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">Duration:</span>
                                            <span class="fw-bold">
                                                <?php if ($match->start_time && $match->end_time): 
                                                    $start = strtotime($match->start_time);
                                                    $end = strtotime($match->end_time);
                                                    $duration = $end - $start;
                                                    echo gmdate("H:i:s", $duration);
                                                else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="detail-item d-flex justify-content-between py-2">
                                            <span class="text-muted">Created:</span>
                                            <span class="fw-bold">
                                                <?= date('M d, Y H:i', strtotime($match->created_at)) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Game Data -->
                    <?php if ($match->pgn_string || $match->fen): ?>
                    <div class="game-data mt-4">
                        <div class="row g-4">
                            <?php if ($match->pgn_string): ?>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white py-3 border-bottom">
                                        <h6 class="mb-0">
                                            <i class="fas fa-list-ol me-2 text-primary"></i>PGN Notation
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <pre class="pgn-display p-3 m-0 bg-light" style="max-height: 200px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 0.85rem;"><?= esc($match->pgn_string) ?></pre>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($match->fen): ?>
                            <div class="<?= $match->pgn_string ? 'col-md-6' : 'col-12' ?>">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white py-3 border-bottom">
                                        <h6 class="mb-0">
                                            <i class="fas fa-chess-board me-2 text-primary"></i>FEN Position
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="fen-display bg-light border rounded p-3">
                                            <code class="text-dark d-block" style="font-family: 'Courier New', monospace; font-size: 0.9rem;"><?= esc($match->fen) ?></code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Time Control Card -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-hourglass-half me-2 text-primary"></i>Time Control
                    </h6>
                </div>
                <div class="card-body p-4">
                    <?php if ($match->white_time_ms || $match->black_time_ms): ?>
                        <div class="time-control-display">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="time-card bg-white border rounded-3 p-3 text-center shadow-sm">
                                        <div class="time-color-indicator bg-light border rounded-circle mx-auto mb-2" style="width: 16px; height: 16px;"></div>
                                        <h6 class="text-muted mb-2">White Time</h6>
                                        <h3 class="fw-bold text-dark mb-0">
                                            <?= $match->white_time_ms ? gmdate("H:i:s", $match->white_time_ms / 1000) : '--:--' ?>
                                        </h3>
                                        <div class="mt-2">
                                            <span class="badge bg-light text-dark small">ms: <?= $match->white_time_ms ?? 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="time-card bg-dark border rounded-3 p-3 text-center shadow-sm">
                                        <div class="time-color-indicator bg-dark border rounded-circle mx-auto mb-2" style="width: 16px; height: 16px;"></div>
                                        <h6 class="text-white mb-2">Black Time</h6>
                                        <h3 class="fw-bold text-white mb-0">
                                            <?= $match->black_time_ms ? gmdate("H:i:s", $match->black_time_ms / 1000) : '--:--' ?>
                                        </h3>
                                        <div class="mt-2">
                                            <span class="badge bg-dark text-white small">ms: <?= $match->black_time_ms ?? 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($match->last_move_time): ?>
                                <div class="mt-4 text-center">
                                    <div class="last-move-info bg-light rounded-3 p-3">
                                        <i class="fas fa-history text-muted me-2"></i>
                                        <span class="text-muted">Last Move:</span>
                                        <span class="fw-bold ms-1"><?= date('H:i:s', strtotime($match->last_move_time)) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mb-3">
                                <i class="fas fa-hourglass-end fa-2x text-muted opacity-25"></i>
                            </div>
                            <p class="text-muted mb-0">No time data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-2 text-primary"></i>Match Actions
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="action-buttons d-grid gap-3">
                        <?php if ($match->status == 'ongoing'): ?>
                            <a href="<?= site_url("matches/{$match->match_id}/update-result") ?>" 
                               class="btn btn-warning btn-lg rounded-pill shadow-sm d-flex align-items-center justify-content-center">
                                <i class="fas fa-flag-checkered me-2"></i>
                                <span>End Match</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($match->pgn_string): ?>
                            <a href="<?= site_url("analysis/{$match->match_id}") ?>" 
                               class="btn btn-info btn-lg rounded-pill shadow-sm d-flex align-items-center justify-content-center">
                                <i class="fas fa-chart-line me-2"></i>
                                <span>Analyze Game</span>
                            </a>
                        <?php endif; ?>

                        <?php if (session()->get('is_admin')): ?>
                            <div class="admin-actions">
                                <div class="row g-2">
                                    <div class="col">
                                        <a href="<?= site_url("matches/{$match->match_id}/update-result") ?>" 
                                           class="btn btn-outline-warning rounded-pill shadow-sm w-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-edit me-1"></i>
                                            <span>Edit Result</span>
                                        </a>
                                    </div>
                                    <div class="col">
                                        <button type="button" class="btn btn-outline-danger rounded-pill shadow-sm w-100 d-flex align-items-center justify-content-center"
                                                onclick="confirmDelete(<?= $match->match_id ?>)">
                                            <i class="fas fa-trash me-1"></i>
                                            <span>Delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Analysis Card -->
            <?php if ($match->is_analyzed || $match->cheat_probability): ?>
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>Game Analysis
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($match->is_analyzed): ?>
                            <div class="analysis-item mb-3">
                                <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle fa-lg text-success me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Analysis Complete</h6>
                                            <p class="mb-0 small">This game has been fully analyzed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($match->cheat_probability): ?>
                            <?php
                            $cheatLevel = $match->cheat_probability > 0.7 ? 'danger' : ($match->cheat_probability > 0.3 ? 'warning' : 'success');
                            $cheatText = $match->cheat_probability > 0.7 ? 'High Risk' : ($match->cheat_probability > 0.3 ? 'Suspicious' : 'Clean');
                            ?>
                            <div class="analysis-item">
                                <div class="cheat-probability">
                                    <h6 class="mb-3">Cheat Detection Score</h6>
                                    <div class="progress-container mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small text-muted">Probability</span>
                                            <span class="fw-bold text-<?= $cheatLevel ?>"><?= number_format($match->cheat_probability * 100, 1) ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-<?= $cheatLevel ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= $match->cheat_probability * 100 ?>%"
                                                 aria-valuenow="<?= $match->cheat_probability * 100 ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="alert alert-<?= $cheatLevel ?> bg-<?= $cheatLevel ?> bg-opacity-10 border border-<?= $cheatLevel ?> border-opacity-25 rounded-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-shield-alt fa-lg text-<?= $cheatLevel ?> me-3"></i>
                                            <div>
                                                <h6 class="mb-1"><?= $cheatText ?></h6>
                                                <p class="mb-0 small">Cheat probability analysis</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Custom Styles */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.page-title.text-gradient {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.match-status .badge {
    font-size: 0.75rem;
    font-weight: 600;
}

.vs-section {
    position: relative;
    z-index: 1;
}

.vs-circle {
    width: 70px;
    height: 70px;
    background: var(--primary-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.player-card {
    padding: 1.5rem;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.player-card:hover {
    transform: translateY(-5px);
}

.player-white {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid rgba(0,0,0,0.1);
}

.player-black {
    background: linear-gradient(135deg, #212529 0%, #343a40 100%);
    color: white;
    border: 1px solid rgba(255,255,255,0.1);
}

.player-color-badge {
    backdrop-filter: blur(10px);
}

.player-white .player-color-badge {
    background: rgba(255, 255, 255, 0.9) !important;
}

.player-black .player-color-badge {
    background: rgba(0, 0, 0, 0.7) !important;
}

.avatar-lg {
    transition: all 0.3s ease;
}

.avatar-lg:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.detail-card {
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.detail-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.detail-title {
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #667eea;
}

.detail-item {
    font-size: 0.9rem;
}

.detail-item:last-child {
    border-bottom: none !important;
}

.time-card {
    transition: all 0.3s ease;
}

.time-card:hover {
    transform: scale(1.05);
}

.time-card.bg-dark {
    background: linear-gradient(135deg, #212529 0%, #343a40 100%) !important;
}

.time-color-indicator {
    border-width: 2px !important;
}

.bg-light .time-color-indicator {
    border-color: #dee2e6 !important;
}

.bg-dark .time-color-indicator {
    border-color: #495057 !important;
}

.last-move-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid rgba(0,0,0,0.05);
}

.pgn-display {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0 0 12px 12px;
    font-family: 'Roboto Mono', 'Courier New', monospace;
    line-height: 1.5;
}

.fen-display {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid rgba(0,0,0,0.05);
    word-break: break-all;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
}

.btn-outline-warning {
    border-width: 2px;
}

.btn-outline-danger {
    border-width: 2px;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
    background-color: rgba(0,0,0,0.05);
}

.progress-bar {
    border-radius: 10px;
    transition: width 1s ease-in-out;
}

.alert {
    border-radius: 12px;
    border: none;
}

.empty-state {
    opacity: 0.7;
}

.empty-icon {
    font-size: 3rem;
}

.border-bottom {
    border-bottom: 2px solid rgba(102, 126, 234, 0.1) !important;
}

.shadow-lg {
    box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
}

.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
}

.rounded-3 {
    border-radius: 12px !important;
}

.rounded-pill {
    border-radius: 50px !important;
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.bg-opacity-25 {
    background-color: rgba(var(--bs-primary-rgb), 0.25) !important;
}

/* Scrollbar styling */
.pgn-display::-webkit-scrollbar {
    width: 6px;
}

.pgn-display::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.pgn-display::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 3px;
}

.pgn-display::-webkit-scrollbar-thumb:hover {
    background: #5a67d8;
}
</style>

<script>
function confirmDelete(matchId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        backdrop: 'rgba(0,0,0,0.4)'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/matches/${matchId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (response.ok) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Match has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#667eea',
                    }).then(() => {
                        window.location.href = '/admin/matches';
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete match.',
                        icon: 'error',
                        confirmButtonColor: '#667eea',
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred.',
                    icon: 'error',
                    confirmButtonColor: '#667eea',
                });
            });
        }
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Check if SweetAlert is available, otherwise use native confirm
    if (typeof Swal === 'undefined') {
        window.confirmDelete = function(matchId) {
            if (confirm('Are you sure you want to delete this match? This action cannot be undone.')) {
                fetch(`/admin/matches/${matchId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => {
                    if (response.ok) {
                        alert('Match deleted successfully!');
                        window.location.href = '/admin/matches';
                    } else {
                        alert('Failed to delete match');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }
    }
    
    // Auto refresh for live matches
    if (document.querySelector('.badge-warning')) {
        setInterval(() => {
            location.reload();
        }, 30000); // Refresh every 30 seconds for live matches
    }
});
</script>

<?= $this->endSection() ?>