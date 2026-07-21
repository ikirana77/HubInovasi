<?php
require_once __DIR__ . '/user-auth.php';
/**
 * Pengepala global HubInovasi.
 * Tetapkan $pageTitle dan $activePage sebelum memanggil fail ini.
 */
$pageTitle = $pageTitle ?? tr('Utama', 'Home');
$activePage = $activePage ?? '';
$homeLaunchpad = $homeLaunchpad ?? false;
$publicMockup = $publicMockup ?? false;
$bodyClass = trim((string) ($bodyClass ?? ''));
$extraStylesheets = is_array($extraStylesheets ?? null) ? $extraStylesheets : [];

$navigation = [];

if ($publicMockup) {
    $navigation = [
        'home' => ['label' => t('nav.home'), 'url' => 'index.php'],
        'explore' => ['label' => t('nav.explore'), 'url' => 'explore.php'],
        'areas' => ['label' => t('nav.areas'), 'url' => 'solution-areas.php'],
        'impact' => ['label' => t('nav.impact'), 'url' => 'competitions-impact.php'],
        'innovators' => ['label' => t('nav.innovators'), 'url' => 'innovator.php'],
        'mentors' => ['label' => t('nav.mentors'), 'url' => 'mentor.php'],
        'about' => ['label' => t('nav.about'), 'url' => 'about.php'],
        'submit' => ['label' => t('nav.submit'), 'url' => 'submit-project.php'],
    ];
} elseif ($homeLaunchpad) {
    $navigation = [
        'explore' => ['label' => t('nav.explore'), 'url' => 'explore.php'],
        'areas' => ['label' => t('nav.areas'), 'url' => 'solution-areas.php'],
        'impact' => ['label' => t('nav.impact'), 'url' => 'competitions-impact.php'],
    ];
    unset($navigation['home']);
    $navigation += [
        'innovators' => ['label' => t('nav.innovators'), 'url' => 'innovator.php'],
        'mentors' => ['label' => t('nav.mentors'), 'url' => 'mentor.php'],
        'about' => ['label' => t('nav.about'), 'url' => 'about.php'],
        'submit' => ['label' => t('nav.submit'), 'url' => 'submit-project.php'],
    ];
} else {
    $navigation = [
        'home' => ['label' => t('nav.home'), 'url' => 'index.php'],
        'explore' => ['label' => t('nav.explore'), 'url' => 'explore.php'],
        'areas' => ['label' => t('nav.areas'), 'url' => 'solution-areas.php'],
        'impact' => ['label' => t('nav.impact'), 'url' => 'competitions-impact.php'],
    ];
    $navigation += [
        'about' => ['label' => t('nav.about'), 'url' => 'about.php'],
        'submit' => ['label' => t('nav.submit'), 'url' => 'submit-project.php'],
    ];
    if (user_is_authenticated()) {
        $navigation['dashboard'] = ['label' => t('nav.dashboard'), 'url' => 'dashboard/index.php'];
    } else {
        $navigation['login'] = ['label' => t('nav.login'), 'url' => 'login.php'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e(app_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(t('meta.description')) ?>">
    <meta name="theme-color" content="#fffdf9">
    <title><?= e($pageTitle) ?> | HubInovasi KVKS</title>
    <link rel="icon" type="image/png" sizes="64x64" href="assets/images/branding/favicon-64.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php foreach ($extraStylesheets as $stylesheet): ?>
        <link rel="stylesheet" href="<?= e((string) $stylesheet) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="assets/css/branding.css?v=20260721-menu-scroll">
    <script src="assets/js/main.js" defer></script>
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>
      data-nav-open-label="<?= e(t('common.open_menu')) ?>"
      data-nav-close-label="<?= e(t('common.close_menu')) ?>">
    <a class="skip-link" href="#main-content"><?= e(t('common.skip')) ?></a>
    <header class="site-header<?= ($homeLaunchpad || $publicMockup) ? ' site-header--launchpad' : '' ?><?= $publicMockup ? ' site-header--public-mockup' : '' ?>">
        <div class="container site-header__inner">
            <a class="brand brand--image" href="index.php" aria-label="HubInovasi KVKS — <?= e(tr('halaman utama', 'home page')) ?>">
                <picture>                    <img class="brand__logo" src="assets/images/branding/hubinovasi-kvks-primary.png" alt="HubInovasi KVKS — <?= e(tr('Platform Inovasi Pelajar', 'Student Innovation Platform')) ?>">
                </picture>
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
                <span class="sr-only"><?= e(t('common.open_menu')) ?></span>
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
            </button>

            <nav class="primary-nav" id="primary-navigation" aria-label="<?= e(t('common.main_navigation')) ?>">
                <ul>
                    <?php foreach ($navigation as $key => $item): ?>
                        <?php if ($key === 'submit'): ?>
                            <?php if ($publicMockup): ?><li class="nav-search-item"><a href="explore.php" aria-label="<?= e(tr('Cari projek', 'Search projects')) ?>">⌕</a></li><?php endif; ?>
                            <li class="language-switcher-item">
                                <div class="language-switcher" role="group" aria-label="<?= e(t('common.language_selection')) ?>">
                                    <a class="language-switcher__option<?= app_language() === 'ms' ? ' is-active' : '' ?>"
                                       href="<?= e(language_switch_url('ms')) ?>"
                                       lang="ms" hreflang="ms"
                                       <?= app_language() === 'ms' ? 'aria-current="true"' : '' ?>
                                       title="<?= e(t('common.malay')) ?>">BM</a>
                                    <span aria-hidden="true">/</span>
                                    <a class="language-switcher__option<?= app_language() === 'en' ? ' is-active' : '' ?>"
                                       href="<?= e(language_switch_url('en')) ?>"
                                       lang="en" hreflang="en"
                                       <?= app_language() === 'en' ? 'aria-current="true"' : '' ?>
                                       title="<?= e(t('common.english')) ?>">EN</a>
                                </div>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?= e($item['url']) ?>"
                               <?= $activePage === $key ? 'aria-current="page"' : '' ?>
                               class="<?= $key === 'submit' ? 'nav-cta' : '' ?>">
                                <?= e($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
