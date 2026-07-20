<?php
require_once __DIR__ . '/includes/google-auth.php';
if (user_is_authenticated()) { header('Location: dashboard/index.php'); exit; }
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $message = ['type' => 'error', 'text' => tr('Sesi pendaftaran tidak sah. Sila muat semula halaman.', 'The registration session is invalid. Reload the page.')];
    } else {
        $result = register_user_account($_POST);
        $message = ['type' => $result['success'] ? 'success' : 'error', 'text' => $result['message']];
        if ($result['success']) $_POST = [];
    }
}
$pageTitle = tr('Daftar Akaun', 'Create Account');
$activePage = 'register';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="auth-shell container">
        <div class="auth-intro"><p class="eyebrow"><?= e(tr('Akaun Penyumbang', 'Contributor Account')) ?></p><h1><?= e(tr('Daftar untuk membina pitch projek.', 'Register to build your project pitch.')) ?></h1><p><?= e(tr('Pelajar dan pensyarah boleh menyimpan draf, menghantar projek untuk semakan, menerima komen pembetulan dan melihat status penerbitan.', 'Students and lecturers can save drafts, submit projects for review, receive revision comments and track publication status.')) ?></p><div class="auth-flow"><span><?= e(tr('Daftar', 'Register')) ?></span><span><?= e(tr('Admin luluskan', 'Admin approves')) ?></span><span><?= e(tr('Hantar projek', 'Submit project')) ?></span><span><?= e(tr('Terbit', 'Publish')) ?></span></div></div>
        <form method="post" class="auth-card">
            <p class="eyebrow"><?= e(tr('Pendaftaran Baharu', 'New Registration')) ?></p><h2><?= e(tr('Cipta akaun HubInovasi', 'Create a HubInovasi account')) ?></h2>
            <?php if ($message): ?><div class="form-message <?= $message['type']==='error'?'form-message--error':'' ?>" role="status"><?= e($message['text']) ?></div><?php endif; ?>
            <div class="google-signin-block"><script src="https://accounts.google.com/gsi/client?hl=<?= e(app_language()) ?>" async></script><div id="g_id_onload" data-client_id="<?= e(google_client_id()) ?>" data-login_uri="<?= e(google_login_uri()) ?>" data-ux_mode="redirect" data-auto_prompt="false"></div><div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="signup_with" data-shape="rectangular" data-logo_alignment="left" data-width="320"></div></div>
            <div class="auth-divider"><span><?= e(tr('atau daftar dengan kata laluan', 'or register with a password')) ?></span></div>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label class="form-field"><span><?= e(tr('Nama penuh', 'Full name')) ?> <em>*</em></span><input type="text" name="full_name" required maxlength="160" autocomplete="name" value="<?= e($_POST['full_name'] ?? '') ?>"></label>
            <label class="form-field"><span>Email <em>*</em></span><input type="email" name="email" required maxlength="255" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></label>
            <div class="form-row">
                <label class="form-field"><span><?= e(tr('Peranan', 'Role')) ?> <em>*</em></span><select name="role" required><option value=""><?= e(tr('Pilih peranan', 'Select a role')) ?></option><option value="student" <?= ($_POST['role']??'')==='student'?'selected':'' ?>><?= e(tr('Pelajar', 'Student')) ?></option><option value="lecturer" <?= ($_POST['role']??'')==='lecturer'?'selected':'' ?>><?= e(tr('Pensyarah', 'Lecturer')) ?></option></select></label>
                <label class="form-field"><span><?= e(tr('Institusi', 'Institution')) ?></span><input type="text" name="institution" maxlength="255" value="<?= e($_POST['institution'] ?? 'Kolej Vokasional Kuala Selangor') ?>"></label>
            </div>
            <label class="form-field"><span><?= e(tr('Program / jawatan', 'Programme / position')) ?></span><input type="text" name="programme_or_position" maxlength="180" value="<?= e($_POST['programme_or_position'] ?? '') ?>" placeholder="<?= e(tr('Contoh: 2 DVM KPD / Pensyarah Teknologi Maklumat', 'Example: 2 DVM KPD / Information Technology Lecturer')) ?>"></label>
            <div class="form-row"><label class="form-field"><span><?= e(tr('Kata laluan', 'Password')) ?> <em>*</em></span><input type="password" name="password" required minlength="10" autocomplete="new-password"></label><label class="form-field"><span><?= e(tr('Sahkan kata laluan', 'Confirm password')) ?> <em>*</em></span><input type="password" name="password_confirmation" required minlength="10" autocomplete="new-password"></label></div>
            <button class="button button--primary" type="submit"><?= e(tr('Daftar Akaun', 'Create Account')) ?></button>
            <p class="auth-card__foot"><?= tr('Sudah berdaftar? <a href="login.php">Log masuk di sini</a>.', 'Already registered? <a href="login.php">Sign in here</a>.') ?></p>
        </form>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
