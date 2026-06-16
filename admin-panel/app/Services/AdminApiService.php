<?php

namespace App\Services;

use Config\Services;
use CodeIgniter\HTTP\Exceptions\HTTPException;

class AdminApiService
{
    protected $client;
    protected $session;
    protected $baseUrl;

    protected $email;
    protected $password;

    public function __construct(?string $email = null, ?string $password = null)
    {
        $this->client  = Services::curlrequest();
        $this->session = session();
        $this->baseUrl = getenv('ADMIN_API_URL');

        // fallback ke ENV jika tidak dikirim
        $this->email    = $email    ?? getenv('ADMIN_API_EMAIL');
        $this->password = $password ?? getenv('ADMIN_API_PASSWORD');
    }

    /* =====================================================
     * TOKEN MANAGEMENT
     * ===================================================== */

    protected function getToken(): ?string
    {
        return $this->session->get('admin_api_token');
    }

    protected function setToken(string $token): void
    {
        $this->session->set('admin_api_token', $token);
    }

    protected function clearToken(): void
    {
        $this->session->remove('admin_api_token');
    }

    protected function headers(): array
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($token = $this->getToken()) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }

    /* =====================================================
     * LOGIN (DYNAMIC CREDENTIAL)
     * ===================================================== */

    protected function login(string $email, string $password): void
    {
        try {
            $response = $this->client->post(
                "{$this->baseUrl}/login",
                [
                    'json' => [
                        'email'    => $email,
                        'password' => $password,
                    ],
                ]
            );

            $data = json_decode($response->getBody(), true);

            if (empty($data['token'])) {
                throw new \RuntimeException('Token tidak ditemukan pada response login');
            }

            $this->setToken($data['token']);

        } catch (\Throwable $e) {
            log_message('error', 'ADMIN API LOGIN FAILED: ' . $e->getMessage());
            throw new HTTPException('Gagal login ke Admin API');
        }
    }

    /* =====================================================
     * REQUEST HANDLER + AUTO RETRY
     * ===================================================== */

    protected function request(string $method, string $uri, array $payload = []): array
    {
        try {
            $response = $this->client->request(
                $method,
                "{$this->baseUrl}/{$uri}",
                [
                    'headers' => $this->headers(),
                    'json'    => $payload,
                ]
            );

            return json_decode($response->getBody(), true);

        } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {

            // 🔁 Token expired / unauthorized
            if ($e->getCode() === 401) {
                $this->clearToken();

                // login ulang pakai credential yang tersedia
                $this->login($this->email, $this->password);

                // retry sekali
                return $this->request($method, $uri, $payload);
            }

            throw $e;

        } catch (\Throwable $e) {
            log_message('error', 'ADMIN API ERROR: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'Admin API Error',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /* =====================================================
     * PUBLIC API METHODS
     * ===================================================== */

    public function broadcast(string $title, string $message, string $type = 'info'): array
    {
        return $this->request('POST', 'broadcast', [
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
        ]);
    }

    public function notifyUser(int $userId, string $title, string $message, string $type = 'info'): array
    {
        return $this->request('POST', 'notify-user', [
            'userId'  => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
        ]);
    }

    public function matchPairing(int $whitePlayerId, int $blackPlayerId): array
    {
        return $this->request('POST', 'match-pairing', [
            'whitePlayerId' => $whitePlayerId,
            'blackPlayerId' => $blackPlayerId,
        ]);
    }
}


/*
$api = new AdminApiService('admin@percasi.com', 'admin');

$api->broadcast(
    'PENGUMUMAN PENTING',
    'Turnamen Akbar akan dimulai hari Sabtu jam 10.00 WIB!',
    'success'
);
*/