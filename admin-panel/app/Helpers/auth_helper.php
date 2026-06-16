<?php

function auth()
{
    return (object) [
        'is_admin_logged_in' => session('is_admin_logged_in'),
        'admin_id'   => session('admin_id'),
        'admin_email'     => session('admin_email'),
        'admin_name'      => session('admin_name'),
        'admin_token'      => session('admin_token'),
        'admin_api_token'      => session("admin_api_token")
    ];
}
