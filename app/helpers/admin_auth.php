<?php

require_once __DIR__ . '/db.php';

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function admin_is_logged_in(): bool
{
    admin_session_start();

    return !empty($_SESSION['admin_logged_in']);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json');
        exit(json_encode(['error' => 'Unauthorized']));
    }
}

function admin_login(string $password): bool
{
    $pdo = get_db();
    $hash = get_setting($pdo, 'admin_password');

    if (!$hash || !password_verify($password, $hash)) {
        return false;
    }

    admin_session_start();
    $_SESSION['admin_logged_in'] = true;

    return true;
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    exit(json_encode($data));
}

function read_json_body(): array
{
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    return is_array($data) ? $data : [];
}
