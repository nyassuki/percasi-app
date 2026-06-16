<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Route untuk halaman utama (opsional)
$routes->get('/', 'Home::index');
$routes->get('/login', 'Admin\Auth::index');
$routes->post('/login/process', 'Admin\Auth::attemptLogin');
$routes->get('/login/auth/otp', 'Admin\Auth::otpView');
$routes->post('/login/auth/otp', 'Admin\Auth::verifyOtp');
$routes->post('/login/auth/cancel', 'Admin\Auth::cancelOtp');
$routes->get('/logout', 'Admin\Auth::logout');
$routes->get('/admin/logout', 'Admin\Auth::logout');
$routes->get('/admin/login', 'Admin\Auth::index');
/**
 * Grouping Route Admin
 * URL akan diawali dengan /admin/....
 * Dilindungi oleh filter 'adminAuth' yang sudah kita buat sebelumnya.
 */
$routes->group('admin', ['filter' => 'adminAuth'], function ($routes) {

    // --- DASHBOARD ---
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->post('user-notification/(:num)', 'Admin\ApiController::sendNotification/$1');
    $routes->post('broadcast-message', 'Admin\ApiController::sendBroadcast');

    // --- MANAJEMEN USERS ---
    $routes->group('users', function ($routes) {
        $routes->get('/', 'Admin\Users::index');             // List User: /admin/users
        $routes->get('search', 'Admin\Users::index');             // List User: /admin/users
        $routes->post('search', 'Admin\Users::searchByName');             // List User: /admin/users
        $routes->get('pending-kyc', 'Admin\Users::searchByPendingKYC');             // List User: /admin/users
        $routes->get('detail/(:num)', 'Admin\Users::detail/$1'); // Detail: /admin/users/detail/1
        
        // Proses Update (POST)
        $routes->post('updateStatus/(:num)', 'Admin\Users::updateStatus/$1'); // Update Status Akun
        $routes->post('approveKyc/(:num)', 'Admin\Users::approveKyc/$1');     // Approve/Reject KYC
    });
    $routes->group('tournaments', function ($routes) {
        // Tambahkan rute manajemen lainnya nanti di bawah sini
         $routes->get('/', 'Admin\Tournaments::index');
         // Rute Tambah Data
        $routes->get('create', 'Admin\Tournaments::create');
        $routes->post('store', 'Admin\Tournaments::store');
        // Rute Edit Data
        // (:num) adalah placeholder untuk ID berupa angka
        $routes->get('edit/(:num)', 'Admin\Tournaments::edit/$1');
        $routes->post('update/(:num)', 'Admin\Tournaments::update/$1');
        // Rute Update Status (Custom Method)
        $routes->post('updateStatus/(:num)', 'Admin\Tournaments::updateStatus/$1');
        // Rute Hapus Data
        // Menggunakan GET agar mudah dipanggil dari link, 
        // tapi secara profesional disarankan POST/DELETE untuk keamanan.
        $routes->get('delete/(:num)', 'Admin\Tournaments::delete/$1');
        $routes->get('view/(:num)', 'Admin\Tournaments::view/$1');
    });

     $routes->group('transactions', function ($routes) {
        $routes->get('/', 'Admin\TransactionController::index');
        $routes->get('detail/(:num)', 'Admin\TransactionController::detail/$1');
        $routes->post('ajax/update/(:num)', 'Admin\TransactionController::updateStatus/$1');
        $routes->get('summary', 'Admin\TransactionController::summary');
        $routes->get('finance', 'Admin\TransactionController::systemImpact');
     });
     
    $routes->get('ratings', 'Admin\RatingController::index');

    $routes->group('matches', function ($routes) {
        $routes->get('/', 'Admin\MatchController::index');
        $routes->get('live', 'Admin\MatchController::livematch');
        $routes->get('mirror/(:num)', 'Admin\MatchController::mirror/$1');
        $routes->get('create', 'Admin\MatchController::create');
        $routes->post('store', 'Admin\MatchController::store');
        $routes->get('ongoing', 'Admin\MatchController::ongoingMatches');
        $routes->get('export/(:any)', 'Admin\MatchController::export/$1');
        $routes->get('export', 'Admin\MatchController::export');
        
        $routes->get('tournament/(:num)', 'Admin\MatchController::tournamentMatches/$1');
        $routes->get('user/(:num)', 'Admin\MatchController::userMatches/$1');
        $routes->get('user', 'Admin\MatchController::userMatches');
        
        $routes->get('(:num)', 'Admin\MatchController::show/$1');
        $routes->post('(:num)/update-result', 'Admin\MatchController::updateResult/$1');
        $routes->delete('(:num)', 'Admin\MatchController::delete/$1');
    });
    $routes->group('accounting', function ($routes) {
        $routes->get('', 'Admin\Accounting::index');
        $routes->get('posting', 'Admin\Accounting::createJournal');
        $routes->get('autopost', 'Admin\Accounting::postJurnal');
        $routes->post('save_journal', 'Admin\Accounting::saveJournal');
        $routes->get('trial-balance', 'Admin\Accounting::trialBalance');
        $routes->get('income-statement', 'Admin\Accounting::incomeStatement');
        $routes->get('neraca', 'Admin\Accounting::balanceSheet');
        $routes->get('account-statement', 'Admin\Accounting::accountStatement');
        $routes->get('pl', 'Admin\Accounting::profitLoss');
    });
});