<?php
require_once __DIR__ . '/user-auth.php';
/**
 * Pengepala global HubInovasi.
 * Tetapkan $pageTitle dan $activePage sebelum memanggil fail ini.
 */
$pageTitle = $pageTitle ?? 'Utama';
$activePage = $activePage ?? '';
$homeLaunchpad = $homeLaunchpad ?? false;
$bodyClass = trim((string) ($bodyClass ?? ''));
$extraStylesheets = is_array($extraStylesheets ?? null) ? $extraStylesheets : [];

if ($homeLaunchpad) {
    $navigation = [
        'explore' => ['label' => 'Discover Solutions', 'url' => 'explore.php'],
        'areas' => ['label' => 'Solution Areas', 'url' => 'solution-areas.php'],
        'impact' => ['label' => 'Competitions & Impact', 'url' => 'competitions-impact.php'],
        'innovators' => ['label' => 'Innovators', 'url' => 'innovator.php'],
        'mentors' => ['label' => 'Mentors', 'url' => 'mentor.php'],
        'about' => ['label' => 'About', 'url' => 'about.php'],
        'submit' => ['label' => 'Submit Project', 'url' => 'submit-project.php'],
    ];
} else {
    $navigation = [
        'home' => ['label' => 'Utama', 'url' => 'index.php'],
        'explore' => ['label' => 'Teroka', 'url' => 'explore.php'],
        'areas' => ['label' => 'Bidang', 'url' => 'solution-areas.php'],
        'impact' => ['label' => 'Pertandingan & Impak', 'url' => 'competitions-impact.php'],
        'about' => ['label' => 'Tentang Kami', 'url' => 'about.php'],
        'submit' => ['label' => 'Hantar Projek', 'url' => 'submit-project.php'],
    ];
    if (user_is_authenticated()) {
        $navigation['dashboard'] = ['label' => 'Dashboard Saya', 'url' => 'dashboard/index.php'];
    } else {
        $navigation['login'] = ['label' => 'Log Masuk', 'url' => 'login.php'];
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HubInovasi KVKS — platform pelancaran idea dan inovasi pelajar.">
    <meta name="theme-color" content="#fffdf9">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | HubInovasi KVKS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php foreach ($extraStylesheets as $stylesheet): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars((string) $stylesheet, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <script src="assets/js/main.js" defer></script>
</head>
<body<?= $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
    <a class="skip-link" href="#main-content">Langkau ke kandungan utama</a>
    <header class="site-header<?= $homeLaunchpad ? ' site-header--launchpad' : '' ?>">
        <div class="container site-header__inner">
            <a class="brand" href="index.php" aria-label="HubInovasi KVKS — halaman utama">
                <?php if (!$homeLaunchpad): ?>
                    <span class="brand__mark" aria-hidden="true">H<span>+</span></span>
                <?php endif; ?>
                <span class="brand__name"><span>HubInovasi</span><small>KVKS</small></span>
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
                <span class="sr-only">Buka menu navigasi</span>
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
            </button>

            <nav class="primary-nav" id="primary-navigation" aria-label="Navigasi utama">
                <ul>
                    <?php foreach ($navigation as $key => $item): ?>
                        <li>
                            <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>"
                               <?= $activePage === $key ? 'aria-current="page"' : '' ?>
                               class="<?= $key === 'submit' ? 'nav-cta' : '' ?>">
                                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
