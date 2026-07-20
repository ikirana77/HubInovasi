<?php
require_once __DIR__ . '/../includes/user-repository.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 0;
    $target = (string) ($_POST['target_status'] ?? '');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['admin_user_flash'] = ['error','Sesi tidak sah. Muat semula halaman.'];
    } else {
        $result = transition_user_status($userId, $target, (int) $_SESSION['admin_user_id'], (string) ($_POST['admin_notes'] ?? ''));
        $_SESSION['admin_user_flash'] = [$result['success']?'success':'error',$result['message']];
    }
    header('Location: users.php');
    exit;
}

$status = trim((string) ($_GET['status'] ?? ''));
$query = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = search_users($status, $query, $page);
$summary = user_summary_counts();
$flash = $_SESSION['admin_user_flash'] ?? null;
unset($_SESSION['admin_user_flash']);
$statusLabels = [''=>'Semua Status','pending'=>'Menunggu Kelulusan','active'=>'Aktif','suspended'=>'Digantung'];
function users_url(string $status, string $query, int $page=1): string {
    return 'users.php?' . http_build_query(array_filter(['status'=>$status,'q'=>$query,'page'=>$page], static fn($v)=>$v!==''&&$v!==1));
}
?>
<!DOCTYPE html><html lang="ms"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow"><title>Pengguna | HubInovasi Admin</title><link rel="icon" type="image/png" sizes="64x64" href="../assets/images/branding/favicon-64.png"><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/branding.css"></head>
<body class="admin-body">
<header class="admin-header"><div class="container"><div class="admin-header__identity"><a class="portal-brand" href="../index.php"><img src="../assets/images/branding/hubinovasi-kvks-primary.png" alt="HubInovasi KVKS — Platform Inovasi Pelajar"><span class="portal-brand__context">Admin</span></a><div><h1>Pengurusan Pengguna</h1><p>Luluskan akaun pelajar dan pensyarah sebelum mereka menghantar projek.</p></div></div><div class="admin-top-actions"><a href="index.php">Submission</a><a href="users.php" aria-current="page">Pengguna</a><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="admin-logout" type="submit">Log keluar</button></form></div></div></header>
<main class="admin-main"><div class="container">
    <?php if($flash): ?><div class="form-message <?= $flash[0]==='error'?'form-message--error':'' ?>" role="status"><?= e($flash[1]) ?></div><?php endif; ?>
    <section class="admin-summary-grid admin-summary-grid--users" aria-label="Ringkasan pengguna">
        <?php foreach ([['all','Semua Pengguna'],['pending','Menunggu Kelulusan'],['active','Aktif'],['suspended','Digantung']] as [$key,$label]): ?>
        <a href="<?= e(users_url($key==='all'?'':$key,'')) ?>" class="admin-summary-card <?= $status===($key==='all'?'':$key)?'is-active':'' ?>"><strong><?= $summary[$key] ?></strong><span><?= e($label) ?></span></a>
        <?php endforeach; ?>
    </section>
    <form method="get" class="admin-toolbar" role="search"><label class="form-field"><span>Cari pengguna</span><input type="search" name="q" value="<?= e($query) ?>" placeholder="Nama, email, program atau institusi"></label><label class="form-field"><span>Status</span><select name="status"><?php foreach($statusLabels as $value=>$label): ?><option value="<?= e($value) ?>" <?= $status===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button class="button button--primary" type="submit">Tapis</button><?php if($query!==''||$status!==''): ?><a class="button button--secondary" href="users.php">Reset</a><?php endif; ?></form>
    <div class="admin-list-heading"><div><p class="eyebrow">Account Approval</p><h2><?= $result['total'] ?> pengguna ditemui</h2></div><span>Terbaharu dahulu</span></div>
    <?php if(!$result['items']): ?><div class="admin-empty"><h2>Tiada pengguna ditemui.</h2><p>Pendaftaran baharu akan muncul di sini.</p></div><?php endif; ?>
    <div class="admin-user-list">
    <?php foreach($result['items'] as $item): ?>
        <article class="admin-user-row">
            <div class="admin-user-identity"><?php if(!empty($item['avatar_url'])): ?><img src="<?= e($item['avatar_url']) ?>" alt="" referrerpolicy="no-referrer"><?php endif; ?><div><span class="account-status account-status--<?= e($item['account_status']) ?>"><?= e(user_status_label($item['account_status'])) ?></span><span class="auth-provider-badge auth-provider-badge--<?= e($item['auth_provider'] ?? 'password') ?>"><?= e(($item['auth_provider'] ?? 'password') === 'google' ? 'Google' : (($item['auth_provider'] ?? 'password') === 'both' ? 'Google + Password' : 'Password')) ?></span><h2><?= e($item['full_name']) ?></h2><p><?= e($item['email']) ?></p></div></div>
            <dl><div><dt>Peranan</dt><dd><?= e(user_role_label($item['role'])) ?></dd></div><div><dt>Program / Jawatan</dt><dd><?= e($item['programme_or_position'] ?: '—') ?></dd></div><div><dt>Institusi</dt><dd><?= e($item['institution'] ?: '—') ?></dd></div><div><dt>Daftar</dt><dd><?= e(date('d/m/Y H:i',strtotime($item['created_at']))) ?></dd></div></dl>
            <div class="admin-user-actions">
            <?php if(in_array($item['account_status'],['pending','suspended'],true)): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="user_id" value="<?= (int)$item['id'] ?>"><input type="hidden" name="target_status" value="active"><button class="button button--primary" type="submit"><?= $item['account_status']==='pending'?'Luluskan Akaun':'Aktifkan Semula' ?></button></form><?php endif; ?>
            <?php if(in_array($item['account_status'],['pending','active'],true)): ?><details><summary><?= $item['account_status']==='pending'?'Tolak / Gantung':'Gantung Akaun' ?></summary><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="user_id" value="<?= (int)$item['id'] ?>"><input type="hidden" name="target_status" value="suspended"><label class="form-field"><span>Nota admin</span><textarea name="admin_notes" rows="2"></textarea></label><button class="button button--secondary" type="submit">Sahkan</button></form></details><?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
    </div>
    <?php if($result['pages']>1): ?><nav class="admin-pagination" aria-label="Pagination pengguna"><?php for($i=1;$i<=$result['pages'];$i++): ?><a href="<?= e(users_url($status,$query,$i)) ?>" <?= $i===$result['page']?'aria-current="page"':'' ?>><?= $i ?></a><?php endfor; ?></nav><?php endif; ?>
</div></main></body></html>
