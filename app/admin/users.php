<?php

require_once __DIR__ . '/../helpers/admin_auth.php';
require_once __DIR__ . '/../helpers/db.php';

admin_require_login();

$pdo = get_db();
$method = $_SERVER['REQUEST_METHOD'];
$defaultAvatar = get_default_avatar($pdo);

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT id, email, name, pic, created_at FROM users ORDER BY name');
    $users = $stmt->fetchAll();

    foreach ($users as &$user) {
        $user['pic'] = $user['pic'] ?: avatar_for_name($user['name'], $defaultAvatar);
    }

    json_response(['users' => $users]);
}

if ($method === 'POST') {
    $data = read_json_body();
    $email = trim($data['email'] ?? '');
    $name = trim($data['name'] ?? '');
    $pic = trim($data['pic'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Valid email is required'], 400);
    }

    if ($name === '') {
        json_response(['error' => 'Name is required'], 400);
    }

    if ($pic === '') {
        $pic = avatar_for_name($name, $defaultAvatar);
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO users (email, name, pic) VALUES (?, ?, ?)');
        $stmt->execute([$email, $name, $pic]);
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'UNIQUE')) {
            json_response(['error' => 'Email already exists'], 409);
        }
        throw $e;
    }

    json_response([
        'success' => true,
        'user' => [
            'id' => (int) $pdo->lastInsertId(),
            'email' => $email,
            'name' => $name,
            'pic' => $pic,
        ],
    ], 201);
}

if ($method === 'PUT') {
    $data = read_json_body();
    $id = (int) ($data['id'] ?? 0);
    $email = trim($data['email'] ?? '');
    $name = trim($data['name'] ?? '');
    $pic = trim($data['pic'] ?? '');

    if ($id <= 0) {
        json_response(['error' => 'Invalid user id'], 400);
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Valid email is required'], 400);
    }

    if ($name === '') {
        json_response(['error' => 'Name is required'], 400);
    }

    if ($pic === '') {
        $pic = avatar_for_name($name, $defaultAvatar);
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET email = ?, name = ?, pic = ? WHERE id = ?');
        $stmt->execute([$email, $name, $pic, $id]);
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'UNIQUE')) {
            json_response(['error' => 'Email already exists'], 409);
        }
        throw $e;
    }

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'User not found'], 404);
    }

    json_response(['success' => true]);
}

if ($method === 'DELETE') {
    $data = read_json_body();
    $id = (int) ($data['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        json_response(['error' => 'Invalid user id'], 400);
    }

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'User not found'], 404);
    }

    json_response(['success' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
