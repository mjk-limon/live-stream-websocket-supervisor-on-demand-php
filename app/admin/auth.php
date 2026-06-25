<?php

require_once __DIR__ . '/../helpers/admin_auth.php';

admin_session_start();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    json_response(['logged_in' => admin_is_logged_in()]);
}

if ($method === 'POST') {
    $data = read_json_body();
    $password = $data['password'] ?? '';

    if (admin_login($password)) {
        json_response(['success' => true]);
    }

    json_response(['error' => 'Invalid password'], 401);
}

if ($method === 'DELETE') {
    admin_logout();
    json_response(['success' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
