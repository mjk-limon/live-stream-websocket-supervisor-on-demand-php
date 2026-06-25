<?php

require_once __DIR__ . '/../helpers/admin_auth.php';
require_once __DIR__ . '/../helpers/db.php';

admin_require_login();

$pdo = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    json_response([
        'scrolling_text' => get_setting($pdo, 'scrolling_text', ''),
        'default_avatar' => get_default_avatar($pdo),
    ]);
}

if ($method === 'PUT') {
    $data = read_json_body();

    if (isset($data['scrolling_text'])) {
        set_setting($pdo, 'scrolling_text', trim($data['scrolling_text']));
    }

    if (isset($data['default_avatar'])) {
        set_setting($pdo, 'default_avatar', trim($data['default_avatar']));
    }

    if (isset($data['admin_password']) && $data['admin_password'] !== '') {
        set_setting($pdo, 'admin_password', password_hash($data['admin_password'], PASSWORD_DEFAULT));
    }

    json_response(['success' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
