<?php
require_once __DIR__ . '/../includes/submission-repository.php';
require_once __DIR__ . '/../includes/submission-category-details.php';
require_once __DIR__ . '/../includes/submission-impact-details.php';
require_once __DIR__ . '/../includes/submission-participants.php';
require_once __DIR__ . '/../includes/user-repository.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: (int) ($_POST['submission_id'] ?? 0);
if ($id < 1) { http_response_code(404); $submission = null; } else { $submission = find_submission_by_id($id); }
if (!$submission) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width"><title>Submission Tidak Dijumpai</title><link rel="icon" type="image/png" sizes="64x64" href="../assets/images/branding/favicon-64.png"><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/branding.css"></head><body class="admin-body"><main class="admin-not-found"><p class="eyebrow">404</p><h1>Submission tidak dijumpai.</h1><a href="index.php">Kembali ke dashboard</a></main></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = (string) ($_POST['target_status'] ?? '');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['admin_flash'] = ['error','Sesi tidak sah. Muat semula halaman.'];
    } else {
        $transition = transition_submission_status($id, $target, (int) $_SESSION['admin_user_id'], (string) ($_POST['admin_notes'] ?? ''));
        $_SESSION['admin_flash'] = [$transition['success'] ? 'success' : 'error', $transition['message']];
    }
    header('Location: submission.php?id=' . $id);
    exit;
}

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);
$history = submission_status_history($id);
$actions = allowed_submission_transitions('admin')[$submission['status']] ?? [];
$actionLabels = ['needs_revision'=>'Minta Pembetulan','published'=>'Terbitkan Projek','archived'=>'Arkibkan'];
$programmeCodes = decode_programme_codes($submission['programme_codes'] ?? null);
$programmeLabels = hub_programmes();
$programmeText = '—';
if ($programmeCodes) {
    $leadLabel = $programmeLabels[$programmeCodes[0]] ?? $programmeCodes[0];
    $contributorLabels = array_map(static fn (string $code): string => $programmeLabels[$code] ?? $code, array_slice($programmeCodes, 1));
    $programmeText = 'Peneraju: ' . $leadLabel;
    if ($contributorLabels) $programmeText .= '; Penyumbang: ' . implode(', ', $contributorLabels);
}
$fields = [
    'Problem'=>'problem','Solution'=>'solution','How It Works'=>'how_it_works','Key Features'=>'key_features','Impact'=>'impact',
    'Technology'=>'technologies','Team'=>'team_details','Project Journey'=>'project_journey','Evidence'=>'evidence_summary','Call to Action'=>'call_to_action',
];
$categoryFields = submission_category_fields((string) ($submission['solution_area'] ?? ''));
$categoryDetails = submission_category_details_for_submission((int) $submission['id']);
$impactDetails = submission_impact_details_for_submission((int) $submission['id']);
$participantDetails = submission_participants_for_submission((int) $submission['id']);
?>
<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow"><title>Semak <?= e($submission['project_name']) ?> | HubInovasi</title><link rel="icon" type="image/png" sizes="64x64" href="../assets/images/branding/favicon-64.png"><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/branding.css"></head>
<body class="admin-body"><header class="admin-detail-header"><div class="container"><a class="portal-brand portal-brand--compact" href="../index.php"><img src="../assets/images/branding/hubinovasi-kvks-primary.png" alt="HubInovasi KVKS — Platform Inovasi Pelajar"><span class="portal-brand__context">Admin</span></a><div class="admin-top-actions"><a href="index.php">← Dashboard</a><a href="users.php">Pengguna</a></div><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="admin-logout" type="submit">Log keluar</button></form></div></header><main class="admin-review"><div class="container">
    <?php if ($flash): ?><div class="form-message <?= $flash[0]==='error'?'form-message--error':'' ?>" role="status"><?= e($flash[1]) ?></div><?php endif; ?>
    <section class="admin-review-hero"><div><p class="eyebrow">Submission #HUB-<?= str_pad((string)$id,5,'0',STR_PAD_LEFT) ?></p><h1><?= e($submission['project_name'] ?: 'Draf tanpa nama') ?></h1><p><?= e($submission['tagline'] ?: 'Tagline belum diberikan.') ?></p></div><span class="status-badge status-badge--<?= e($submission['status']) ?>"><?= e(submission_status_label($submission['status'])) ?></span></section>
    <section class="admin-review-meta"><dl><div><dt>Penghantar</dt><dd><?= e($submission['submitter_name'] ?: '—') ?><br><?= e($submission['submitter_email'] ?: '—') ?><?php if($submission['owner_role']): ?><br><small><?= e(user_role_label($submission['owner_role'])) ?> • Akaun berdaftar</small><?php endif; ?></dd></div><div><dt>Bidang penyelesaian</dt><dd><?= e(solution_area_label($submission['solution_area'] ?? '')) ?></dd></div><div><dt>Jenis inovasi</dt><dd><?= e(innovation_type_label($submission['innovation_type'] ?? '')) ?></dd></div><div><dt>Program penyumbang</dt><dd><?= e($programmeText) ?></dd></div><div><dt>Tahap pembangunan</dt><dd><?= e($submission['project_development_status'] ?: '—') ?></dd></div><div><dt>Draf bermula</dt><dd><?= e(date('d/m/Y H:i',strtotime($submission['created_at']))) ?></dd></div><div><dt>Dihantar</dt><dd><?= $submission['submitted_at']?e(date('d/m/Y H:i',strtotime($submission['submitted_at']))):'—' ?></dd></div><div><dt>Disemak</dt><dd><?= $submission['reviewed_at']?e(date('d/m/Y H:i',strtotime($submission['reviewed_at']))):'—' ?></dd></div></dl><?php if ($submission['linked_project_id']): ?><a href="../project.php?slug=<?= e($submission['linked_project_slug']) ?>">Lihat projek diterbitkan ↗</a><?php endif; ?></section>
    <section class="admin-people-review"><p class="eyebrow">Bahagian 05</p><h2>Peserta & Mentor</h2><div><?php foreach ($participantDetails['students'] as $person): ?><article><?php if ($photo = submission_profile_photo_public_path($person['profile_image_path'] ?? null)): ?><img src="../<?= e($photo) ?>" alt=""><?php endif; ?><p><?php if ($person['is_team_leader']): ?><span>Ketua Pasukan</span><?php endif; ?><strong><?= e($person['full_name']) ?></strong><small><?= e($person['programme']) ?> · <?= e($person['class_name']) ?> · <?= e($person['year_of_study']) ?></small><em><?= e($person['role_title']) ?></em></p></article><?php endforeach; ?><?php foreach ($participantDetails['mentors'] as $person): ?><article><?php if ($photo = submission_profile_photo_public_path($person['profile_image_path'] ?? null)): ?><img src="../<?= e($photo) ?>" alt=""><?php endif; ?><p><span>Mentor</span><strong><?= e($person['full_name']) ?></strong><small><?= e($person['position_title']) ?> · <?= e($person['institution']) ?></small><em><?= e($person['role_title']) ?></em></p></article><?php endforeach; ?><?php if (!$participantDetails['students'] && !$participantDetails['mentors']): ?><p>Belum diberikan.</p><?php endif; ?></div></section>
    <div class="admin-review-layout"><div class="admin-pitch-sections"><?php $n=1; foreach($fields as $label=>$column): ?><article><span><?= str_pad((string)$n++,2,'0',STR_PAD_LEFT) ?></span><div><h2><?= e($label) ?></h2><p><?= nl2br(e($submission[$column] ?: 'Belum diberikan.')) ?></p><?php if($label==='Evidence'): ?><small>Status bukti: <?= e($submission['evidence_status'] ?: 'Belum dinyatakan') ?></small><?php endif; ?></div></article><?php endforeach; ?><?php if ($categoryFields): ?><article><span>03A</span><div><h2>Butiran Produk Mengikut Kategori</h2><dl class="admin-adaptive-details"><?php foreach ($categoryFields as $field): ?><div><dt><?= e($field['label']) ?></dt><dd><?= nl2br(e($categoryDetails[$field['key']] ?? 'Belum diberikan.')) ?></dd></div><?php endforeach; ?></dl></div></article><?php endif; ?><article><span>04A</span><div><h2>Metrik, Bukti & Pengiktirafan</h2><div class="admin-impact-collections"><section><h3>Metrik Impak</h3><?php if (!$impactDetails['metrics']): ?><p>Belum diberikan.</p><?php else: ?><ul><?php foreach ($impactDetails['metrics'] as $metric): ?><li><strong><?= e($metric['label']) ?></strong>: <?= e(($metric['value'] ?: $metric['target']) ?: '—') ?> <?= e($metric['unit'] ?: '') ?><?php if ($metric['evidence_notes']): ?><small><?= e($metric['evidence_notes']) ?></small><?php endif; ?></li><?php endforeach; ?></ul><?php endif; ?></section><section><h3>Bukti Impak</h3><?php if (!$impactDetails['evidence']): ?><p>Belum diberikan.</p><?php else: ?><ul><?php foreach ($impactDetails['evidence'] as $item): ?><li><strong><?= e($item['title']) ?></strong><?php if (valid_external_url($item['url'])): ?> — <a href="<?= e($item['url']) ?>" target="_blank" rel="noopener noreferrer">Buka bukti ↗</a><?php endif; ?><?php if ($item['description']): ?><small><?= e($item['description']) ?></small><?php endif; ?></li><?php endforeach; ?></ul><?php endif; ?></section><section><h3>Anugerah & Pengiktirafan</h3><?php if (!$impactDetails['recognitions']): ?><p>Tiada dinyatakan.</p><?php else: ?><ul><?php foreach ($impactDetails['recognitions'] as $recognition): ?><li><strong><?= e($recognition['title']) ?></strong><?= $recognition['level'] ? ' — ' . e($recognition['level']) : '' ?><?= $recognition['organiser'] ? '<small>' . e($recognition['organiser']) . '</small>' : '' ?></li><?php endforeach; ?></ul><?php endif; ?></section></div></div></article></div>
    <aside class="admin-review-sidebar"><section><p class="eyebrow">Tindakan Sah</p><h2>Keputusan Semakan</h2><?php if(!$actions): ?><p>Tiada tindakan lanjut tersedia untuk status ini.</p><?php endif; ?><?php foreach($actions as $target): ?><details class="admin-action admin-action--<?= e($target) ?>"><summary><?= e($actionLabels[$target] ?? submission_status_label($target)) ?></summary><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="submission_id" value="<?= $id ?>"><input type="hidden" name="target_status" value="<?= e($target) ?>"><?php if(in_array($target,['needs_revision','archived'],true)): ?><label class="form-field"><span>Nota admin (wajib)</span><textarea name="admin_notes" rows="4" required></textarea></label><?php else: ?><input type="hidden" name="admin_notes" value=""><?php endif; ?><p class="admin-confirmation">Sahkan tindakan ini. Perubahan akan direkodkan dalam audit trail.</p><button class="button <?= $target==='published'?'button--primary':'button--secondary' ?>" type="submit"><?= e($actionLabels[$target] ?? 'Sahkan') ?></button></form></details><?php endforeach; ?></section><section><p class="eyebrow">Sejarah Status</p><?php if(!$history): ?><p>Belum ada perubahan status oleh admin.</p><?php endif; ?><ol class="admin-history"><?php foreach($history as $entry): ?><li><strong><?= e(submission_status_label($entry['from_status'])) ?> → <?= e(submission_status_label($entry['to_status'])) ?></strong><span><?= e($entry['admin_name'] ?: 'Sistem') ?> • <?= e(date('d/m/Y H:i',strtotime($entry['created_at']))) ?></span><?php if($entry['admin_notes']): ?><p><?= e($entry['admin_notes']) ?></p><?php endif; ?></li><?php endforeach; ?></ol><?php if($submission['admin_notes']): ?><div class="admin-latest-note"><strong>Nota terkini</strong><p><?= e($submission['admin_notes']) ?></p></div><?php endif; ?></section></aside></div>
</div></main></body></html>
