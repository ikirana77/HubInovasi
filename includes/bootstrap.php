<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    set_exception_handler(static function (Throwable $exception): void {
        error_log('Unhandled application error: ' . $exception->getMessage());
        if (!headers_sent()) http_response_code(500);
        echo '<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width"><title>Ralat Sistem | HubInovasi</title></head><body><main><h1>Permintaan tidak dapat diproses.</h1><p>Sila cuba lagi sebentar atau hubungi pentadbir sistem.</p></main></body></html>';
    });
}

const ADMIN_IDLE_TIMEOUT = 1800;
const ADMIN_ABSOLUTE_LIFETIME = 28800;

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/i18n.php';

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
    if (empty($_SESSION['admin_user_id']) || empty($_SESSION['admin_login_at']) || empty($_SESSION['admin_last_activity'])) {
        return false;
    }
    $now = time();
    if (($now - (int) $_SESSION['admin_last_activity']) > ADMIN_IDLE_TIMEOUT || ($now - (int) $_SESSION['admin_login_at']) > ADMIN_ABSOLUTE_LIFETIME) {
        destroy_session();
        return false;
    }
    $_SESSION['admin_last_activity'] = $now;
    return true;
}

function destroy_session(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
}

function require_admin(): void
{
    if (!admin_is_authenticated()) {
        header('Location: login.php');
        exit;
    }
}
