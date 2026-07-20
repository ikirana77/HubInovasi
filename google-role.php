<?php
require_once __DIR__ . '/includes/google-auth.php';
if (user_is_authenticated()) { header('Location: dashboard/index.php'); exit; }
$identity = pending_google_identity();
if (!$identity) {
    set_google_auth_flash('error', tr('Sesi pendaftaran Google telah tamat. Sila log masuk semula.', 'The Google registration session has expired. Please sign in again.'));
    header('Location: login.php'); exit;
}
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $message = ['type' => 'error', 'text' => tr('Sesi pendaftaran tidak sah. Muat semula halaman.', 'The registration session is invalid. Reload the page.')];
    } else {
        $result = create_google_user_account($identity, (string) ($_POST['role'] ?? ''), (string) ($_POST['institution'] ?? ''), (string) ($_POST['programme_or_position'] ?? ''));
        if ($result['success']) {
            clear_pending_google_identity();
            set_google_auth_flash('success', $result['message']);
            header('Location: login.php'); exit;
        }
        $message = ['type' => 'error', 'text' => $result['message']];
    }
}
$pageTitle = tr('Lengkapkan Pendaftaran Google', 'Complete Google Registration');
$activePage = 'register';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="auth-shell auth-shell--login container">
        <div class="auth-intro"><p class="eyebrow"><?= e(tr('Akaun Google Disambungkan', 'Google Account Connected')) ?></p><h1><?= e(tr('Satu langkah lagi.', 'One more step.')) ?></h1><p><?= e(tr('Pilih peranan anda. Akaun baharu tetap perlu diluluskan oleh admin sebelum projek boleh dihantar.', 'Select your role. New accounts still require administrator approval before a project can be submitted.')) ?></p></div>
        <form method="post" class="auth-card">
            <p class="eyebrow"><?= e(tr('Profil Google', 'Google Profile')) ?></p>
            <div class="google-profile-preview"><?php if (!empty($identity['avatar_url'])): ?><img src="<?= e($identity['avatar_url']) ?>" alt="" referrerpolicy="no-referrer"><?php endif; ?><div><h2><?= e($identity['full_name']) ?></h2><p><?= e($identity['email']) ?></p></div></div>
            <?php if ($message): ?><div class="form-message form-message--error" role="alert"><?= e($message['text']) ?></div><?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label class="form-field"><span><?= e(tr('Peranan', 'Role')) ?> <em>*</em></span><select name="role" required><option value=""><?= e(tr('Pilih peranan', 'Select a role')) ?></option><option value="student" <?= ($_POST['role']??'')==='student'?'selected':'' ?>><?= e(tr('Pelajar', 'Student')) ?></option><option value="lecturer" <?= ($_POST['role']??'')==='lecturer'?'selected':'' ?>><?= e(tr('Pensyarah', 'Lecturer')) ?></option></select></label>
            <label class="form-field"><span><?= e(tr('Institusi', 'Institution')) ?></span><input type="text" name="institution" maxlength="255" value="<?= e($_POST['institution'] ?? 'Kolej Vokasional Kuala Selangor') ?>"></label>
            <label class="form-field"><span><?= e(tr('Program / jawatan', 'Programme / position')) ?></span><input type="text" name="programme_or_position" maxlength="180" value="<?= e($_POST['programme_or_position'] ?? '') ?>" placeholder="<?= e(tr('Contoh: 2 DVM KPD / Pensyarah Teknologi Maklumat', 'Example: 2 DVM KPD / Information Technology Lecturer')) ?>"></label>
            <button class="button button--primary" type="submit"><?= e(tr('Hantar untuk Kelulusan', 'Submit for Approval')) ?></button>
            <p class="auth-card__foot"><a href="login.php">← <?= e(tr('Kembali ke log masuk', 'Back to sign in')) ?></a></p>
        </form>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
