<?php

require_once __DIR__ . '/../helpers/db.php';

header('Content-Type: application/json');

$pdo = get_db();

$stmt = $pdo->query('SELECT dash_url, hls_url FROM stream_sources WHERE is_active = 1 LIMIT 1');
$stream = $stmt->fetch() ?: ['dash_url' => '', 'hls_url' => ''];

echo json_encode([
    'scrolling_text' => get_setting($pdo, 'scrolling_text', ''),
    'dash_url' => $stream['dash_url'],
    'hls_url' => $stream['hls_url'],
]);
