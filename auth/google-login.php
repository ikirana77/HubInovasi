<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/google-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('login.php'));
    exit;
}

try {
    if (!verify_google_double_submit_csrf()) {
        throw new RuntimeException('Pengesahan keselamatan Google gagal. Sila cuba lagi.');
    }

    $payload = verify_google_id_credential((string) ($_POST['credential'] ?? ''));
    $identity = normalized_google_identity($payload);
    $result = resolve_verified_google_identity($identity);

    if ($result['action'] === 'role_required' && $result['identity']) {
        store_pending_google_identity($result['identity']);
        header('Location: ' . app_url('google-role.php'));
        exit;
    }

    if ($result['action'] === 'login' && $result['user']) {
        establish_user_session($result['user']);
        $next = (string) ($_SESSION['google_next'] ?? '');
        unset($_SESSION['google_next']);
        header('Location: ' . ($next !== '' ? $next : app_url('dashboard/index.php')));
        exit;
    }

    set_google_auth_flash($result['action'] === 'error' ? 'error' : 'info', $result['message']);
} catch (Throwable $exception) {
    error_log('Google login failed safely: ' . $exception->getMessage());
    set_google_auth_flash('error', $exception->getMessage());
}

header('Location: ' . app_url('login.php'));
exit;
