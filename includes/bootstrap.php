<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/** @return PDO */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/../config/database.php';
    $dsn = !empty($config['socket'])
        ? sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s', $config['socket'], $config['database'], $config['charset'])
        : sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $config['host'], $config['port'], $config['database'], $config['charset']);

    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function valid_external_url(?string $url): bool
{
    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function admin_is_authenticated(): bool
{
    return !empty($_SESSION['admin_authenticated']);
}
