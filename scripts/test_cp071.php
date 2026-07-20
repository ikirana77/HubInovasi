<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/google-auth.php';
require_once __DIR__ . '/../includes/user-repository.php';

function cp071_check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
    echo "[PASS] {$message}\n";
}

$pdo = db();
$pdo->beginTransaction();
try {
    $adminEmail = 'cp071-admin-' . bin2hex(random_bytes(4)) . '@example.test';
    $pdo->prepare("INSERT INTO admin_users(full_name,email,password_hash,role,is_active) VALUES(?,?,?,'admin',1)")
        ->execute(['CP07.1 Admin',$adminEmail,password_hash('CP071-Admin-Password!',PASSWORD_DEFAULT)]);
    $adminId = (int) $pdo->lastInsertId();

    $identity = [
        'sub' => 'cp071-google-' . bin2hex(random_bytes(8)),
        'email' => 'cp071-user-' . bin2hex(random_bytes(4)) . '@gmail.com',
        'full_name' => 'CP07.1 Google Student',
        'avatar_url' => 'https://example.com/avatar.png',
    ];

    $created = create_google_user_account($identity, 'student', 'KVKS', '2 DVM KPD');
    cp071_check($created['success'], 'Google account registration succeeds');
    $user = find_user_by_google_sub($identity['sub']);
    cp071_check($user && $user['account_status'] === 'pending', 'Google account starts as pending');
    cp071_check($user && $user['auth_provider'] === 'google', 'Google auth provider is stored');
    cp071_check($user && $user['password_hash'] === null, 'Google-only account does not require password');

    $resolvedPending = resolve_verified_google_identity($identity);
    cp071_check($resolvedPending['action'] === 'pending', 'Pending Google user cannot enter dashboard');

    $approval = transition_user_status((int) $user['id'], 'active', $adminId, 'CP07.1 test approval');
    cp071_check($approval['success'], 'Admin can activate Google account');
    $resolvedActive = resolve_verified_google_identity($identity);
    cp071_check($resolvedActive['action'] === 'login', 'Active Google user can login');

    $passwordEmail = 'cp071-link-' . bin2hex(random_bytes(4)) . '@gmail.com';
    $pdo->prepare("INSERT INTO users(full_name,email,password_hash,role,account_status,auth_provider) VALUES(?,?,?,'lecturer','active','password')")
        ->execute(['Existing Password Lecturer',$passwordEmail,password_hash('Existing-Password-123!',PASSWORD_DEFAULT)]);
    $linkIdentity = [
        'sub' => 'cp071-link-sub-' . bin2hex(random_bytes(8)),
        'email' => $passwordEmail,
        'full_name' => 'Existing Password Lecturer',
        'avatar_url' => null,
    ];
    $linked = resolve_verified_google_identity($linkIdentity);
    cp071_check($linked['action'] === 'login', 'Verified Google email links to existing active account');
    $linkedUser = find_user_by_email($passwordEmail);
    cp071_check($linkedUser && $linkedUser['auth_provider'] === 'both', 'Linked account keeps password and Google login');
    cp071_check($linkedUser && $linkedUser['google_sub'] === $linkIdentity['sub'], 'Stable Google sub is stored');

    $pdo->rollBack();
    echo "[PASS] CP07.1 test transaction rolled back\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "[FAIL] {$exception->getMessage()}\n");
    exit(1);
}
