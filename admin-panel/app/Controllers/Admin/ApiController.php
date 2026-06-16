<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Helpers\helper;

class ApiController extends BaseController
{
    function __construct () {
               

    }
    public function sendNotification($userId)
    {

          $title = $this->request->getPost('title');
        $message = $this->request->getPost('message');
        $type = $this->request->getPost('notificationType');


   
        
        $result = send_notification($userId, $title, $message,  $type);
         
        if ($result['success']) {
            return redirect()->back()->with('success', 'Notifikasi berhasil dikirim');
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim notifikasi: ' . $result['message']);
        }
    }
    public function sendBroadcast()
    {

        $title = $this->request->getPost('broadcastTitle');
        $message = $this->request->getPost('broadcastMessage');
        $type = $this->request->getPost('broadcastType');

        $result = send_broadcast($title, $message, $type);
    
        if ($result['success']) {
            return redirect()->back()->with('success', 'Broadcast berhasil dikirim');
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim Broadcast: ' . $result['message']);
        }
    }
    public function sendAutoBroadcast()
    {

        $title = $this->request->getPost('broadcastTitle');
        $message = $this->request->getPost('broadcastMessage');
        $type = $this->request->getPost('broadcastType');

        $result = send_broadcast($title, $message, $type);
    
        if ($result['success']) {
            return redirect()->back()->with('success', 'Broadcast berhasil dikirim');
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim Broadcast: ' . $result['message']);
        }
    }
     
}