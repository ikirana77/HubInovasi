<?php
require_once __DIR__ . '/../includes/submission-repository.php';
if (!admin_is_authenticated()) { header('Location: login.php'); exit; }

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $message = ['type' => 'error', 'text' => 'Sesi tidak sah. Muat semula halaman.'];
    } else {
        $updated = admin_update_submission((int) ($_POST['submission_id'] ?? 0), (string) ($_POST['status'] ?? ''), trim((string) ($_POST['admin_notes'] ?? '')));
        $message = ['type' => $updated ? 'success' : 'error', 'text' => $updated ? 'Status submission telah dikemas kini.' : 'Status tidak dapat dikemas kini.'];
    }
}
$submissions = get_all_submissions();
$statuses = ['draft','pending_review','needs_revision','published','archived'];
?>
<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Dashboard Admin | HubInovasi</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body class="admin-body"><header class="admin-header"><div class="container"><div><p class="eyebrow">HubInovasi KVKS</p><h1>Aliran Semakan</h1></div><a href="logout.php">Log keluar</a></div></header><main class="admin-main"><div class="container"><?php if ($message): ?><div class="form-message <?= $message['type'] === 'error' ? 'form-message--error' : '' ?>"><?= e($message['text']) ?></div><?php endif; ?><div class="admin-summary"><strong><?= count($submissions) ?></strong><span>Jumlah submission</span></div><?php if (!$submissions): ?><div class="admin-empty"><h2>Belum ada submission.</h2><p>Submission yang disimpan sebagai draft atau dihantar untuk semakan akan muncul di sini.</p></div><?php endif; ?><div class="admin-submissions"><?php foreach ($submissions as $submission): ?><article class="admin-submission"><header><div><span class="status-badge status-badge--<?= e($submission['status']) ?>"><?= e(str_replace('_', ' ', $submission['status'])) ?></span><h2><?= e($submission['project_name'] ?: 'Draft tanpa nama') ?></h2><p><?= e($submission['submitter_name'] ?: 'Penghantar belum dikenal pasti') ?><?= $submission['submitter_email'] ? ' • ' . e($submission['submitter_email']) : '' ?></p></div><time datetime="<?= e($submission['updated_at']) ?>"><?= e(date('d/m/Y H:i', strtotime($submission['updated_at']))) ?></time></header><details><summary>Lihat kandungan pitch</summary><dl class="admin-pitch"><div><dt>Masalah</dt><dd><?= e($submission['problem']) ?: '—' ?></dd></div><div><dt>Penyelesaian</dt><dd><?= e($submission['solution']) ?: '—' ?></dd></div><div><dt>Impak</dt><dd><?= e($submission['impact']) ?: '—' ?></dd></div></dl></details><form method="post" class="admin-review-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="submission_id" value="<?= (int) $submission['id'] ?>"><label class="form-field"><span>Status semakan</span><select name="status"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $submission['status'] === $status ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $status)) ?></option><?php endforeach; ?></select></label><label class="form-field"><span>Nota admin</span><textarea name="admin_notes" rows="3"><?= e($submission['admin_notes']) ?></textarea></label><button class="button button--primary" type="submit">Kemas Kini Status</button></form></article><?php endforeach; ?></div></div></main></body></html>
