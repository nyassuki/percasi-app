<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4 py-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-book me-2"></i>General <span class="text-emerald-600">Ledger</span>
            </h1>
            <p class="text-muted mb-0">Complete financial transaction records and journal entries</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/accounting/autopost') ?>" 
               class="btn btn-gradient shadow-sm fw-bold text-uppercase tracking-wider px-4 py-3"
               style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); font-size: 0.75rem; letter-spacing: 1px;">
                <i class="fas fa-bolt me-2"></i>POSTING TRANSAKSI
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fas fa-arrow-down fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold">
                                <?php 
                                $totalDebit = 0;
                                foreach($journals as $j) $totalDebit += $j->debit;
                                echo 'Rp ' . number_format($totalDebit, 0, ',', '.');
                                ?>
                            </h5>
                            <p class="text-muted mb-0">Total Debit</p>
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
                            <div class="avatar avatar-lg bg-emerald bg-opacity-10 text-emerald rounded-circle">
                                <i class="fas fa-arrow-up fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold">
                                <?php 
                                $totalCredit = 0;
                                foreach($journals as $j) $totalCredit += $j->credit;
                                echo 'Rp ' . number_format($totalCredit, 0, ',', '.');
                                ?>
                            </h5>
                            <p class="text-muted mb-0">Total Credit</p>
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
                            <div class="avatar avatar-lg bg-slate bg-opacity-10 text-slate rounded-circle">
                                <i class="fas fa-exchange-alt fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold"><?= count($journals) ?></h5>
                            <p class="text-muted mb-0">Journal Entries</p>
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
                                <i class="fas fa-balance-scale fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold">
                                <?php 
                                $balance = $totalDebit - $totalCredit;
                                $balanceClass = $balance == 0 ? 'text-success' : 'text-danger';
                                $balanceIcon = $balance == 0 ? 'fa-check' : 'fa-exclamation';
                                ?>
                                <span class="<?= $balanceClass ?>">
                                    Rp <?= number_format(abs($balance), 0, ',', '.') ?>
                                </span>
                            </h5>
                            <p class="text-muted mb-0">
                                <i class="fas <?= $balanceIcon ?> me-1 <?= $balanceClass ?>"></i>
                                Balance <?= $balance == 0 ? '(Balanced)' : '(Unbalanced)' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card glass-card border-0 shadow-lg mb-4">
        <div class="card-header bg-transparent py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2 text-primary"></i>Filters & Search
                </h5>
                <button class="btn btn-sm btn-outline-primary rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-sliders-h me-1"></i>Toggle
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body pt-0">
                <form method="get" class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Date Range</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-calendar text-primary"></i>
                            </span>
                            <input type="date" name="start_date" class="form-control form-control-sm border-start-0 ps-0" 
                                   value="<?= esc($filters['start_date'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">To Date</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-calendar text-primary"></i>
                            </span>
                            <input type="date" name="end_date" class="form-control form-control-sm border-start-0 ps-0" 
                                   value="<?= esc($filters['end_date'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Account</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-book text-success"></i>
                            </span>
                            
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Reference</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-hashtag text-info"></i>
                            </span>
                            <input type="text" name="reference_no" class="form-control form-control-sm border-start-0 ps-0" 
                                   placeholder="Reference No..." 
                                   value="<?= esc($filters['reference_no'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Description</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-search text-secondary"></i>
                            </span>
                            <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" 
                                   placeholder="Search description..." 
                                   value="<?= esc($filters['search'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-uppercase fw-bold text-muted">Type</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-exchange-alt text-dark"></i>
                            </span>
                            <select name="type" class="form-control form-control-sm border-start-0 ps-0">
                                <option value="">All Types</option>
                                <option value="debit" <?= ($filters['type'] ?? '') == 'debit' ? 'selected' : '' ?>>Debit Only</option>
                                <option value="credit" <?= ($filters['type'] ?? '') == 'credit' ? 'selected' : '' ?>>Credit Only</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm">
                                <i class="fas fa-filter me-1"></i>Apply Filters
                            </button>
                            <a href="<?= site_url('admin/accounting') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
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
                    <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>General Ledger Entries
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-list-alt me-1"></i>
                    <?= count($journals) ?> Entries
                </span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 border-0" style="width: 180px;">
                                <span class="text-muted small fw-bold text-uppercase">Date / Reference</span>
                            </th>
                            <th class="py-3 border-0">
                                <span class="text-muted small fw-bold text-uppercase">Account & Description</span>
                            </th>
                            <th class="py-3 border-0 text-end" style="width: 180px;">
                                <span class="text-muted small fw-bold text-uppercase">Debit</span>
                            </th>
                            <th class="pe-4 py-3 border-0 text-end" style="width: 180px;">
                                <span class="text-muted small fw-bold text-uppercase">Credit</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($journals)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon mb-3">
                                            <i class="fas fa-book fa-3x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">No journal entries found</h5>
                                        <p class="text-muted mb-4">Try adjusting your filters or create a new transaction</p>
                                        <a href="<?= base_url('admin/accounting/posting') ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i>Create New Transaction
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $lastId = null; ?>
                            <?php foreach($journals as $j): ?>
                                <?php $isNewTransaction = $lastId != $j->id; ?>
                                <?php $isCredit = $j->credit > 0; ?>
                                
                                <tr class="journal-row <?= $isNewTransaction ? 'transaction-group' : '' ?>">
                                    <!-- Date & Reference Column -->
                                    <td class="ps-4 py-3 <?= $isNewTransaction ? 'border-top' : '' ?>">
                                        <?php if($isNewTransaction): ?>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold text-slate-800 mb-1">
                                                    <i class="fas fa-calendar-day me-2 text-primary"></i>
                                                    <?= date('d M Y', strtotime($j->transaction_date)) ?>
                                                </div>
                                                <div>
                                                    <span class="badge bg-emerald bg-opacity-10 text-emerald border border-emerald border-opacity-25 rounded-pill px-3 py-1">
                                                        <i class="fas fa-hashtag me-1 fa-xs"></i>
                                                        <?= $j->reference_no ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small text-center">↳</div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Account & Description Column -->
                                    <td class="py-3 <?= $isNewTransaction ? 'border-top' : '' ?>">
                                        <div class="<?= $isCredit ? 'ps-4 border-start border-slate-200' : '' ?>">
                                            <div class="d-flex align-items-center">
                                                <div class="account-icon me-3">
                                                    <div class="avatar avatar-xs bg-<?= $isCredit ? 'emerald' : 'primary' ?> bg-opacity-10 text-<?= $isCredit ? 'emerald' : 'primary' ?> rounded-circle">
                                                        <i class="fas fa-<?= $isCredit ? 'arrow-up' : 'arrow-down' ?> fa-xs"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-slate-800 text-uppercase small">
                                                        <?= $j->account_name ?>
                                                    </div>
                                                    <?php if($j->description && !$isCredit): ?>
                                                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                                            <?= $j->description ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Debit Column -->
                                    <td class="py-3 text-end <?= $isNewTransaction ? 'border-top' : '' ?>">
                                        <?php if($j->debit > 0): ?>
                                            <div class="debit-amount">
                                                <span class="fw-bold text-slate-900 fs-6">
                                                    Rp <?= number_format($j->debit, 0, ',', '.') ?>
                                                </span>
                                                <?php if($j->description && $isCredit): ?>
                                                    <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                                        <?= $j->description ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Credit Column -->
                                    <td class="pe-4 py-3 text-end <?= $isNewTransaction ? 'border-top' : '' ?>">
                                        <?php if($j->credit > 0): ?>
                                            <div class="credit-amount">
                                                <span class="fw-bold text-emerald-600 fs-6">
                                                    Rp <?= number_format($j->credit, 0, ',', '.') ?>
                                                </span>
                                                <?php if($j->description && !$isCredit): ?>
                                                    <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                                        <?= $j->description ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $lastId = $j->id; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <!-- Footer dengan total -->
                    <?php if(!empty($journals)): ?>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td class="ps-4 py-3 border-top" colspan="2">
                                    <div class="fw-bold text-slate-700 text-uppercase small">
                                        <i class="fas fa-calculator me-2"></i>Grand Total
                                    </div>
                                </td>
                                <td class="py-3 text-end border-top">
                                    <div class="total-debit">
                                        <span class="fw-bold text-slate-900 fs-5">
                                            Rp <?= number_format($totalDebit, 0, ',', '.') ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="pe-4 py-3 text-end border-top">
                                    <div class="total-credit">
                                        <span class="fw-bold text-emerald-600 fs-5">
                                            Rp <?= number_format($totalCredit, 0, ',', '.') ?>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?= $balance == 0 ? 'bg-success bg-opacity-5' : 'bg-danger bg-opacity-5' ?>">
                                <td class="ps-4 py-3" colspan="2">
                                    <div class="fw-bold <?= $balance == 0 ? 'text-success' : 'text-danger' ?> text-uppercase small">
                                        <i class="fas fa-balance-scale me-2"></i>
                                        Balance Difference
                                    </div>
                                </td>
                                <td class="py-3 text-end" colspan="2">
                                    <span class="fw-bold <?= $balance == 0 ? 'text-success' : 'text-danger' ?> fs-5">
                                        <?php if($balance == 0): ?>
                                            <i class="fas fa-check-circle me-2"></i>BALANCED
                                        <?php else: ?>
                                            Rp <?= number_format(abs($balance), 0, ',', '.') ?>
                                            <span class="text-muted small ms-2">
                                                (<?= $balance > 0 ? 'Debit > Credit' : 'Credit > Debit' ?>)
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="card-footer bg-white py-3 border-top">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="mb-2 mb-md-0">
                        <span class="text-muted small">
                            Showing <span class="fw-bold text-dark"><?= $pager->getFirst() ?></span> to 
                            <span class="fw-bold text-dark"><?= $pager->getLast() ?></span> of 
                            <span class="fw-bold text-dark"><?= $pager->getTotal() ?></span> entries
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <?= $pager->links() ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    --emerald-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
    --slate-gradient: linear-gradient(135deg, #64748b 0%, #475569 100%);
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
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

.avatar-xs {
    width: 28px;
    height: 28px;
}

.card-hover {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.journal-row {
    transition: all 0.2s ease;
}

.journal-row:hover {
    background-color: rgba(100, 116, 139, 0.05);
}

.transaction-group {
    border-left: 3px solid #3b82f6;
    background-color: rgba(59, 130, 246, 0.02);
}

.debit-amount {
    font-family: 'Courier New', monospace;
}

.credit-amount {
    font-family: 'Courier New', monospace;
}

.bg-emerald {
    background-color: #10b981 !important;
}

.text-emerald {
    color: #10b981 !important;
}

.border-emerald {
    border-color: #10b981 !important;
}

.bg-slate {
    background-color: #64748b !important;
}

.text-slate {
    color: #64748b !important;
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state-icon {
    opacity: 0.5;
}

.table tbody tr.transaction-group:first-child td {
    border-top: 2px solid #e2e8f0 !important;
}

.table tfoot tr:first-child td {
    border-top: 2px solid #e2e8f0 !important;
    font-weight: 600;
}

.account-icon .avatar {
    width: 32px;
    height: 32px;
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
    color: #64748b;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background-color: rgba(100, 116, 139, 0.1);
    border-color: #64748b;
}

.input-group-text {
    border-right: none;
    background-color: #f8f9fa !important;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.1);
    border-color: #64748b;
}

.border-bottom {
    border-bottom: 2px solid rgba(100, 116, 139, 0.1) !important;
}

.table th {
    font-weight: 600;
    letter-spacing: 0.5px;
    font-size: 0.75rem;
}

.table td {
    vertical-align: middle;
}

.text-uppercase {
    letter-spacing: 0.5px;
}

.fs-6 {
    font-size: 1rem !important;
}

.fs-5 {
    font-size: 1.25rem !important;
}

.tracking-wider {
    letter-spacing: 1px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Auto-refresh jika ada filter aktif
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.toString()) {
        setTimeout(function() {
            console.log('Auto-refreshing ledger data...');
            window.location.reload();
        }, 30000); // Refresh setiap 30 detik jika ada filter
    }
});
</script>

<?= $this->endSection() ?>