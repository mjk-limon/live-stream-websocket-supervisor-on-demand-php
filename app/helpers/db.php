<?php

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dataDir = __DIR__ . '/../../data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        $pdo = new PDO('sqlite:' . $dataDir . '/stream.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    return $pdo;
}

function init_schema(PDO $pdo): void
{
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL COLLATE NOCASE,
            name TEXT NOT NULL,
            pic TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS stream_sources (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT "",
            dash_url TEXT NOT NULL,
            hls_url TEXT NOT NULL,
            is_active INTEGER NOT NULL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ');
}

function seed_defaults(PDO $pdo): void
{
    if (get_setting($pdo, 'seeded') === '1') {
        return;
    }

    set_setting($pdo, 'scrolling_text', 'পরীক্ষামূলকভাবে সম্প্রচার চালু হয়েছে');
    set_setting($pdo, 'default_avatar', 'https://ui-avatars.com/api/?background=random&rounded=true&&color=fff&size=128');
    set_setting($pdo, 'admin_password', password_hash('admin', PASSWORD_DEFAULT));

    $stmt = $pdo->query('SELECT COUNT(*) FROM stream_sources');
    if ((int) $stmt->fetchColumn() === 0) {
        $insert = $pdo->prepare('INSERT INTO stream_sources (name, dash_url, hls_url, is_active) VALUES (?, ?, ?, 1)');
        $insert->execute([
            'Default',
            '',
            'https://devstreaming-cdn.apple.com/videos/streaming/examples/img_bipbop_adv_example_fmp4/master.m3u8',
        ]);
    }

    $legacyUsers = [
        ['jahid.limon@prothomalo.com', 'Jahidul Hasan Limon', 'https://lh3.googleusercontent.com/a/ACg8ocLqSjtRwnCpooqgDZWAyIDUOygLqYQ3E-4S_oW3tns_54nmrwM=s64-p-k-rw-no'],
        ['imtiaz.amin@prothomalo.com', 'Imtiaz Amin', 'https://lh3.googleusercontent.com/a-/ALV-UjXfbUEMSV9nb6gqMzw7DsOv-ca_2S0BOeqXDIodinPHyuntOgE=s64-p-k-rw-no'],
        ['taiful.islam@prothomalo.com', 'Md. Taiful Islam', 'https://lh3.googleusercontent.com/a-/ALV-UjVm83G6bARRFrqvKbbB5gMMCgUQBV3Pdv6P7pj5vXxSfm7AWQzL=s64-p-k-rw-no'],
        ['tasnim.sami@prothomalo.com', 'Md.Tasnim Sami Khan', 'https://lh3.googleusercontent.com/a-/ALV-UjXh5Vk1i4VCUFqk5tzA5wwSFT90n3qOWVALP-6iiJJvowYN6fs=s64-p-k-rw-no'],
        ['mohammad.fahad@prothomalo.com', 'Mohammad Fahad', 'https://lh3.googleusercontent.com/a-/ALV-UjXh5Vk1i4VCUFqk5tzA5wwSFT90n3qOWVALP-6iiJJvowYN6fs=s64-p-k-rw-no'],
        ['mohammad.yasin@prothomalo.com', 'Mohammad Yasin', 'https://lh3.googleusercontent.com/a-/ALV-UjVQg8VEHUg7ftM4yryCHg8zkDJUx_MWEkkfe_2MZ9KiFpFTrxHk=s64-p-k-rw-no'],
        ['nurul.islam@prothomalo.com', 'Nurul Islam', 'https://lh3.googleusercontent.com/a-/ALV-UjWVC4SbqsTZ4v4IL_QpSWgL2SErISUATwpi4k1mvmvplXjiyaJG=s64-p-k-rw-no'],
        ['tanvir.anzum@prothomalo.com', 'Tanvir Anzum', 'https://lh3.googleusercontent.com/a-/ALV-UjVtLOTaWdkWmtpOZs9jQmpQmrC1JkMc9SBS6dEIZCRe0fyb8ks=s64-p-k-rw-no'],
        ['monoranjan.sutradhar@prothomalo.com', 'Monoranjan Sutradhar', 'https://lh3.googleusercontent.com/a-/ALV-UjX-aJ6G9v80k6MXyr-jQvME74w-2aL2guOfqimt7YrzEzJKuMk=s64-p-k-rw-no'],
        ['mithun.adhikary@prothomalo.com', 'Mithun Kumar Adhikary', 'https://lh3.googleusercontent.com/a-/ALV-UjVls0CDxo6NicNFSg1dFoLvuiHAlWC82WnVINZlVsZ9NFrln4v7=s64-p-k-rw-no'],
        ['asif.aman@prothomalo.com', 'Asif Aman', 'https://lh3.googleusercontent.com/a-/ALV-UjWwGz5i3CnrOjimWOvI4819mZE-7BWosDXEhCZEnbAvCDyrn5Y=s64-p-k-rw-no'],
    ];

    $defaultAvatar = get_setting($pdo, 'default_avatar');
    $insertUser = $pdo->prepare('INSERT OR IGNORE INTO users (email, name, pic) VALUES (?, ?, ?)');
    foreach ($legacyUsers as [$email, $name, $pic]) {
        $insertUser->execute([$email, $name, $pic ?: $defaultAvatar]);
    }

    set_setting($pdo, 'seeded', '1');
}

function get_setting(PDO $pdo, string $key, ?string $default = null): ?string
{
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();

    return $row ? $row['value'] : $default;
}

function set_setting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare('INSERT INTO settings (key, value) VALUES (?, ?)
        ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    $stmt->execute([$key, $value]);
}

function get_default_avatar(PDO $pdo): string
{
    return get_setting($pdo, 'default_avatar', 'https://ui-avatars.com/api/?background=random&rounded=true&color=fff&size=128');
}

function avatar_for_name(string $name, string $defaultAvatar): string
{
    if (str_contains($defaultAvatar, 'ui-avatars.com')) {
        return $defaultAvatar . '&name=' . urlencode($name);
    }

    return $defaultAvatar;
}
