<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Sistem Manajemen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #06d6a0;
            --danger-color: #ef476f;
            --warning-color: #ffd166;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            background: linear-gradient(to right, var(--primary-color), #5a5fe0);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-body {
            padding: 2.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .btn-login {
            background: linear-gradient(to right, var(--primary-color), #5a5fe0);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .otp-modal .modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
        }
        
        .otp-modal .modal-header {
            background: linear-gradient(to right, var(--primary-color), #5a5fe0);
            color: white;
            border-bottom: none;
            padding: 1.5rem 2rem;
        }
        
        .otp-modal .modal-body {
            padding: 2.5rem;
        }
        
        .otp-digit {
            width: 55px;
            height: 65px;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin: 0 8px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background: #f8f9fa;
            color: var(--dark-color);
            transition: all 0.3s;
        }
        
        @media (max-width: 576px) {
            .otp-digit {
                width: 45px;
                height: 55px;
                font-size: 24px;
                margin: 0 4px;
            }
        }
        
        .otp-digit:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            transform: translateY(-2px);
        }
        
        .otp-digit.filled {
            border-color: var(--success-color);
            background-color: rgba(6, 214, 160, 0.05);
        }
        
        .auth-icon {
            background: linear-gradient(135deg, var(--primary-color), #5a5fe0);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 36px;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .btn-verify {
            background: linear-gradient(to right, var(--success-color), #06c290);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(6, 214, 160, 0.3);
        }
        
        .btn-cancel {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            border-color: var(--danger-color);
            color: var(--danger-color);
            background-color: rgba(239, 71, 111, 0.05);
        }
        
        .error-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .success-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(6, 214, 160, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(6, 214, 160, 0); }
            100% { box-shadow: 0 0 0 0 rgba(6, 214, 160, 0); }
        }
        
        .timer-text {
            font-size: 14px;
            color: #6c757d;
            margin-top: 1rem;
        }
        
        .timer-countdown {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .spinner-border {
            width: 1.2rem;
            height: 1.2rem;
        }
        
        .status-message {
            border-radius: 10px;
            border-left: 4px solid;
            padding: 1rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .status-success {
            background-color: rgba(6, 214, 160, 0.1);
            border-left-color: var(--success-color);
            color: #0a8754;
        }
        
        .status-error {
            background-color: rgba(239, 71, 111, 0.1);
            border-left-color: var(--danger-color);
            color: #b02a37;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        
        .input-group {
            position: relative;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="login-card">
                    <div class="login-header">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="auth-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <h2 class="mb-2">Admin Panel</h2>
                        <p class="mb-0 opacity-75">Sistem Manajemen Terpadu</p>
                    </div>
                    
                    <div class="login-body">
                        <div id="statusMessage" class="d-none"></div>
                        
                        <form id="loginForm" method="post">
                            <div class="mb-4">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <input type="email" name="email" id="email" 
                                           class="form-control border-start-0 ps-1" 
                                           placeholder="admin@example.com" 
                                           value="admin@percasi.com" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" name="password" id="password" 
                                           class="form-control border-start-0 ps-1" 
                                           placeholder="••••••••" 
                                           value="admin" 
                                           required>
                                    <button type="button" class="btn password-toggle" onclick="togglePassword()">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" id="submitBtn" class="btn btn-login text-white w-100">
                                <span id="btnText">Masuk ke Sistem</span>
                                <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4 text-white">
                    <small>&copy; <?= date('Y') ?> Sistem Manajemen. Hak Cipta Dilindungi.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade otp-modal" id="otpModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-check me-2"></i>
                        Verifikasi Keamanan
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="auth-icon">
                        <i class="bi bi-phone"></i>
                    </div>
                    
                    <h4 class="text-center mb-2" id="welcomeText">Verifikasi 2-Faktor</h4>
                    <p class="text-center text-muted mb-4">
                        Buka aplikasi <strong>Google Authenticator</strong> Anda dan masukkan kode 6 digit di bawah ini
                    </p>
                    
                    <div id="otpStatusMessage" class="d-none"></div>
                    
                    <div class="otp-container mb-4">
                        <div class="d-flex justify-content-center mb-3">
                            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" 
                                   oninput="moveToNext(this, 1)" onkeydown="handleOtpKeydown(event, 1)">
                            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                   oninput="moveToNext(this, 2)" onkeydown="handleOtpKeydown(event, 2)">
                            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                   oninput="moveToNext(this, 3)" onkeydown="handleOtpKeydown(event, 3)">
                            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                   oninput="moveToNext(this, 4)" onkeydown="handleOtpKeydown(event, 4)">
                            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                   oninput="moveToNext(this, 5)" onkeydown="handleOtpKeydown(event, 5)">
                            <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                   oninput="moveToNext(this, 6)" onkeydown="handleOtpKeydown(event, 6)">
                        </div>
                        <input type="hidden" id="fullOtpCode" name="otp_code">
                        
                        <div class="timer-text text-center">
                            <i class="bi bi-clock me-1"></i>
                            Kode berubah setiap <span class="timer-countdown">30</span> detik
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-verify text-white" onclick="verifyOtp()" id="verifyBtn">
                            <span id="verifyBtnText">Verifikasi & Lanjutkan</span>
                            <span id="verifySpinner" class="spinner-border spinner-border-sm d-none ms-2"></span>
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="cancelOtp()" id="cancelBtn">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Login
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if(isset($showOtpModal) && $showOtpModal): ?>
                showOtpModal();
            <?php endif; ?>
            document.getElementById('email').focus();
        });

        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        function showStatus(message, type = 'success') {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.className = `status-message status-${type}`;
            statusDiv.innerHTML = `<div class="d-flex align-items-center"><i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-3"></i><div>${message}</div></div>`;
            statusDiv.classList.remove('d-none');
            if (type === 'success') setTimeout(() => statusDiv.classList.add('d-none'), 5000);
        }

        function showOtpModal() {
            const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
            otpModal.show();
            resetOtpInputs();
            startOtpTimer();
        }

        // HANDLE LOGIN FORM (MODIFIED FOR 2FA LOGIC)
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            submitBtn.disabled = true;
            btnText.textContent = 'Memproses...';
            loadingSpinner.classList.remove('d-none');
            document.getElementById('statusMessage').classList.add('d-none');
            
            try {
                const formData = new FormData(this);
                const response = await fetch('<?= base_url('login/process') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showStatus(data.message, 'success');
                    
                    // CEK APAKAH BUTUH 2FA
                    if (data.requires_2fa) {
                        setTimeout(() => {
                            showOtpModal();
                            if (data.admin_name) document.getElementById('welcomeText').textContent = `Halo, ${data.admin_name}!`;
                        }, 1000);
                    } else {
                        // LANGSUNG REDIRECT JIKA 2FA NO
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                } else {
                    showStatus(data.message, 'error');
                    document.getElementById('loginForm').classList.add('error-shake');
                    setTimeout(() => document.getElementById('loginForm').classList.remove('error-shake'), 500);
                }
            } catch (error) {
                showStatus('Terjadi kesalahan. Silahkan coba lagi.', 'error');
            } finally {
                submitBtn.disabled = false;
                btnText.textContent = 'Masuk ke Sistem';
                loadingSpinner.classList.add('d-none');
            }
        });

        // OTP DIGIT LOGIC
        function moveToNext(input, index) {
            input.value = input.value.replace(/[^0-9]/g, '');
            input.classList.toggle('filled', input.value !== '');
            if (input.value.length === 1 && index < 6) {
                document.querySelectorAll('.otp-digit')[index].focus();
            }
            updateFullOtpCode();
        }
        
        function handleOtpKeydown(event, index) {
            const inputs = document.querySelectorAll('.otp-digit');
            const currentInput = inputs[index - 1];
            if (event.key === 'Backspace') {
                if (currentInput.value === '' && index > 1) {
                    inputs[index - 2].focus();
                    inputs[index - 2].select();
                } else {
                    currentInput.value = '';
                    currentInput.classList.remove('filled');
                }
                updateFullOtpCode();
                event.preventDefault();
            }
        }
        
        function updateFullOtpCode() {
            let fullCode = '';
            document.querySelectorAll('.otp-digit').forEach(digit => fullCode += digit.value);
            document.getElementById('fullOtpCode').value = fullCode;
            if (fullCode.length === 6) setTimeout(() => verifyOtp(), 300);
        }
        
        function resetOtpInputs() {
            document.querySelectorAll('.otp-digit').forEach(digit => {
                digit.value = '';
                digit.classList.remove('filled');
            });
            document.getElementById('fullOtpCode').value = '';
            document.querySelector('.otp-digit').focus();
        }

        let otpTimer;
        function startOtpTimer() {
            let timeLeft = 30;
            const timerElement = document.querySelector('.timer-countdown');
            clearInterval(otpTimer);
            otpTimer = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(otpTimer);
                    document.querySelector('.timer-text').innerHTML = '<i class="bi bi-exclamation-triangle text-warning me-1"></i>Kode sudah kadaluarsa, refresh aplikasi Authenticator';
                }
            }, 1000);
        }

        async function verifyOtp() {
            const otpCode = document.getElementById('fullOtpCode').value;
            const verifyBtn = document.getElementById('verifyBtn');
            if (otpCode.length !== 6) return;
            
            verifyBtn.disabled = true;
            document.getElementById('verifyBtnText').textContent = 'Memverifikasi...';
            document.getElementById('verifySpinner').classList.remove('d-none');
            
            try {
                const response = await fetch('<?= base_url('login/auth/otp') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'otp_code=' + encodeURIComponent(otpCode)
                });
                
                const data = await response.json();
                if (data.success) {
                    showOtpStatus('Verifikasi berhasil! Mengalihkan...', 'success');
                    verifyBtn.classList.add('success-pulse');
                    setTimeout(() => window.location.href = data.redirect, 1000);
                } else {
                    showOtpStatus(data.message, 'error');
                    resetOtpInputs();
                    document.querySelector('.otp-container').classList.add('error-shake');
                    setTimeout(() => document.querySelector('.otp-container').classList.remove('error-shake'), 500);
                }
            } catch (error) {
                showOtpStatus('Terjadi kesalahan jaringan.', 'error');
            } finally {
                verifyBtn.disabled = false;
                document.getElementById('verifyBtnText').textContent = 'Verifikasi & Lanjutkan';
                document.getElementById('verifySpinner').classList.add('d-none');
            }
        }
        
        function showOtpStatus(message, type = 'success') {
            const statusDiv = document.getElementById('otpStatusMessage');
            statusDiv.className = `status-message status-${type}`;
            statusDiv.innerHTML = `<div class="d-flex align-items-center"><i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-3"></i><div>${message}</div></div>`;
            statusDiv.classList.remove('d-none');
        }

        async function cancelOtp() {
            const modalEl = document.getElementById('otpModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            await fetch('<?= base_url('login/auth/cancel') ?>', { method: 'POST' });
            modal.hide();
            clearInterval(otpTimer);
            resetOtpInputs();
        }
    </script>
</body>
</html>