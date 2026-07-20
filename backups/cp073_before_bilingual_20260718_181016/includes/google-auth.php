<?php
declare(strict_types=1);
require_once __DIR__ . '/user-auth.php';

const GOOGLE_PENDING_IDENTITY_TTL = 900;

function google_config(): array
{
    static $config = null;
    if (is_array($config)) return $config;
    $config = require __DIR__ . '/../config/google.php';
    return $config;
}

function google_client_id(): string
{
    return trim((string) (google_config()['client_id'] ?? ''));
}

function absolute_app_url(string $path = ''): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $scheme . '://' . $host . app_url($path);
}

function google_login_uri(): string
{
    $configured = trim((string) (google_config()['login_uri'] ?? ''));
    return $configured !== '' ? $configured : absolute_app_url('auth/google-login.php');
}

function google_library_autoload_path(): ?string
{
    $path = __DIR__ . '/../vendor/autoload.php';
    return is_file($path) ? $path : null;
}

function verify_google_double_submit_csrf(): bool
{
    $cookie = (string) ($_COOKIE['g_csrf_token'] ?? '');
    $body = (string) ($_POST['g_csrf_token'] ?? '');
    return $cookie !== '' && $body !== '' && hash_equals($cookie, $body);
}

function google_base64url_decode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding !== 0) $value .= str_repeat('=', 4 - $padding);
    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    if ($decoded === false) throw new RuntimeException('Format token Google tidak sah.');
    return $decoded;
}

/** @return array<string,string> */
function google_public_certificates(bool $forceRefresh = false): array
{
    $cacheFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'hubinovasi-google-oauth-certs.json';
    if (!$forceRefresh && is_file($cacheFile)) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached) && (int) ($cached['expires_at'] ?? 0) > time() + 30 && is_array($cached['certs'] ?? null)) {
            return $cached['certs'];
        }
    }

    $url = 'https://www.googleapis.com/oauth2/v1/certs';
    $body = '';
    $maxAge = 1800;

    if (function_exists('curl_init')) {
        $headers = [];
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$headers): int {
                $length = strlen($line);
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
                return $length;
            },
        ]);
        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if (!is_string($response) || $status !== 200) {
            throw new RuntimeException('Kunci pengesahan Google tidak dapat dimuatkan' . ($error !== '' ? ': ' . $error : '.'));
        }
        $body = $response;
        if (preg_match('/max-age=(\d+)/i', (string) ($headers['cache-control'] ?? ''), $match)) $maxAge = max(300, (int) $match[1]);
    } else {
        $context = stream_context_create(['http' => ['timeout' => 10], 'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $response = @file_get_contents($url, false, $context);
        if (!is_string($response)) throw new RuntimeException('Kunci pengesahan Google tidak dapat dimuatkan.');
        $body = $response;
        foreach (($http_response_header ?? []) as $header) {
            if (preg_match('/^Cache-Control:.*max-age=(\d+)/i', $header, $match)) $maxAge = max(300, (int) $match[1]);
        }
    }

    $certs = json_decode($body, true);
    if (!is_array($certs) || $certs === []) throw new RuntimeException('Respons kunci Google tidak sah.');
    @file_put_contents($cacheFile, json_encode(['expires_at' => time() + $maxAge, 'certs' => $certs], JSON_UNESCAPED_SLASHES), LOCK_EX);
    return $certs;
}

/** @return array<string,mixed> */
function verify_google_jwt_natively(string $credential): array
{
    if (!extension_loaded('openssl')) throw new RuntimeException('PHP OpenSSL diperlukan untuk mengesahkan login Google.');
    $parts = explode('.', $credential);
    if (count($parts) !== 3) throw new RuntimeException('Format token Google tidak sah.');

    [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
    $header = json_decode(google_base64url_decode($encodedHeader), true);
    $payload = json_decode(google_base64url_decode($encodedPayload), true);
    $signature = google_base64url_decode($encodedSignature);
    if (!is_array($header) || !is_array($payload)) throw new RuntimeException('Kandungan token Google tidak sah.');
    if (($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) throw new RuntimeException('Algoritma token Google tidak dibenarkan.');

    $kid = (string) $header['kid'];
    $certs = google_public_certificates();
    if (!isset($certs[$kid])) $certs = google_public_certificates(true);
    $certificate = $certs[$kid] ?? null;
    if (!is_string($certificate) || $certificate === '') throw new RuntimeException('Kunci token Google tidak ditemui.');

    $verified = openssl_verify($encodedHeader . '.' . $encodedPayload, $signature, $certificate, OPENSSL_ALGO_SHA256);
    if ($verified !== 1) throw new RuntimeException('Tandatangan token Google tidak sah.');

    $now = time();
    $audience = $payload['aud'] ?? null;
    $audienceValid = is_array($audience) ? in_array(google_client_id(), $audience, true) : hash_equals(google_client_id(), (string) $audience);
    if (!$audienceValid) throw new RuntimeException('Token Google bukan untuk aplikasi HubInovasi.');
    if (!in_array((string) ($payload['iss'] ?? ''), ['accounts.google.com', 'https://accounts.google.com'], true)) throw new RuntimeException('Penerbit token Google tidak sah.');
    if ((int) ($payload['exp'] ?? 0) < $now - 60) throw new RuntimeException('Token Google telah tamat tempoh.');
    if ((int) ($payload['iat'] ?? 0) > $now + 300) throw new RuntimeException('Masa token Google tidak sah.');
    return $payload;
}

/** @return array<string,mixed> */
function verify_google_id_credential(string $credential): array
{
    $credential = trim($credential);
    if ($credential === '') throw new RuntimeException('Credential Google tidak diterima.');
    if (google_client_id() === '') throw new RuntimeException('Google Client ID belum dikonfigurasi.');

    $autoload = google_library_autoload_path();
    if ($autoload !== null) {
        require_once $autoload;
        if (class_exists('Google\\Client')) {
            $client = new Google\Client(['client_id' => google_client_id()]);
            $payload = $client->verifyIdToken($credential);
            if (!is_array($payload)) throw new RuntimeException('Token Google tidak sah atau telah tamat tempoh.');
            return $payload;
        }
    }

    return verify_google_jwt_natively($credential);
}

/** @return array{sub:string,email:string,full_name:string,avatar_url:?string} */
function normalized_google_identity(array $payload): array
{
    $sub = trim((string) ($payload['sub'] ?? ''));
    $email = normalize_user_email((string) ($payload['email'] ?? ''));
    $fullName = trim((string) ($payload['name'] ?? ''));
    $avatar = trim((string) ($payload['picture'] ?? ''));
    $verified = $payload['email_verified'] ?? false;
    $isVerified = in_array($verified, [true, 1, '1', 'true'], true);

    if ($sub === '' || strlen($sub) > 255) throw new RuntimeException('Google Account ID tidak sah.');
    if (!$isVerified || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email Google belum disahkan.');
    if ($fullName === '') $fullName = strstr($email, '@', true) ?: 'Pengguna HubInovasi';
    if (mb_strlen($fullName) > 160) $fullName = mb_substr($fullName, 0, 160);
    if ($avatar !== '' && (!filter_var($avatar, FILTER_VALIDATE_URL) || !str_starts_with(strtolower($avatar), 'https://'))) $avatar = '';

    return [
        'sub' => $sub,
        'email' => $email,
        'full_name' => $fullName,
        'avatar_url' => $avatar !== '' ? $avatar : null,
    ];
}

function find_user_by_google_sub(string $sub): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE google_sub = ? LIMIT 1');
    $stmt->execute([$sub]);
    return $stmt->fetch() ?: null;
}

/** @return array{action:string,user:?array,message:string,identity:?array} */
function resolve_verified_google_identity(array $identity): array
{
    $sub = (string) $identity['sub'];
    $email = (string) $identity['email'];
    $avatar = $identity['avatar_url'] ?? null;

    $user = find_user_by_google_sub($sub);
    if (!$user) {
        $user = find_user_by_email($email);
        if ($user) {
            $existingSub = trim((string) ($user['google_sub'] ?? ''));
            if ($existingSub !== '' && !hash_equals($existingSub, $sub)) {
                return ['action' => 'error', 'user' => null, 'message' => 'Email ini telah dipautkan kepada akaun Google yang lain.', 'identity' => null];
            }
            $provider = !empty($user['password_hash']) ? 'both' : 'google';
            try {
                $stmt = db()->prepare('UPDATE users SET google_sub=?, google_email=?, avatar_url=?, auth_provider=?, google_linked_at=COALESCE(google_linked_at,CURRENT_TIMESTAMP), google_last_login_at=CURRENT_TIMESTAMP WHERE id=?');
                $stmt->execute([$sub, $email, $avatar, $provider, $user['id']]);
            } catch (PDOException $exception) {
                error_log('Google account linking failed: ' . $exception->getMessage());
                return ['action' => 'error', 'user' => null, 'message' => 'Akaun Google tidak dapat dipautkan.', 'identity' => null];
            }
            $user = find_user_by_id((int) $user['id']);
        }
    } else {
        $stmt = db()->prepare('UPDATE users SET google_email=?, avatar_url=COALESCE(?,avatar_url), google_last_login_at=CURRENT_TIMESTAMP WHERE id=?');
        $stmt->execute([$email, $avatar, $user['id']]);
        $user = find_user_by_id((int) $user['id']);
    }

    if (!$user) {
        return ['action' => 'role_required', 'user' => null, 'message' => 'Lengkapkan pendaftaran HubInovasi.', 'identity' => $identity];
    }

    return match ((string) $user['account_status']) {
        'active' => ['action' => 'login', 'user' => $user, 'message' => 'Log masuk Google berjaya.', 'identity' => null],
        'pending' => ['action' => 'pending', 'user' => $user, 'message' => 'Akaun anda masih menunggu kelulusan admin.', 'identity' => null],
        'suspended' => ['action' => 'suspended', 'user' => $user, 'message' => 'Akaun ini telah digantung. Hubungi admin HubInovasi.', 'identity' => null],
        default => ['action' => 'error', 'user' => null, 'message' => 'Status akaun tidak sah.', 'identity' => null],
    };
}

function store_pending_google_identity(array $identity): void
{
    $_SESSION['pending_google_identity'] = [
        'identity' => $identity,
        'created_at' => time(),
    ];
}

function pending_google_identity(): ?array
{
    $pending = $_SESSION['pending_google_identity'] ?? null;
    if (!is_array($pending) || !is_array($pending['identity'] ?? null) || empty($pending['created_at'])) return null;
    if ((time() - (int) $pending['created_at']) > GOOGLE_PENDING_IDENTITY_TTL) {
        unset($_SESSION['pending_google_identity']);
        return null;
    }
    return $pending['identity'];
}

function clear_pending_google_identity(): void
{
    unset($_SESSION['pending_google_identity']);
}

/** @return array{success:bool,message:string,user:?array} */
function create_google_user_account(array $identity, string $role, string $institution = '', string $programme = ''): array
{
    if (!in_array($role, ['student', 'lecturer'], true)) {
        return ['success' => false, 'message' => 'Pilih peranan pelajar atau pensyarah.', 'user' => null];
    }
    $institution = trim($institution);
    $programme = trim($programme);
    if (mb_strlen($institution) > 255 || mb_strlen($programme) > 180) {
        return ['success' => false, 'message' => 'Maklumat institusi atau program terlalu panjang.', 'user' => null];
    }

    $existing = find_user_by_google_sub((string) $identity['sub']) ?: find_user_by_email((string) $identity['email']);
    if ($existing) {
        return ['success' => false, 'message' => 'Akaun ini sudah didaftarkan. Sila log masuk semula.', 'user' => $existing];
    }

    try {
        $stmt = db()->prepare("INSERT INTO users (full_name,email,password_hash,role,account_status,institution,programme_or_position,google_sub,google_email,avatar_url,auth_provider,google_linked_at,google_last_login_at) VALUES (?,?,NULL,?,'pending',?,?,?,?,?,'google',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
        $stmt->execute([
            $identity['full_name'], $identity['email'], $role,
            $institution !== '' ? $institution : null,
            $programme !== '' ? $programme : null,
            $identity['sub'], $identity['email'], $identity['avatar_url'] ?? null,
        ]);
        $user = find_user_by_id((int) db()->lastInsertId());
        return ['success' => true, 'message' => 'Pendaftaran Google berjaya. Akaun sedang menunggu kelulusan admin.', 'user' => $user];
    } catch (PDOException $exception) {
        if ((string) $exception->getCode() === '23000') {
            return ['success' => false, 'message' => 'Akaun Google atau email ini sudah didaftarkan.', 'user' => null];
        }
        error_log('Google user creation failed: ' . $exception->getMessage());
        return ['success' => false, 'message' => 'Pendaftaran Google tidak dapat disimpan.', 'user' => null];
    }
}

function set_google_auth_flash(string $type, string $message): void
{
    $_SESSION['google_auth_flash'] = [$type, $message];
}

function pull_google_auth_flash(): ?array
{
    $flash = $_SESSION['google_auth_flash'] ?? null;
    unset($_SESSION['google_auth_flash']);
    return is_array($flash) ? $flash : null;
}
