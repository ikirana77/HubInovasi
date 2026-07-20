<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

const LOGIN_MAX_FAILURES = 5;
const LOGIN_WINDOW_MINUTES = 15;

function normalize_admin_email(string $email): string
{
    return strtolower(trim($email));
}

function login_identifier_hash(string $value): string
{
    return hash('sha256', $value);
}

function client_ip_hash(): string
{
    return login_identifier_hash((string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'));
}

function find_admin_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([normalize_admin_email($email)]);
    return $stmt->fetch() ?: null;
}

function login_is_rate_limited(string $email, ?string $ipHash = null): bool
{
    $stmt = db()->prepare("SELECT COUNT(*) FROM admin_login_attempts WHERE normalized_email_hash = ? AND ip_hash = ? AND success = 0 AND attempted_at >= (CURRENT_TIMESTAMP - INTERVAL 15 MINUTE)");
    $stmt->execute([login_identifier_hash(normalize_admin_email($email)), $ipHash ?: client_ip_hash()]);
    return (int) $stmt->fetchColumn() >= LOGIN_MAX_FAILURES;
}

function record_login_attempt(string $email, bool $success, ?string $ipHash = null): void
{
    try {
        $emailHash = login_identifier_hash(normalize_admin_email($email));
        $ipHash = $ipHash ?: client_ip_hash();
        db()->exec("DELETE FROM admin_login_attempts WHERE attempted_at < (CURRENT_TIMESTAMP - INTERVAL 30 DAY)");
        if ($success) {
            $reset = db()->prepare('DELETE FROM admin_login_attempts WHERE normalized_email_hash = ? AND ip_hash = ? AND success = 0');
            $reset->execute([$emailHash, $ipHash]);
        }
        $stmt = db()->prepare('INSERT INTO admin_login_attempts (normalized_email_hash, ip_hash, success) VALUES (?, ?, ?)');
        $stmt->execute([$emailHash, $ipHash, $success ? 1 : 0]);
    } catch (Throwable $exception) {
        error_log('Login attempt logging failed: ' . $exception->getMessage());
    }
}

function authenticate_admin(string $email, string $password, ?string $ipHash = null): ?array
{
    try {
        if (login_is_rate_limited($email, $ipHash)) return null;
        $admin = find_admin_by_email($email);
        $valid = $admin && (bool) $admin['is_active'] && password_verify($password, $admin['password_hash']);
        record_login_attempt($email, (bool) $valid, $ipHash);
        if (!$valid) return null;

        $stmt = db()->prepare('UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$admin['id']]);
        return $admin;
    } catch (Throwable $exception) {
        error_log('Admin authentication failed safely: ' . $exception->getMessage());
        return null;
    }
}

function establish_admin_session(array $admin): void
{
    session_regenerate_id(true);
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role'], $_SESSION['user_login_at'], $_SESSION['user_last_activity']);
    $_SESSION['admin_user_id'] = (int) $admin['id'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_login_at'] = time();
    $_SESSION['admin_last_activity'] = time();
    unset($_SESSION['csrf_token']);
}
