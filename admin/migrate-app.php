<?php

require_once __DIR__ . '/../app/helpers/admin_auth.php';

try {
    if (! isset($_GET['pin']) || $_GET['pin'] != 12345) {
        json_response(['success' => false, 'message' => 'Not authorized']);
    }

    $pdo = get_db();
    init_schema($pdo);
    seed_defaults($pdo);
} catch (\Exception $e) {
    json_response(['success' => false]);
}

json_response(['success' => true]);
