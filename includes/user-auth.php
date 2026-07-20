<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

const USER_LOGIN_MAX_FAILURES = 5;

function normalize_user_email(string $email): string
{
    return strtolower(trim($email));
}

function user_identifier_hash(string $value): string
{
    return hash('sha256', $value);
}

function user_client_ip_hash(): string
{
    return user_identifier_hash((string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'));
}

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([normalize_user_email($email)]);
    return $stmt->fetch() ?: null;
}

function find_user_by_id(int $id): ?array
{
    if ($id < 1) return null;
    $stmt = db()->prepare('SELECT id,full_name,email,role,account_status,institution,programme_or_position,last_login_at,created_at,updated_at,google_sub,google_email,avatar_url,auth_provider,google_linked_at,google_last_login_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function user_login_is_rate_limited(string $email, ?string $ipHash = null): bool
{
    $stmt = db()->prepare("SELECT COUNT(*) FROM user_login_attempts WHERE normalized_email_hash = ? AND ip_hash = ? AND success = 0 AND attempted_at >= (CURRENT_TIMESTAMP - INTERVAL 15 MINUTE)");
    $stmt->execute([user_identifier_hash(normalize_user_email($email)), $ipHash ?: user_client_ip_hash()]);
    return (int) $stmt->fetchColumn() >= USER_LOGIN_MAX_FAILURES;
}

function record_user_login_attempt(string $email, bool $success, ?string $ipHash = null): void
{
    try {
        $emailHash = user_identifier_hash(normalize_user_email($email));
        $ipHash = $ipHash ?: user_client_ip_hash();
        db()->exec("DELETE FROM user_login_attempts WHERE attempted_at < (CURRENT_TIMESTAMP - INTERVAL 30 DAY)");
        if ($success) {
            $reset = db()->prepare('DELETE FROM user_login_attempts WHERE normalized_email_hash = ? AND ip_hash = ? AND success = 0');
            $reset->execute([$emailHash, $ipHash]);
        }
        $stmt = db()->prepare('INSERT INTO user_login_attempts (normalized_email_hash, ip_hash, success) VALUES (?, ?, ?)');
        $stmt->execute([$emailHash, $ipHash, $success ? 1 : 0]);
    } catch (Throwable $exception) {
        error_log('User login attempt logging failed: ' . $exception->getMessage());
    }
}

/** @return array{success:bool,code:string,user:?array} */
function authenticate_user(string $email, string $password, ?string $ipHash = null): array
{
    try {
        if (user_login_is_rate_limited($email, $ipHash)) {
            return ['success' => false, 'code' => 'rate_limited', 'user' => null];
        }
        $user = find_user_by_email($email);
        $passwordValid = $user && password_verify($password, (string) $user['password_hash']);
        record_user_login_attempt($email, (bool) $passwordValid, $ipHash);
        if (!$passwordValid) return ['success' => false, 'code' => 'invalid', 'user' => null];
        if ($user['account_status'] !== 'active') {
            return ['success' => false, 'code' => (string) $user['account_status'], 'user' => null];
        }
        $stmt = db()->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$user['id']]);
        return ['success' => true, 'code' => 'ok', 'user' => $user];
    } catch (Throwable $exception) {
        error_log('User authentication failed safely: ' . $exception->getMessage());
        return ['success' => false, 'code' => 'error', 'user' => null];
    }
}

/** @return array{success:bool,message:string} */
function register_user_account(array $source): array
{
    $fullName = trim((string) ($source['full_name'] ?? ''));
    $email = normalize_user_email((string) ($source['email'] ?? ''));
    $role = (string) ($source['role'] ?? '');
    $institution = trim((string) ($source['institution'] ?? ''));
    $programme = trim((string) ($source['programme_or_position'] ?? ''));
    $password = (string) ($source['password'] ?? '');
    $passwordConfirmation = (string) ($source['password_confirmation'] ?? '');

    if (mb_strlen($fullName) < 3 || mb_strlen($fullName) > 160) {
        return ['success' => false, 'message' => tr('Masukkan nama penuh yang sah.', 'Enter a valid full name.')];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => tr('Masukkan alamat email yang sah.', 'Enter a valid email address.')];
    }
    if (!in_array($role, ['student', 'lecturer'], true)) {
        return ['success' => false, 'message' => tr('Pilih peranan pelajar atau pensyarah.', 'Select the student or lecturer role.')];
    }
    if (strlen($password) < 10) {
        return ['success' => false, 'message' => tr('Kata laluan mestilah sekurang-kurangnya 10 aksara.', 'The password must contain at least 10 characters.')];
    }
    if ($password !== $passwordConfirmation) {
        return ['success' => false, 'message' => tr('Pengesahan kata laluan tidak sepadan.', 'The password confirmation does not match.')];
    }
    if (find_user_by_email($email)) {
        return ['success' => false, 'message' => tr('Email ini sudah didaftarkan.', 'This email address is already registered.')];
    }

    try {
        $stmt = db()->prepare("INSERT INTO users (full_name,email,password_hash,role,account_status,institution,programme_or_position) VALUES (?,?,?,?,'pending',?,?)");
        $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $role, $institution ?: null, $programme ?: null]);
        return ['success' => true, 'message' => tr('Pendaftaran berjaya. Akaun sedang menunggu kelulusan admin.', 'Registration successful. The account is awaiting administrator approval.')];
    } catch (PDOException $exception) {
        if ((string) $exception->getCode() === '23000') {
            return ['success' => false, 'message' => tr('Email ini sudah didaftarkan.', 'This email address is already registered.')];
        }
        error_log('User registration failed: ' . $exception->getMessage());
        return ['success' => false, 'message' => tr('Pendaftaran tidak dapat disimpan. Sila cuba lagi.', 'The registration could not be saved. Please try again.')];
    }
}

function establish_user_session(array $user): void
{
    session_regenerate_id(true);
    unset($_SESSION['admin_user_id'], $_SESSION['admin_name'], $_SESSION['admin_login_at'], $_SESSION['admin_last_activity']);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = (string) $user['full_name'];
    $_SESSION['user_role'] = (string) $user['role'];
    $_SESSION['user_login_at'] = time();
    $_SESSION['user_last_activity'] = time();
    unset($_SESSION['csrf_token']);
}

function user_is_authenticated(): bool
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['user_login_at']) || empty($_SESSION['user_last_activity'])) return false;
    $now = time();
    if (($now - (int) $_SESSION['user_last_activity']) > ADMIN_IDLE_TIMEOUT || ($now - (int) $_SESSION['user_login_at']) > ADMIN_ABSOLUTE_LIFETIME) {
        clear_user_session();
        return false;
    }
    $user = find_user_by_id((int) $_SESSION['user_id']);
    if (!$user || $user['account_status'] !== 'active') {
        clear_user_session();
        return false;
    }
    $_SESSION['user_last_activity'] = $now;
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['role'];
    return true;
}

function current_user(): ?array
{
    return user_is_authenticated() ? find_user_by_id((int) $_SESSION['user_id']) : null;
}

function clear_user_session(): void
{
    foreach (['user_id','user_name','user_role','user_login_at','user_last_activity'] as $key) unset($_SESSION[$key]);
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
}

function app_base_path(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    foreach (['/dashboard/', '/admin/', '/auth/'] as $marker) {
        $position = strpos($script, $marker);
        if ($position !== false) return rtrim(substr($script, 0, $position), '/');
    }
    $directory = rtrim(str_replace('\\', '/', dirname($script)), '/.');
    return $directory === '/' ? '' : $directory;
}

function app_url(string $path = ''): string
{
    return app_base_path() . '/' . ltrim($path, '/');
}

function require_user(): void
{
    if (!user_is_authenticated()) {
        $next = (string) ($_SERVER['REQUEST_URI'] ?? app_url('dashboard/index.php'));
        header('Location: ' . app_url('login.php') . '?next=' . rawurlencode($next));
        exit;
    }
}

function safe_user_redirect(?string $next, string $fallback = 'dashboard/index.php'): string
{
    $next = trim((string) $next);
    if ($next === '' || str_contains($next, "\r") || str_contains($next, "\n")) return $fallback;
    $parts = parse_url($next);
    if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host'])) return $fallback;
    $path = $parts['path'] ?? '';
    $base = app_base_path();
    if (!is_string($path) || !str_starts_with($path, '/') || ($base !== '' && !str_starts_with($path, $base . '/')) || str_starts_with($path, '//')) return $fallback;
    return $next;
}
