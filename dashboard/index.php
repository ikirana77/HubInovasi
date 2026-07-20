<?php
require_once __DIR__ . '/../includes/submission-repository.php';
require_once __DIR__ . '/../includes/user-repository.php';
require_user();
$user = current_user();
$submissions = user_submissions((int) $user['id']);
$counts = user_submission_summary_counts((int) $user['id']);
$flash = $_SESSION['user_flash'] ?? null;
unset($_SESSION['user_flash']);
?>
<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow"><title>Dashboard Saya | HubInovasi</title><link rel="icon" type="image/png" sizes="64x64" href="../assets/images/branding/favicon-64.png"><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/branding.css"></head>
<body class="member-body">
<header class="member-header"><div class="container"><a class="portal-brand" href="../index.php"><picture><source media="(max-width:640px)" srcset="../assets/images/branding/hubinovasi-symbol.png"><img src="../assets/images/branding/hubinovasi-kvks-primary.png" alt="HubInovasi KVKS — Platform Inovasi Pelajar"></picture></a><nav><a aria-current="page" href="index.php">Projek Saya</a><a href="profile.php">Profil</a><a href="../submit-project.php" class="button button--primary">+ Projek Baharu</a><form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="admin-logout" type="submit">Log keluar</button></form></nav></div></header>
<main class="member-main"><div class="container">
    <?php if ($flash): ?><div class="form-message <?= $flash[0]==='error'?'form-message--error':'' ?>" role="status"><?= e($flash[1]) ?></div><?php endif; ?>
    <section class="member-welcome"><div><p class="eyebrow"><?= e(user_role_label($user['role'])) ?> • My HubInovasi</p><h1>Selamat datang, <?= e($user['full_name']) ?>.</h1><p>Semua projek anda dikawal dari ruang ini. Hanya projek yang diluluskan admin akan diterbitkan kepada umum.</p></div><a class="button button--primary" href="../submit-project.php">Hantar Projek Baharu ↗</a></section>
    <section class="member-summary-grid" aria-label="Ringkasan projek"><article><strong><?= $counts['all'] ?></strong><span>Semua Projek</span></article><article><strong><?= $counts['draft'] ?></strong><span>Draf</span></article><article><strong><?= $counts['pending_review'] ?></strong><span>Dalam Semakan</span></article><article><strong><?= $counts['needs_revision'] ?></strong><span>Perlu Pembetulan</span></article><article><strong><?= $counts['published'] ?></strong><span>Diterbitkan</span></article></section>
    <div class="member-list-heading"><div><p class="eyebrow">Project Workspace</p><h2>Projek Saya</h2></div><span><?= count($submissions) ?> rekod</span></div>
    <?php if (!$submissions): ?><section class="member-empty"><h2>Belum ada projek.</h2><p>Mulakan pitch pertama anda dan simpan sebagai draf sebelum dihantar untuk semakan.</p><a class="button button--primary" href="../submit-project.php">Cipta Projek Pertama</a></section><?php endif; ?>
    <section class="member-project-list">
    <?php foreach ($submissions as $item): $editable=in_array($item['status'],['draft','needs_revision'],true); ?>
        <article class="member-project-card">
            <div><span class="status-badge status-badge--<?= e($item['status']) ?>"><?= e(submission_status_label($item['status'])) ?></span><h2><?= e($item['project_name'] ?: 'Draf tanpa nama') ?></h2><p><?= e($item['tagline'] ?: 'Tagline belum diberikan.') ?></p></div>
            <dl><div><dt>Dikemas kini</dt><dd><?= e(date('d/m/Y H:i', strtotime($item['updated_at']))) ?></dd></div><div><dt>Dihantar</dt><dd><?= $item['submitted_at']?e(date('d/m/Y H:i',strtotime($item['submitted_at']))):'Belum dihantar' ?></dd></div></dl>
            <?php if ($item['admin_notes']): ?><div class="member-admin-note"><strong>Nota admin</strong><p><?= e($item['admin_notes']) ?></p></div><?php endif; ?>
            <div class="member-project-actions">
                <?php if ($editable): ?><a class="button button--primary" href="../submit-project.php?token=<?= e($item['public_token']) ?>"><?= $item['status']==='needs_revision'?'Buat Pembetulan':'Sambung Draf' ?></a><?php endif; ?>
                <?php if ($item['linked_project_slug']): ?><a class="button button--secondary" href="../project.php?slug=<?= e($item['linked_project_slug']) ?>">Lihat Projek Awam ↗</a><?php endif; ?>
                <?php if (!$editable && !$item['linked_project_slug']): ?><span class="member-waiting">Menunggu tindakan admin</span><?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
    </section>
</div></main></body></html>
