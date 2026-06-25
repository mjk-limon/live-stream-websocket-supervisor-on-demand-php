<?php

require_once __DIR__ . '/../helpers/admin_auth.php';
require_once __DIR__ . '/../helpers/db.php';

admin_require_login();

$pdo = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT id, name, dash_url, hls_url, is_active, created_at FROM stream_sources ORDER BY id');
    json_response(['sources' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = read_json_body();
    $name = trim($data['name'] ?? 'Stream');
    $dashUrl = trim($data['dash_url'] ?? '');
    $hlsUrl = trim($data['hls_url'] ?? '');

    if ($dashUrl === '' && $hlsUrl === '') {
        json_response(['error' => 'DASH or HLS URLs are required'], 400);
    }

    $stmt = $pdo->prepare('INSERT INTO stream_sources (name, dash_url, hls_url, is_active) VALUES (?, ?, ?, 0)');
    $stmt->execute([$name, $dashUrl, $hlsUrl]);

    json_response(['success' => true, 'id' => (int) $pdo->lastInsertId()], 201);
}

if ($method === 'PUT') {
    $data = read_json_body();
    $id = (int) ($data['id'] ?? 0);

    if ($id <= 0) {
        json_response(['error' => 'Invalid source id'], 400);
    }

    if (!empty($data['activate'])) {
        $pdo->exec('UPDATE stream_sources SET is_active = 0');
        $stmt = $pdo->prepare('UPDATE stream_sources SET is_active = 1 WHERE id = ?');
        $stmt->execute([$id]);
        json_response(['success' => true]);
    }

    $name = trim($data['name'] ?? '');
    $dashUrl = trim($data['dash_url'] ?? '');
    $hlsUrl = trim($data['hls_url'] ?? '');

    $stmt = $pdo->prepare('UPDATE stream_sources SET name = ?, dash_url = ?, hls_url = ? WHERE id = ?');
    $stmt->execute([$name, $dashUrl, $hlsUrl, $id]);

    json_response(['success' => true]);
}

if ($method === 'DELETE') {
    $data = read_json_body();
    $id = (int) ($data['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        json_response(['error' => 'Invalid source id'], 400);
    }

    $stmt = $pdo->prepare('SELECT is_active FROM stream_sources WHERE id = ?');
    $stmt->execute([$id]);
    $source = $stmt->fetch();

    if (!$source) {
        json_response(['error' => 'Source not found'], 404);
    }

    if ((int) $source['is_active'] === 1) {
        json_response(['error' => 'Cannot delete the active stream source'], 400);
    }

    $stmt = $pdo->prepare('DELETE FROM stream_sources WHERE id = ?');
    $stmt->execute([$id]);

    json_response(['success' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
