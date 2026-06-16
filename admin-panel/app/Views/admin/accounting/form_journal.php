<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4 py-4">
    <!-- Header dengan gradient -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="page-title fw-bold text-gradient">
                <i class="fas fa-file-invoice-dollar me-2"></i>Manual <span class="text-emerald-600">Journal Entry</span>
            </h1>
            <p class="text-muted mb-0">Create and post manual accounting journal entries</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/accounting') ?>" 
               class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to Ledger
            </a>
        </div>
    </div>

    <!-- Stats Cards untuk validasi balance -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4" id="debitCard">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="fas fa-arrow-down fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold" id="totalDebitDisplay">Rp 0</h5>
                            <p class="text-muted mb-0">Total Debit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4" id="creditCard">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-emerald bg-opacity-10 text-emerald rounded-circle">
                                <i class="fas fa-arrow-up fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold text-emerald-600" id="totalCreditDisplay">Rp 0</h5>
                            <p class="text-muted mb-0">Total Credit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card card-hover border-0 shadow-sm mb-4" id="balanceCard">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-warning bg-opacity-10 text-warning rounded-circle">
                                <i class="fas fa-balance-scale fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0 fw-bold" id="balanceStatus">UNBALANCED</h5>
                            <p class="text-muted mb-0" id="balanceAmount">Difference: Rp 0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <form action="<?= base_url('admin/accounting/save_journal') ?>" method="POST" id="journalForm">
        <?= csrf_field() ?>
        
        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2 text-primary"></i>Journal Information
                </h5>
            </div>
            
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label small text-uppercase fw-bold text-muted">
                            <i class="fas fa-calendar-day me-1 text-primary"></i>Transaction Date
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-calendar text-primary"></i>
                            </span>
                            <input type="date" name="transaction_date" required 
                                   class="form-control border-start-0 ps-0 fw-bold"
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label small text-uppercase fw-bold text-muted">
                            <i class="fas fa-hashtag me-1 text-info"></i>Reference Number
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-file-alt text-info"></i>
                            </span>
                            <input type="text" name="reference_no" placeholder="JRN-001" required 
                                   class="form-control border-start-0 ps-0 fw-bold"
                                   value="<?= 'JRN-' . date('YmdHis') . rand(100, 999) ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label small text-uppercase fw-bold text-muted">
                            <i class="fas fa-calendar-alt me-1 text-success"></i>Accounting Period
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-clock text-success"></i>
                            </span>
                            <select name="period_id" required class="form-control border-start-0 ps-0 fw-bold">
                                <option value="">Select Period</option>
                                <?php foreach($periods as $p): ?>
                                    <option value="<?= $p->id ?>" <?= $p->is_active ? 'selected' : '' ?>>
                                        <?= $p->period_name ?> 
                                        <?= $p->is_active ? '(Active)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label small text-uppercase fw-bold text-muted">
                            <i class="fas fa-align-left me-1 text-secondary"></i>Journal Description
                        </label>
                        <textarea name="description" rows="3" required
                                  class="form-control fw-bold"
                                  placeholder="Enter journal description..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Details Card -->
        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list-alt me-2 text-primary"></i>Journal Entries
                    </h5>
                    <button type="button" onclick="addRow()" class="btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus me-1"></i>Add Line
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="detailTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 border-0" style="width: 50%;">
                                    <span class="text-muted small fw-bold text-uppercase">
                                        <i class="fas fa-book me-1"></i>Account (COA)
                                    </span>
                                </th>
                                <th class="py-3 border-0 text-center" style="width: 20%;">
                                    <span class="text-muted small fw-bold text-uppercase">
                                        <i class="fas fa-arrow-down me-1"></i>Debit (Rp)
                                    </span>
                                </th>
                                <th class="py-3 border-0 text-center" style="width: 20%;">
                                    <span class="text-muted small fw-bold text-uppercase">
                                        <i class="fas fa-arrow-up me-1"></i>Credit (Rp)
                                    </span>
                                </th>
                                <th class="pe-4 py-3 border-0 text-center" style="width: 10%;">
                                    <span class="text-muted small fw-bold text-uppercase">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Baris pertama -->
                            <tr class="row-item" id="row-0">
                                <td class="ps-4 py-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-university text-dark"></i>
                                        </span>
                                        <select name="journal_details[0][coa_id]" required 
								                class="select2 js-select-account form-control fw-bold"
								                data-placeholder="Select Account...">
								            <option value=""></option> <!-- Empty option for placeholder -->
								            <?php foreach($accounts as $acc): ?>
								                <option value="<?= $acc->id ?>">
								                    <?= $acc->account_code ?> - <?= $acc->account_name ?>
								                </option>
								            <?php endforeach; ?>
								        </select>
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    <input type="number" name="journal_details[0][debit]" value="0" min="0" step="0.01"
                                           class="form-control text-center fw-bold border-0 debit-input"
                                           onfocus="this.select()">
                                </td>
                                <td class="py-3 text-center">
                                    <input type="number" name="journal_details[0][credit]" value="0" min="0" step="0.01"
                                           class="form-control text-center fw-bold border-0 text-emerald-600 credit-input"
                                           onfocus="this.select()">
                                </td>
                                <td class="pe-4 py-3 text-center">
                                    <button type="button" onclick="removeRow(0)" 
                                            class="btn btn-sm btn-outline-danger rounded-circle remove-row"
                                            data-bs-toggle="tooltip" title="Remove Line">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer bg-white py-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1 text-info"></i>
                            <span id="rowCount">1</span> line(s) added
                        </div>
                        <div class="d-flex gap-4">
                            <div class="text-end">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Total Debit</div>
                                <div class="fw-bold fs-5" id="totalDebitText">Rp 0</div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Total Credit</div>
                                <div class="fw-bold fs-5 text-emerald-600" id="totalCreditText">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card border-0 shadow-lg">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="validation-status">
                        <div id="validationMessage" class="alert alert-warning mb-0 py-2 px-3 d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span>Journal is not balanced. Please adjust debit and credit amounts.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="<?= base_url('admin/accounting/journal') ?>" 
                           class="btn btn-lg btn-outline-secondary px-4">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" id="btnSubmit" 
                                class="btn btn-lg btn-primary px-5 shadow-sm" disabled>
                            <i class="fas fa-save me-2"></i>Save Journal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

.btn-gradient:hover:not(:disabled) {
    background: var(--success-gradient);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}

.btn-gradient:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.card-hover {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.card-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-emerald {
    background-color: #10b981 !important;
}

.text-emerald-600 {
    color: #10b981 !important;
}

.input-group-lg .form-control,
.input-group-lg .input-group-text {
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: #3b82f6;
}

.table th {
    font-weight: 600;
    letter-spacing: 0.5px;
    font-size: 0.75rem;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    vertical-align: middle;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

.debit-input, .credit-input {
    font-family: 'Courier New', monospace;
    font-size: 1rem;
    border: 1px solid transparent !important;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.debit-input:focus, .credit-input:focus {
    border-color: #3b82f6 !important;
    background-color: rgba(59, 130, 246, 0.05);
}

.debit-input {
    background-color: rgba(59, 130, 246, 0.05);
}

.credit-input {
    background-color: rgba(16, 185, 129, 0.05);
}

.select-account {
    border: 1px solid transparent;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.select-account:focus {
    border-color: #3b82f6;
    background-color: rgba(59, 130, 246, 0.05);
}

#validationMessage {
    border-radius: 8px;
    border: 1px solid rgba(245, 158, 11, 0.2);
    background-color: rgba(245, 158, 11, 0.05);
}

#validationMessage.success {
    background-color: rgba(16, 185, 129, 0.05);
    border-color: rgba(16, 185, 129, 0.2);
    color: #059669;
}

.fs-5 {
    font-size: 1.25rem !important;
}

/* Balance status colors */
.balanced #balanceCard {
    border: 2px solid #10b981;
}

.unbalanced #balanceCard {
    border: 2px solid #ef4444;
}

.balanced #validationMessage {
    display: none !important;
}

.unbalanced #validationMessage {
    display: flex !important;
}

/* Validation states */
.is-invalid {
    border-color: #ef4444 !important;
    background-color: rgba(239, 68, 68, 0.05) !important;
}

.is-valid {
    border-color: #10b981 !important;
    background-color: rgba(16, 185, 129, 0.05) !important;
}

/* Alert untuk error */
.alert-fixed {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
}

/* Disable button style */
.btn:disabled {
    cursor: not-allowed;
}
</style>

<script>
// Global variable untuk melacak jumlah baris
let rowCounter = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initial calculation
    calculateTotal();
    
    // Event listener untuk input changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('debit-input') || e.target.classList.contains('credit-input')) {
            validateAmount(e.target);
            calculateTotal();
        }
    });
    
    // Event untuk form submission - JANGAN gunakan preventDefault di sini
    // Biarkan form submit normal dengan validasi
    document.getElementById('journalForm').addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault(); // Hanya prevent jika validasi gagal
            return false;
        }
        
        // Jika validasi berhasil, format data sebelum dikirim
        prepareFormData();
        
        // Biarkan form submit normal
        return true;
    });
});

// FUNGSI UTAMA
function addRow() {
    const tbody = document.querySelector('#detailTable tbody');
    
    // Buat ID baru untuk baris
    const newRowId = rowCounter;
    rowCounter++;
    
    // Buat HTML untuk baris baru dengan nama field yang benar
    const newRowHTML = `
        <tr class="row-item" id="row-${newRowId}">
            <td class="ps-4 py-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-university text-dark"></i>
                    </span>
                    <select name="journal_details[${newRowId}][coa_id]" required 
                            class="form-control select-account border-start-0 ps-0 fw-bold">
                        <option value="">Select Account...</option>
                        <?php foreach($accounts as $acc): ?>
                            <option value="<?= $acc->id ?>">
                                <?= $acc->account_code ?> - <?= $acc->account_name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </td>
            <td class="py-3 text-center">
                <input type="number" name="journal_details[${newRowId}][debit]" value="0" min="0" step="0.01"
                       class="form-control text-center fw-bold border-0 debit-input"
                       onfocus="this.select()">
            </td>
            <td class="py-3 text-center">
                <input type="number" name="journal_details[${newRowId}][credit]" value="0" min="0" step="0.01"
                       class="form-control text-center fw-bold border-0 text-emerald-600 credit-input"
                       onfocus="this.select()">
            </td>
            <td class="pe-4 py-3 text-center">
                <button type="button" onclick="removeRow(${newRowId})" 
                        class="btn btn-sm btn-outline-danger rounded-circle remove-row"
                        data-bs-toggle="tooltip" title="Remove Line">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>
    `;
    
    // Tambahkan baris baru ke tabel
    tbody.insertAdjacentHTML('beforeend', newRowHTML);
    
    updateRowCount();
    calculateTotal();
    
    // Focus ke select account baru
    setTimeout(() => {
        document.querySelector(`#row-${newRowId} .select-account`).focus();
    }, 100);
}

function removeRow(rowId) {
    const row = document.getElementById(`row-${rowId}`);
    const rows = document.querySelectorAll('.row-item');
    
    if (rows.length > 1) {
        row.remove();
        updateRowCount();
        calculateTotal();
    } else {
        // Reset baris pertama jika hanya ada satu baris
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.type === 'number') {
                input.value = '0';
            } else if (input.tagName === 'SELECT') {
                input.value = '';
            }
            input.classList.remove('is-valid', 'is-invalid');
        });
        calculateTotal();
    }
}

function updateRowCount() {
    const rows = document.querySelectorAll('.row-item');
    document.getElementById('rowCount').textContent = rows.length;
}

// VALIDASI
function validateAmount(input) {
    const value = parseFloat(input.value) || 0;
    
    if (value < 0) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else if (value > 0) {
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        return true;
    } else {
        input.classList.remove('is-invalid', 'is-valid');
        return true;
    }
}

function validateForm() {
    let isValid = true;
    let hasValidEntries = false;
    
    // Reset semua validasi error sebelumnya
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    
    // Validasi semua account
    const accountSelects = document.querySelectorAll('.select-account');
    accountSelects.forEach((select, index) => {
        if (!select.value) {
            select.classList.add('is-invalid');
            isValid = false;
        } else {
            select.classList.remove('is-invalid');
        }
    });
    
    // Validasi minimal ada satu entry dengan debit atau credit > 0
    const debitInputs = document.querySelectorAll('.debit-input');
    const creditInputs = document.querySelectorAll('.credit-input');
    
    for (let i = 0; i < debitInputs.length; i++) {
        const debit = parseNumber(debitInputs[i].value);
        const credit = parseNumber(creditInputs[i].value);
        
        if (debit > 0 || credit > 0) {
            hasValidEntries = true;
            break;
        }
    }
    
    if (!hasValidEntries) {
        showAlert('At least one entry must have a debit or credit amount greater than 0.', 'warning');
        isValid = false;
    }
    
    // Validasi balance
    const totalD = getTotalDebit();
    const totalC = getTotalCredit();
    const isBalanced = Math.abs(totalD - totalC) < 0.01;
    
    if (!isBalanced) {
        showAlert('Journal is not balanced. Total Debit and Total Credit must be equal.', 'warning');
        isValid = false;
    }
    
    return isValid;
}

// PERHITUNGAN
function parseNumber(str) {
    if (!str) return 0;
    // Konversi ke number, tangani nilai kosong
    const num = parseFloat(str);
    return isNaN(num) ? 0 : num;
}

function getTotalDebit() {
    let total = 0;
    const debitInputs = document.querySelectorAll('.debit-input');
    
    debitInputs.forEach(input => {
        total += parseNumber(input.value);
    });
    
    return total;
}

function getTotalCredit() {
    let total = 0;
    const creditInputs = document.querySelectorAll('.credit-input');
    
    creditInputs.forEach(input => {
        total += parseNumber(input.value);
    });
    
    return total;
}

function calculateTotal() {
    const totalD = getTotalDebit();
    const totalC = getTotalCredit();
    const balance = totalD - totalC;
    const isBalanced = Math.abs(balance) < 0.01 && totalD > 0;
    
    // Update display
    document.getElementById('totalDebitDisplay').textContent = formatCurrency(totalD);
    document.getElementById('totalCreditDisplay').textContent = formatCurrency(totalC);
    document.getElementById('totalDebitText').textContent = formatCurrency(totalD);
    document.getElementById('totalCreditText').textContent = formatCurrency(totalC);
    
    // Update balance status
    updateBalanceStatus(totalD, totalC, balance, isBalanced);
}

function updateBalanceStatus(totalD, totalC, balance, isBalanced) {
    const balanceCard = document.getElementById('balanceCard');
    const balanceStatus = document.getElementById('balanceStatus');
    const balanceAmount = document.getElementById('balanceAmount');
    const validationMessage = document.getElementById('validationMessage');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (isBalanced) {
        // Balanced
        document.body.classList.add('balanced');
        document.body.classList.remove('unbalanced');
        
        // Update balance card
        if (balanceCard.querySelector('.avatar')) {
            balanceCard.querySelector('.avatar').className = 'avatar avatar-lg bg-success bg-opacity-10 text-success rounded-circle';
            balanceCard.querySelector('i').className = 'fas fa-check-circle fa-lg';
        }
        balanceStatus.textContent = 'BALANCED';
        balanceStatus.className = 'mb-0 fw-bold text-success';
        balanceAmount.textContent = 'Ready to post';
        balanceAmount.className = 'text-muted mb-0 text-success';
        
        // Update validation message
        validationMessage.className = 'alert alert-success mb-0 py-2 px-3 d-flex align-items-center success';
        validationMessage.innerHTML = '<i class="fas fa-check-circle me-2"></i><span>Journal is balanced and ready to save.</span>';
        
        // Enable submit button
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-save me-2"></i>Save Journal';
    } else {
        // Unbalanced
        document.body.classList.add('unbalanced');
        document.body.classList.remove('balanced');
        
        // Update balance card
        if (balanceCard.querySelector('.avatar')) {
            balanceCard.querySelector('.avatar').className = 'avatar avatar-lg bg-danger bg-opacity-10 text-danger rounded-circle';
            balanceCard.querySelector('i').className = 'fas fa-exclamation-triangle fa-lg';
        }
        balanceStatus.textContent = 'UNBALANCED';
        balanceStatus.className = 'mb-0 fw-bold text-danger';
        
        const diff = Math.abs(balance);
        const diffText = balance > 0 ? 
            `Debit exceeds by ${formatCurrency(diff)}` : 
            `Credit exceeds by ${formatCurrency(diff)}`;
        
        balanceAmount.textContent = diffText;
        balanceAmount.className = 'text-muted mb-0 text-danger';
        
        // Update validation message
        validationMessage.className = 'alert alert-warning mb-0 py-2 px-3 d-flex align-items-center';
        validationMessage.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i><span>Journal is not balanced. ${diffText}</span>`;
        
        // Disable submit button
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-lock me-2"></i>Balance Required';
    }
}

// FORMATTING
function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function prepareFormData() {
    // Konversi semua input amount ke format number tanpa formatting
    document.querySelectorAll('.debit-input, .credit-input').forEach(input => {
        input.value = parseNumber(input.value);
    });
    
    // Show loading state
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
}

// UTILITIES
function showAlert(message, type = 'warning') {
    // Hapus alert sebelumnya
    const existingAlert = document.querySelector('.alert-fixed');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Buat alert baru
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-fixed shadow`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Debounce function untuk performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<?= $this->endSection() ?>