<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Cek Sesi
        if (!session()->get('is_admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        // 2. Cek Validitas JWT (Double Security)
        $token = session()->get('admin_token');
        if (!$token) {
            return redirect()->to('/admin/login');
        }

        try {
            $key = getenv('JWT_SECRET') ?: 'rahasia-dapur-admin-123';
            // Decode token
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            
            // Opsional: Simpan data user decoded ke request agar bisa diakses controller
            // $request->adminData = $decoded;

        } catch (\Exception $e) {
            // Token expired atau invalid
            session()->destroy();
            return redirect()->to('/admin/login')->with('error', 'Sesi kadaluarsa.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
