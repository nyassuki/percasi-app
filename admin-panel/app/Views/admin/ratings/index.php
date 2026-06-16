<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark">
        <i class="fa-solid fa-chess-knight me-2"></i> Rating Pemain Chess
    </h4>
</div>

<!-- Search and Filter Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('admin/ratings') ?>" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fa-solid fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           name="search" 
                           value="<?= esc($search ?? '') ?>" 
                           placeholder="Cari berdasarkan username, nama, atau ID pemain...">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-search"></i> Cari
                    </button>
                    <?php if (!empty($search)): ?>
                    <a href="<?= base_url('admin/ratings') ?>" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-times"></i> Reset
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-2">
                <select class="form-select" name="sort_by" id="sortBy">
                    <option value="standard_rating" <?= (($sort_by ?? 'standard_rating') == 'standard_rating') ? 'selected' : '' ?>>Rating Standard</option>
                    <option value="rapid_rating" <?= (($sort_by ?? '') == 'rapid_rating') ? 'selected' : '' ?>>Rating Rapid</option>
                    <option value="blitz_rating" <?= (($sort_by ?? '') == 'blitz_rating') ? 'selected' : '' ?>>Rating Blitz</option>
                    <option value="bullet_rating" <?= (($sort_by ?? '') == 'bullet_rating') ? 'selected' : '' ?>>Rating Bullet</option>
                    <option value="wins" <?= (($sort_by ?? '') == 'wins') ? 'selected' : '' ?>>Kemenangan</option>
                    <option value="win_rate" <?= (($sort_by ?? '') == 'win_rate') ? 'selected' : '' ?>>Win Rate</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <select class="form-select" name="sort_order" id="sortOrder">
                    <option value="desc" <?= (($sort_order ?? 'desc') == 'desc') ? 'selected' : '' ?>>Tertinggi</option>
                    <option value="asc" <?= (($sort_order ?? '') == 'asc') ? 'selected' : '' ?>>Terendah</option>
                </select>
            </div>
        </form>
        
        <!-- Quick Stats -->
        <div class="row mt-3">
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                        <i class="fa-solid fa-users text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Pemain</div>
                        <div class="fw-bold"><?= isset($pager) ? number_format($pager['totalPlayers'], 0, ',', '.') : '0' ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2">
                        <i class="fa-solid fa-trophy text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Rating Tertinggi</div>
                        <div class="fw-bold">
                            <?php 
                            $maxRating = 0;
                            if (isset($players) && !empty($players)) {
                                foreach ($players as $player) {
                                    if ($player->standard_rating > $maxRating) {
                                        $maxRating = $player->standard_rating;
                                    }
                                }
                            }
                            echo $maxRating ?: '0';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                        <i class="fa-solid fa-chart-line text-info"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Rata-rata Rating</div>
                        <div class="fw-bold">
                            <?php 
                            $avgRating = 0;
                            if (isset($players) && !empty($players)) {
                                $totalRating = 0;
                                foreach ($players as $player) {
                                    $totalRating += $player->standard_rating;
                                }
                                $avgRating = round($totalRating / count($players));
                            }
                            echo $avgRating ?: '0';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-2">
                        <i class="fa-solid fa-chess-board text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pemain Aktif</div>
                        <div class="fw-bold">
                            <?php 
                            $activePlayers = 0;
                            if (isset($players) && !empty($players)) {
                                foreach ($players as $player) {
                                    if (($player->wins + $player->losses + $player->draws) > 0) {
                                        $activePlayers++;
                                    }
                                }
                            }
                            echo $activePlayers;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Players Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Daftar Rating Pemain</h6>
            <div class="text-muted small">
                <?php if (isset($pager)): ?>
                Menampilkan <?= $pager['offset'] ?>-<?= $pager['limit'] ?> dari <?= number_format($pager['totalPlayers'], 0, ',', '.') ?> pemain
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3 fw-semibold">#</th>
                        <th class="py-3 fw-semibold">PEMAIN</th>
                        <th class="py-3 fw-semibold">RATING STANDARD</th>
                        <th class="py-3 fw-semibold">RAPID</th>
                        <th class="py-3 fw-semibold">BLITZ</th>
                        <th class="py-3 fw-semibold">BULLET</th>
                        <th class="py-3 fw-semibold">STATISTIK</th>
                        <th class="py-3 fw-semibold">UPDATE</th>
                        <th class="text-center py-3 fw-semibold">AKSI</th>
                    </tr>
                </thead>
                <tbody id="playersTableBody">
                    <?php if (empty($players)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="py-5">
                                <i class="fa-solid fa-chess-board fa-2x text-muted mb-3"></i>
                                <h6 class="text-muted">Tidak ada data rating pemain</h6>
                                <?php if (!empty($search)): ?>
                                <p class="text-muted small">Coba ubah kata kunci pencarian</p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $no = $pager['offset'];
                        foreach ($players as $player): 
                        ?>
                        <tr>
                            <!-- Ranking Column -->
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-center" style="width: 30px;">
                                    <?= $no++ ?>
                                </div>
                            </td>

                            <!-- Player Column -->
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative">
                                        <?php
                                        $avatarUrl = !empty($player->avatar_url) 
                                            ? base_url(str_replace("public", "", $player->avatar_url))
                                            : 'https://ui-avatars.com/api/?name=' . urlencode($player->username) . '&length=2&rounded=true&background=0D8ABC&color=fff';
                                        ?>
                                        <img src="<?= $avatarUrl ?>" 
                                             class="rounded-circle me-3 border" 
                                             width="48" 
                                             height="48"
                                             alt="<?= esc($player->full_name) ?>"
                                             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($player->username) ?>&length=1&rounded=true'">
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1"><?= esc($player->full_name) ?></h6>
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-at me-1"></i><?= esc($player->username) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-id-card me-1"></i>ID: <?= $player->user_id ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Standard Rating Column -->
                            <td class="py-3">
                                <div class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 fw-bold">
                                        <?= $player->standard_rating ?>
                                    </span>
                                    <div class="text-muted small mt-1">Standard</div>
                                </div>
                            </td>

                            <!-- Rapid Rating Column -->
                            <td class="py-3">
                                <div class="text-center">
                                    <div class="fw-bold"><?= $player->rapid_rating ?></div>
                                    <div class="text-muted small">Rapid</div>
                                </div>
                            </td>

                            <!-- Blitz Rating Column -->
                            <td class="py-3">
                                <div class="text-center">
                                    <div class="fw-bold"><?= $player->blitz_rating ?></div>
                                    <div class="text-muted small">Blitz</div>
                                </div>
                            </td>

                            <!-- Bullet Rating Column -->
                            <td class="py-3">
                                <div class="text-center">
                                    <div class="fw-bold"><?= $player->bullet_rating ?></div>
                                    <div class="text-muted small">Bullet</div>
                                </div>
                            </td>

                            <!-- Statistics Column -->
                            <td class="py-3">
                                <div class="small">
                                    <div class="d-flex mb-1">
                                        <span class="text-success me-3">
                                            <i class="fa-solid fa-trophy me-1"></i><?= $player->wins ?>W
                                        </span>
                                        <span class="text-danger me-3">
                                            <i class="fa-solid fa-times me-1"></i><?= $player->losses ?>L
                                        </span>
                                        <span class="text-warning">
                                            <i class="fa-solid fa-handshake me-1"></i><?= $player->draws ?>D
                                        </span>
                                    </div>
                                    <?php 
                                    $totalGames = $player->wins + $player->losses + $player->draws;
                                    $winRate = $totalGames > 0 ? round(($player->wins / $totalGames) * 100, 1) : 0;
                                    ?>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-success" style="width: <?= $winRate ?>%"></div>
                                    </div>
                                    <div class="text-muted mt-1">Win Rate: <?= $winRate ?>%</div>
                                </div>
                            </td>

                            <!-- Update Column -->
                            <td class="py-3">
                                <div class="text-muted small">
                                    <div><i class="fa-solid fa-clock me-1"></i>Update:</div>
                                    <div><?= date('d M Y', strtotime($player->updated_at)) ?></div>
                                    <div><?= date('H:i', strtotime($player->updated_at)) ?></div>
                                </div>
                            </td>

                            <!-- Actions Column -->
                            <td class="text-center pe-4 py-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= base_url('admin/players/detail/' . $player->user_id) ?>" 
                                       class="btn btn-outline-primary border"
                                       title="Lihat Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-warning border"
                                            title="Edit Rating"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editRatingModal"
                                            data-user-id="<?= $player->user_id ?>"
                                            data-user-name="<?= esc($player->full_name) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
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
                           href="<?= base_url('admin/ratings?page=' . ($pager['currentPage'] - 1) . '&search=' . urlencode($search ?? '') . '&sort_by=' . ($sort_by ?? '') . '&sort_order=' . ($sort_order ?? '')) ?>"
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
                        <a class="page-link" href="<?= base_url('admin/ratings?page=1&search=' . urlencode($search ?? '') . '&sort_by=' . ($sort_by ?? '') . '&sort_order=' . ($sort_order ?? '')) ?>">1</a>
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
                           href="<?= base_url('admin/ratings?page=' . $i . '&search=' . urlencode($search ?? '') . '&sort_by=' . ($sort_by ?? '') . '&sort_order=' . ($sort_order ?? '')) ?>">
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
                           href="<?= base_url('admin/ratings?page=' . $pager['totalPages'] . '&search=' . urlencode($search ?? '') . '&sort_by=' . ($sort_by ?? '') . '&sort_order=' . ($sort_order ?? '')) ?>">
                            <?= $pager['totalPages'] ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Next Button -->
                    <li class="page-item <?= !$pager['hasNext'] ? 'disabled' : '' ?>">
                        <a class="page-link" 
                           href="<?= base_url('admin/ratings?page=' . ($pager['currentPage'] + 1) . '&search=' . urlencode($search ?? '') . '&sort_by=' . ($sort_by ?? '') . '&sort_order=' . ($sort_order ?? '')) ?>"
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

<!-- Edit Rating Modal -->
<div class="modal fade" id="editRatingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Rating Pemain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editRatingForm">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">Nama Pemain</label>
                        <input type="text" class="form-control" id="editUserName" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Standard Rating</label>
                            <input type="number" class="form-control" name="standard_rating" id="editStandardRating" min="0" max="3000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rapid Rating</label>
                            <input type="number" class="form-control" name="rapid_rating" id="editRapidRating" min="0" max="3000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Blitz Rating</label>
                            <input type="number" class="form-control" name="blitz_rating" id="editBlitzRating" min="0" max="3000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bullet Rating</label>
                            <input type="number" class="form-control" name="bullet_rating" id="editBulletRating" min="0" max="3000">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveRatingBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto submit form when sort options change
    document.getElementById('sortBy').addEventListener('change', function() {
        document.querySelector('form').submit();
    });
    
    document.getElementById('sortOrder').addEventListener('change', function() {
        document.querySelector('form').submit();
    });
    
    // Edit Rating Modal
    const editRatingModal = document.getElementById('editRatingModal');
    if (editRatingModal) {
        editRatingModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            
            // Isi data ke modal
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUserName').value = userName;
            
            // Load data rating via AJAX
            fetch(`<?= base_url('admin/ratings/get-rating/') ?>${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('editStandardRating').value = data.data.standard_rating;
                        document.getElementById('editRapidRating').value = data.data.rapid_rating;
                        document.getElementById('editBlitzRating').value = data.data.blitz_rating;
                        document.getElementById('editBulletRating').value = data.data.bullet_rating;
                    }
                });
        });
    }
    
    // Save Rating
    document.getElementById('saveRatingBtn').addEventListener('click', function() {
        const formData = new FormData(document.getElementById('editRatingForm'));
        
        fetch('<?= base_url('admin/ratings/update-rating') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Gagal menyimpan rating');
            }
        });
    });
    
    // Real-time search with debounce
    const searchInput = document.querySelector('input[name="search"]');
    const searchForm = document.querySelector('form');
    
    if (searchInput && searchForm) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Reset to page 1 when searching
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('page', '1');
                window.location.href = currentUrl.toString();
            }, 800);
        });
    }
});
</script>

<?= $this->endSection() ?>