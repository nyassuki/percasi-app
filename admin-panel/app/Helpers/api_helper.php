<?php

use Config\Services;

/* =====================================================
 * TOKEN SESSION
 * ===================================================== */

if (!function_exists('get_api_token')) {
    function get_api_token(): ?string
    {
        return session()->get('admin_api_token');
    }
}

if (!function_exists('set_api_token')) {
    function set_api_token(string $token): void
    {
        session()->set('admin_api_token', trim($token));
    }
}

if (!function_exists('clear_api_token')) {
    function clear_api_token(): void
    {
        session()->remove('admin_api_token');
    }
}

if (!function_exists('get_api_base_url')) {
    function get_api_base_url(): string
    {
        return rtrim(
            getenv('API_BASE_URL'),
            '/'
        );
    }
}

/* =====================================================
 * CORE API REQUEST (JSON BODY – SESSION TOKEN)
 * ===================================================== */

if (!function_exists('api_request')) {
    function api_request(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): array {
        $token = get_api_token();

        if (!$token) {
            return [
                'success' => false,
                'status'  => 401,
                'message' => 'API token not found in session',
                'data'    => null
            ];
        }

        $client = Services::curlrequest();
        $url    = get_api_base_url() . '/' . ltrim($endpoint, '/');

        $defaultHeaders = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . trim($token),
        ];

        $options = [
            'headers'         => array_merge($defaultHeaders, $headers),
            'timeout'         => 30,
            'allow_redirects' => true,
            'verify'          => false,
            'http_errors'     => false,
            'connect_timeout' => 10, // Tambahkan connect timeout
        ];

        if (!empty($data)) {
            if (strtoupper($method) === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
                $options['body'] = json_encode($data); // Backup body format
            }
        }

        try {
            $response = $client->request(strtoupper($method), $url, $options);
            
            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody();
            $body = $rawBody ? (string) $rawBody : '';
            
            // Debug logging
            //log_message('debug', 'API Response - Status: ' . $statusCode . ' | Body: ' . $body);
            
            $decodedData = null;
            if (!empty($body)) {
                $decodedData = json_decode($body, true);
                
                // Jika JSON decode gagal
                if (json_last_error() !== JSON_ERROR_NONE) {
                    log_message('error', 'JSON decode error: ' . json_last_error_msg());
                    log_message('debug', 'Raw response: ' . $body);
                    $decodedData = ['raw_response' => $body];
                }
            }
            
            // Check for successful status codes
            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'success' => true,
                    'status'  => $statusCode,
                    'data'    => $decodedData,
                    'message' => $decodedData['message'] ?? 'Request successful'
                ];
            } else {
                // Error response
                return [
                    'success' => false,
                    'status'  => $statusCode,
                    'data'    => $decodedData,
                    'message' => $decodedData['message'] ?? 
                                $decodedData['error'] ?? 
                                'API request failed with status ' . $statusCode
                ];
            }

        } catch (\Throwable $e) {
            log_message('error', 'API REQUEST ERROR: ' . $e->getMessage());
            log_message('error', 'URL: ' . $url . ' | Method: ' . $method);

            return [
                'success' => false,
                'status'  => 500,
                'message' => 'API Connection Error: ' . $e->getMessage(),
                'data'    => null
            ];
        }
    }
}

/* =====================================================
 * LOGIN (SET TOKEN)
 * ===================================================== */

if (!function_exists('api_admin_login')) {
    function api_admin_login(string $email, string $password): array
    {
        $client = Services::curlrequest();
        $url = get_api_base_url() . '/admin/login';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email'    => trim($email),
                    'password' => $password,
                ],
                'timeout' => 30,
                'verify'  => false,
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody();
            $body = $rawBody ? (string) $rawBody : '';
            
            //log_message('debug', 'Login API Response - Status: ' . $statusCode . ' | Body: ' . $body);
            
            $data = [];
            if (!empty($body)) {
                $data = json_decode($body, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    log_message('error', 'Login JSON decode error: ' . json_last_error_msg());
                    return [
                        'success' => false,
                        'message' => 'Invalid response from server',
                        'status'  => $statusCode,
                    ];
                }
            }
            
            if ($statusCode === 200 || $statusCode === 201) {
                if (empty($data['token'])) {
                    log_message('error', 'Token not found in response: ' . print_r($data, true));
                    return [
                        'success' => false,
                        'message' => 'Token not found in server response',
                        'status'  => $statusCode,
                    ];
                }

                set_api_token($data['token']);
                
                return [
                    'success' => true,
                    'token'   => $data['token'],
                    'message' => $data['message'] ?? 'Login successful',
                    'data'    => $data,
                ];
            } else {
                $errorMsg = $data['message'] ?? 
                           $data['error'] ?? 
                           'Login failed with status ' . $statusCode;
                
                log_message('error', 'Login failed: ' . $errorMsg);
                
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'status'  => $statusCode,
                ];
            }

        } catch (\Throwable $e) {
            log_message('error', 'ADMIN API LOGIN ERROR: ' . $e->getMessage());
            log_message('error', 'Login URL: ' . $url);

            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }
}
/* =====================================================
 * SHORTCUT FUNCTIONS
 * ===================================================== */

if (!function_exists('send_notification')) {
    function send_notification(
        int $userId,
        string $title,
        string $message,
        string $type = 'info'
    ): array {
        return api_request(
            'POST',
            'admin/notify-user',
            compact('userId', 'title', 'message', 'type')
        );
    }
}

if (!function_exists('send_broadcast')) {
    function send_broadcast(
        string $title,
        string $message,
        string $type = 'info'
    ): array {
        return api_request(
            'POST',
            'admin/broadcast',
            compact('title', 'message', 'type')
        );
    }
}

if (!function_exists('create_match')) {
    function create_match(int $whitePlayerId, int $blackPlayerId): array
    {
        return api_request(
            'POST',
            'admin/match-pairing',
            compact('whitePlayerId', 'blackPlayerId')
        );
    }
}
if (!function_exists('test_api_connection')) {
    function test_api_connection(): array
    {
        $client = Services::curlrequest();
        $url = get_api_base_url() . '/health'; // atau endpoint test jika ada
        
        try {
            $response = $client->get($url, [
                'timeout' => 10,
                'verify' => false,
            ]);
            
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'body' => (string) $response->getBody(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'url' => $url,
            ];
        }
    }
}

if (!function_exists('debug_api_config')) {
    function debug_api_config(): array
    {
        return [
            'base_url' => get_api_base_url(),
            'has_token' => !empty(get_api_token()),
            'token_length' => strlen(get_api_token() ?? ''),
            'env_url' => getenv('ADMIN_API_URL'),
        ];
    }
}