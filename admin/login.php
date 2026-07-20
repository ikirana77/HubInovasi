<?php
require_once __DIR__ . '/../includes/admin-auth.php';
if (admin_is_authenticated()) { header('Location: index.php'); exit; }

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = normalize_admin_email((string) ($_POST['email'] ?? ''));
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Maklumat log masuk tidak sah.';
    } else {
        $admin = authenticate_admin($email, (string) ($_POST['password'] ?? ''));
        if ($admin) {
            establish_admin_session($admin);
            header('Location: index.php');
            exit;
        }
        $error = 'Maklumat log masuk tidak sah.';
    }
}
?>
<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow"><title>Log Masuk Admin | HubInovasi</title><link rel="icon" type="image/png" sizes="64x64" href="../assets/images/branding/favicon-64.png"><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/branding.css"></head>
<body class="admin-body"><main class="admin-login"><form method="post" class="admin-login__card"><a class="portal-brand portal-brand--compact" href="../index.php"><img src="../assets/images/branding/hubinovasi-kvks-primary.png" alt="HubInovasi KVKS — Platform Inovasi Pelajar"><span class="portal-brand__context">Admin</span></a><h1>Ruang Semakan</h1><p>Log masuk menggunakan akaun admin yang aktif.</p><?php if ($error): ?><div class="form-message form-message--error" role="alert"><?= e($error) ?></div><?php endif; ?><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label class="form-field"><span>Email</span><input type="email" name="email" required autocomplete="username"></label><label class="form-field"><span>Kata laluan</span><input type="password" name="password" required autocomplete="current-password"></label><button class="button button--primary" type="submit">Log Masuk</button><a href="../index.php">← Kembali ke laman awam</a></form></main></body></html>
