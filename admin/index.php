<?php
require_once __DIR__ . '/../includes/submission-repository.php';
require_admin();

$status = trim((string) ($_GET['status'] ?? ''));
$query = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = search_submissions($status, $query, $page, 10);
$summary = submission_summary_counts();
$statusLabels = ['' => 'Semua Status','pending_review'=>'Menunggu Semakan','needs_revision'=>'Perlu Pembetulan','published'=>'Diterbitkan','archived'=>'Diarkibkan','draft'=>'Draf'];

function dashboard_url(string $status, string $query, int $page = 1): string {
    return 'index.php?' . http_build_query(array_filter(['status'=>$status,'q'=>$query,'page'=>$page], static fn($v) => $v !== '' && $v !== 1));
}
?>
<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow"><title>Dashboard Admin | HubInovasi</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body class="admin-body">
<header class="admin-header"><div class="container"><div><p class="eyebrow">HubInovasi KVKS • Admin</p><h1>Ruang Semakan</h1><p>Selamat datang, <?= e($_SESSION['admin_name'] ?? 'Admin') ?>.</p></div><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="admin-logout" type="submit">Log keluar</button></form></div></header>
<main class="admin-main"><div class="container">
    <section class="admin-summary-grid" aria-label="Ringkasan submission">
        <?php foreach ([['all','Semua submission'],['pending_review','Menunggu Semakan'],['needs_revision','Perlu Pembetulan'],['published','Diterbitkan'],['archived','Diarkibkan']] as [$key,$label]): ?>
            <a href="<?= e(dashboard_url($key === 'all' ? '' : $key, '')) ?>" class="admin-summary-card <?= $status === ($key === 'all' ? '' : $key) ? 'is-active' : '' ?>"><strong><?= $summary[$key] ?></strong><span><?= e($label) ?></span></a>
        <?php endforeach; ?>
    </section>

    <form method="get" class="admin-toolbar" role="search">
        <label class="form-field"><span>Cari submission</span><input type="search" name="q" value="<?= e($query) ?>" placeholder="Nama projek, penghantar atau email"></label>
        <label class="form-field"><span>Status</span><select name="status"><?php foreach ($statusLabels as $value=>$label): ?><option value="<?= e($value) ?>" <?= $status===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
        <button class="button button--primary" type="submit">Tapis</button>
        <?php if ($query !== '' || $status !== ''): ?><a class="button button--secondary" href="index.php">Reset</a><?php endif; ?>
    </form>

    <div class="admin-list-heading"><div><p class="eyebrow">Queue Semakan</p><h2><?= $result['total'] ?> submission ditemui</h2></div><span>Terbaharu dahulu</span></div>
    <?php if (!$result['items']): ?><div class="admin-empty"><h2>Tiada submission ditemui.</h2><p>Cuba kata kunci atau status yang berbeza.</p></div><?php endif; ?>
    <div class="admin-submissions">
        <?php foreach ($result['items'] as $item): ?>
            <article class="admin-submission-row"><div class="admin-submission-row__main"><span class="status-badge status-badge--<?= e($item['status']) ?>"><?= e(submission_status_label($item['status'])) ?></span><h2><?= e($item['project_name'] ?: 'Draf tanpa nama') ?></h2><p><?= e($item['submitter_name'] ?: 'Penghantar belum dikenal pasti') ?><?= $item['submitter_email'] ? ' • ' . e($item['submitter_email']) : '' ?></p></div><dl><div><dt>Tahap</dt><dd><?= e($item['project_development_status'] ?: 'Belum dinyatakan') ?></dd></div><div><dt>Bukti</dt><dd><?= e($item['evidence_status'] ?: 'Belum dinyatakan') ?></dd></div><div><dt>Dihantar</dt><dd><?= $item['submitted_at'] ? e(date('d/m/Y H:i', strtotime($item['submitted_at']))) : 'Belum dihantar' ?></dd></div><div><dt>Dikemas kini</dt><dd><?= e(date('d/m/Y H:i', strtotime($item['updated_at']))) ?></dd></div></dl><a class="button button--primary" href="submission.php?id=<?= (int) $item['id'] ?>">Semak Projek</a></article>
        <?php endforeach; ?>
    </div>
    <?php if ($result['pages'] > 1): ?><nav class="admin-pagination" aria-label="Pagination submission"><?php for ($i=1;$i<=$result['pages'];$i++): ?><a href="<?= e(dashboard_url($status,$query,$i)) ?>" <?= $i===$result['page']?'aria-current="page"':'' ?>><?= $i ?></a><?php endfor; ?></nav><?php endif; ?>
</div></main></body></html>
