<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use Firebase\JWT\JWT;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

class Auth extends BaseController
{
    // 1. Tampilkan Halaman Login dengan OTP Modal
    public function index()
    {
        // Jika sudah login, lempar ke dashboard
        if (session()->get('is_admin_logged_in')) {
            return redirect()->to('/admin/dashboard');
        }
        
        // Cek apakah sedang dalam proses OTP verification
        $showOtpModal = session()->has('temp_admin_id');
        
        return view('admin/auth/login', ['showOtpModal' => $showOtpModal]);
    }
    public function attemptLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $model = new AdminModel();
        $admin = $model->where('email', $email)->first();

        if ($admin) {
            if ($admin['is_active'] == 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Akun Anda dinonaktifkan.']);
            }

            if (password_verify($password, $admin['password_hash'])) {
                $token_api = api_admin_login($email,$password);
                // --- LOGIKA BARU: CEK STATUS 2FA ---
                if ($admin['is2fa_active'] === 'YES') {
                    // Jika 2FA Aktif, simpan ID sementara dan minta OTP
                    session()->set('temp_admin_id', $admin['id']);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'requires_2fa' => true, // Flag untuk Frontend
                        'message' => 'Silahkan masukkan kode OTP',
                        'admin_name' => $admin['full_name'],
                    ]);
                } else {
                    // Jika 2FA Tidak Aktif, Langsung Login
                    $this->completeLogin($admin);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'requires_2fa' => false, // Flag untuk Frontend
                        'message' => 'Login berhasil!',
                        'redirect' => base_url('/admin/dashboard')
                    ]);
                }
            }
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Email atau Password salah.']);
    }

    public function verifyOtp()
    {
        $otpCode = $this->request->getPost('otp_code');
        $adminId = session()->get('temp_admin_id');

        if (!$adminId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesi login telah kadaluarsa.',
                'redirect' => base_url('/admin/login')
            ]);
        }

        $model = new AdminModel();
        $admin = $model->find($adminId);

        $g = new GoogleAuthenticator();
        if ($g->checkCode($admin['two_factor_secret'], $otpCode)) {
            
            // OTP BENAR, Selesaikan login
            $this->completeLogin($admin);
            session()->remove('temp_admin_id');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Login berhasil!',
                'redirect' => base_url('/admin/dashboard')
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Kode OTP salah.']);
        }
    }

    /**
     * Helper Method untuk men-generate JWT dan Set Session
     * Agar kode tidak duplikat di attemptLogin dan verifyOtp
     */
    private function completeLogin($admin)
    {
        $key = getenv('JWT_SECRET') ?: 'rahasia-super-aman-123';
        $payload = [
            'iss'  => 'admin-panel-system',
            'aud'  => 'admin-client',
            'iat'  => time(),
            'exp'  => time() + (3600 * 24),
            'uid'  => $admin['id'],
            'role' => 'super_admin'
        ];
        
        $jwtToken = JWT::encode($payload, $key, 'HS256');

        session()->set([
            'is_admin_logged_in' => true,
            'admin_id'           => $admin['id'],
            'admin_email'        => $admin['email'],
            'admin_name'         => $admin['full_name'],
            'admin_token'        => $jwtToken
        ]);
    }

    // 4. Cancel OTP Process
    public function cancelOtp()
    {
        session()->remove('temp_admin_id');
        return $this->response->setJSON([
            'success' => true,
            'redirect' => base_url('/login')
        ]);
        clear_api_token();
    }

    // 5. Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}