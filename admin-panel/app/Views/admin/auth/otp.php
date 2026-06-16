<!DOCTYPE html>
<html>
<head>
    <title>2FA Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card p-4 shadow" style="width: 400px;">
        <h4 class="text-center mb-4">Google Authenticator</h4>
        <p class="text-center text-muted">Masukkan 6 digit kode dari aplikasi.</p>
        
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('login/auth/otp') ?>" method="post">
            <div class="mb-3 text-center">
                <input type="text" name="otp_code" class="form-control text-center text-lg fw-bold" placeholder="000000" maxlength="6" style="font-size: 24px; letter-spacing: 5px;" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Verify & Login</button>
        </form>
    </div>
</body>
</html>
