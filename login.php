<?php
require_once __DIR__ . '/includes/google-auth.php';
if (user_is_authenticated()) { header('Location: dashboard/index.php'); exit; }

$error = null;
$flash = pull_google_auth_flash();
$next = (string) ($_GET['next'] ?? $_POST['next'] ?? '');
if ($next !== '' && str_starts_with($next, '/') && !str_starts_with($next, '//')) $_SESSION['google_next'] = $next;
else unset($_SESSION['google_next']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = normalize_user_email((string) ($_POST['email'] ?? ''));
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = tr('Sesi log masuk tidak sah. Sila cuba lagi.', 'The sign-in session is invalid. Please try again.');
    } else {
        $result = authenticate_user($email, (string) ($_POST['password'] ?? ''));
        if ($result['success'] && $result['user']) {
            establish_user_session($result['user']);
            header('Location: ' . safe_user_redirect($next));
            exit;
        }
        $error = match ($result['code']) {
            'pending' => tr('Akaun anda masih menunggu kelulusan admin.', 'Your account is still awaiting administrator approval.'),
            'suspended' => tr('Akaun ini telah digantung. Hubungi admin HubInovasi.', 'This account has been suspended. Contact the HubInovasi administrator.'),
            'rate_limited' => tr('Terlalu banyak percubaan. Cuba semula selepas 15 minit.', 'Too many attempts. Try again in 15 minutes.'),
            default => tr('Email atau kata laluan tidak sah.', 'Invalid email address or password.'),
        };
    }
}
$pageTitle = tr('Log Masuk', 'Sign In');
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="auth-shell auth-shell--login container">
        <div class="auth-intro"><p class="eyebrow">My HubInovasi</p><h1><?= e(tr('Sambung projek anda.', 'Continue your project.')) ?></h1><p><?= e(tr('Urus draf, semak komen admin dan ikuti perjalanan projek sehingga diterbitkan sebagai pitch inovasi.', 'Manage drafts, review administrator comments and follow the project journey until it is published as an innovation pitch.')) ?></p></div>
        <div class="auth-card">
            <p class="eyebrow"><?= e(tr('Akaun Pelajar / Pensyarah', 'Student / Lecturer Account')) ?></p><h2><?= e(tr('Log masuk', 'Sign in')) ?></h2>
            <?php if ($flash): ?><div class="form-message <?= ($flash[0] ?? '')==='error'?'form-message--error':'' ?>" role="status"><?= e($flash[1] ?? '') ?></div><?php endif; ?>
            <?php if ($error): ?><div class="form-message form-message--error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <div class="google-signin-block">
                <script src="https://accounts.google.com/gsi/client?hl=<?= e(app_language()) ?>" async></script>
                <div id="g_id_onload" data-client_id="<?= e(google_client_id()) ?>" data-login_uri="<?= e(google_login_uri()) ?>" data-ux_mode="redirect" data-auto_prompt="false"></div>
                <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="continue_with" data-shape="rectangular" data-logo_alignment="left" data-width="320"></div>
                <p><?= e(tr('Pengguna baharu akan memilih peranan dan menunggu kelulusan admin.', 'New users will select a role and await administrator approval.')) ?></p>
            </div>
            <div class="auth-divider"><span><?= e(tr('atau guna kata laluan', 'or use a password')) ?></span></div>
            <form method="post" class="password-login-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="next" value="<?= e($next) ?>">
                <label class="form-field"><span>Email</span><input type="email" name="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>"></label>
                <label class="form-field"><span><?= e(tr('Kata laluan', 'Password')) ?></span><input type="password" name="password" required autocomplete="current-password"></label>
                <button class="button button--primary" type="submit"><?= e(tr('Log Masuk', 'Sign In')) ?></button>
            </form>
            <p class="auth-card__foot"><?= tr('Belum mempunyai akaun? Anda boleh teruskan dengan Google atau <a href="register.php">daftar menggunakan kata laluan</a>.', 'Do not have an account? Continue with Google or <a href="register.php">register with a password</a>.') ?></p>
            <p class="auth-card__admin"><a href="admin/login.php"><?= e(tr('Log masuk admin', 'Administrator sign in')) ?> →</a></p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
