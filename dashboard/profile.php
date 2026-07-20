<?php
require_once __DIR__ . '/../includes/user-repository.php';
require_user();
$user = current_user();
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $message = ['error','Sesi tidak sah. Muat semula halaman.'];
    } else {
        $result = update_user_profile((int) $user['id'], $_POST);
        $message = [$result['success']?'success':'error',$result['message']];
        $user = current_user();
    }
}
?>
<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow"><title>Profil Saya | HubInovasi</title><link rel="icon" type="image/png" sizes="64x64" href="../assets/images/branding/favicon-64.png"><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/branding.css"></head>
<body class="member-body"><header class="member-header"><div class="container"><a class="portal-brand" href="../index.php"><picture><source media="(max-width:640px)" srcset="../assets/images/branding/hubinovasi-symbol.png"><img src="../assets/images/branding/hubinovasi-kvks-primary.png" alt="HubInovasi KVKS — Platform Inovasi Pelajar"></picture></a><nav><a href="index.php">Projek Saya</a><a aria-current="page" href="profile.php">Profil</a><a href="../submit-project.php" class="button button--primary">+ Projek Baharu</a><form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="admin-logout" type="submit">Log keluar</button></form></nav></div></header>
<main class="member-main"><div class="container member-profile-layout"><section class="member-profile-intro"><p class="eyebrow">Profil Penyumbang</p><h1><?= e($user['full_name']) ?></h1><p><?= e(user_role_label($user['role'])) ?> • <?= e($user['email']) ?></p><span class="account-status account-status--<?= e($user['account_status']) ?>"><?= e(user_status_label($user['account_status'])) ?></span></section><form method="post" class="auth-card"><p class="eyebrow">Maklumat Profil</p><h2>Kemas kini butiran</h2><?php if($message): ?><div class="form-message <?= $message[0]==='error'?'form-message--error':'' ?>"><?= e($message[1]) ?></div><?php endif; ?><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label class="form-field"><span>Institusi</span><input type="text" name="institution" maxlength="255" value="<?= e($user['institution'] ?? '') ?>"></label><label class="form-field"><span>Program / jawatan</span><input type="text" name="programme_or_position" maxlength="180" value="<?= e($user['programme_or_position'] ?? '') ?>"></label><button class="button button--primary" type="submit">Simpan Profil</button></form></div></main></body></html>
