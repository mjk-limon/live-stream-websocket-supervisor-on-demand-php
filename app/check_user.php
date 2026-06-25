<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers/db.php';
require_once __DIR__ . '/helpers/supervisor_helpers.php';

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? $_POST['username'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit(json_encode(['exists' => false, 'error' => 'Invalid email address']));
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT id, email, name, pic FROM users WHERE email = ? COLLATE NOCASE');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    $defaultAvatar = get_default_avatar($pdo);
    $pic = $user['pic'] ?: avatar_for_name($user['name'], $defaultAvatar);

    http_response_code(200);
    start_socket_if_not_running();
    exit(json_encode([
        'exists' => true,
        'user' => [
            'id' => $user['email'],
            'email' => $user['email'],
            'name' => $user['name'],
            'pic' => $pic,
        ],
    ]));
}

http_response_code(404);
exit(json_encode(['exists' => false]));
