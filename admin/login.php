<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/../config/admin.php';
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi tidak sah. Sila cuba lagi.';
    } elseif ($config['password'] !== '' && hash_equals($config['password'], (string) ($_POST['password'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Kata laluan tidak sah.';
    }
}
?>
<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Log Masuk Admin | HubInovasi</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body class="admin-body"><main class="admin-login"><form method="post" class="admin-login__card"><p class="eyebrow">HubInovasi KVKS</p><h1>Semakan Admin</h1><p>Log masuk untuk mengurus status submission.</p><?php if ($error): ?><div class="form-message form-message--error"><?= e($error) ?></div><?php endif; ?><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label class="form-field"><span>Kata laluan</span><input type="password" name="password" required autocomplete="current-password"></label><button class="button button--primary" type="submit">Log Masuk</button><a href="../index.php">← Kembali ke laman awam</a></form></main></body></html>
