<?php
/**
 * HubInovasi KVKS — bilingual launchpad homepage aligned to the approved mockup.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/taxonomy.php';
$pageTitle = tr('Platform Pelancaran Inovasi', 'Innovation Launchpad');
$activePage = 'home';
$homeLaunchpad = true;
$bodyClass = 'home-launchpad-page';
$extraStylesheets = ['assets/css/home-launchpad.css'];

$publicProjectSlugs = [];
try {
    require __DIR__ . '/data/projects.php';
    foreach ($projects as $project) {
        if (!empty($project['slug'])) $publicProjectSlugs[] = (string) $project['slug'];
    }
} catch (Throwable $exception) {
    error_log('Homepage project lookup skipped: ' . $exception->getMessage());
}

function launchpad_project_url(string $slug, array $publicProjectSlugs): string
{
    return in_array($slug, $publicProjectSlugs, true)
        ? 'project.php?slug=' . rawurlencode($slug)
        : 'explore.php';
}

function launchpad_icon(string $name): string
{
    $icons = [
        'rocket' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 5.3c2.6-2.6 5.6-2.2 5.6-2.2s.4 3-2.2 5.6l-4.6 4.6-2.8-.6-.6-2.8 4.6-4.6Z"/><path d="m10.1 9.9-3.5.5-3.2 3.2 5.1.8M13.6 13.4l.5 3.5-3.2 3.2-.8-5.1"/><circle cx="16.7" cy="6.7" r="1.5"/><path d="M7.2 17.1c-1.8.2-3.2 1.6-3.4 3.4 1.8-.2 3.2-1.6 3.4-3.4Z"/></svg>',
        'people' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 20c.3-4 2.2-6 5.5-6s5.2 2 5.5 6M16 11c2.2 0 4 1.8 4 4v3M17 4.5a3 3 0 0 1 0 6"/></svg>',
        'award' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 4h8v5a4 4 0 0 1-8 0V4Z"/><path d="M8 6H4v2a4 4 0 0 0 4 4M16 6h4v2a4 4 0 0 1-4 4M12 13v5M8 21h8M9 18h6"/></svg>',
        'partner' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.2 8.5 2-2a3 3 0 0 1 4.2 0l2.1 2.1a3 3 0 0 1 0 4.2l-1.8 1.8M14.8 15.5l-2 2a3 3 0 0 1-4.2 0l-2.1-2.1a3 3 0 0 1 0-4.2l1.8-1.8M9.5 14.5l5-5"/></svg>',
        'education' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5.5c3-1 6-.8 9 1.2v12c-3-2-6-2.2-9-1.2v-12ZM21 5.5c-3-1-6-.8-9 1.2v12c3-2 6-2.2 9-1.2v-12Z"/></svg>',
        'community' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="8" cy="7" r="3"/><circle cx="17" cy="8" r="2.5"/><path d="M2.5 20c.3-4 2.2-6.2 5.5-6.2s5.2 2.2 5.5 6.2M13 14.5c1-.9 2.3-1.4 4-1.4 2.8 0 4.3 1.8 4.5 5"/></svg>',
        'health' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21S4 16.5 4 9.7C4 6.8 5.8 5 8.4 5c1.5 0 2.8.8 3.6 2 .8-1.2 2.1-2 3.6-2C18.2 5 20 6.8 20 9.7 20 16.5 12 21 12 21Z"/><path d="M8 12h2l1-2.5 2 5 1-2.5h2"/></svg>',
        'sustainability' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4C11 4 5 8.3 5 15c0 2.8 1.8 5 4.7 5C16.4 20 20 12.8 20 4Z"/><path d="M4 21c3.5-6 7.5-9.5 12-12"/></svg>',
        'campus' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10h18L12 4 3 10ZM5 10v8M9 10v8M15 10v8M19 10v8M3 20h18"/></svg>',
        'life' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8.5 10h.01M15.5 10h.01M8.5 14.5c2 2 5 2 7 0"/></svg>',
    ];
    $aliases = ['code'=>'campus','chart'=>'award','home'=>'campus','leaf'=>'sustainability','book'=>'education','shield'=>'campus'];
    return $icons[$name] ?? $icons[$aliases[$name] ?? ''] ?? '';
}

$stats = [
    ['icon' => 'rocket', 'value' => (string) count($publicProjectSlugs), 'label' => tr('Projek Disahkan', 'Verified Projects'), 'description' => tr('Hanya projek yang disemak dan diterbitkan', 'Only reviewed and published projects')],
    ['icon' => 'people', 'value' => '7', 'label' => tr('Program KVKS', 'KVKS Programmes'), 'description' => tr('Kolaborasi inovasi merentas program', 'Cross-programme innovation collaboration')],
    ['icon' => 'award', 'value' => '8', 'label' => tr('Bidang Penyelesaian', 'Solution Areas'), 'description' => tr('Dikumpulkan mengikut masalah dunia sebenar', 'Grouped by real-world problems')],
    ['icon' => 'partner', 'value' => '1', 'label' => tr('Ekosistem Inovasi', 'Innovation Ecosystem'), 'description' => tr('Satu pentas untuk semua program', 'One stage for every programme')],
];

$trendingProjects = [
    ['slug' => 'hers', 'name' => 'HERS', 'description' => tr('Laluan pembelajaran diperibadikan untuk setiap pelajar.', 'Personalised learning pathways for every student.'), 'tags' => ['AI/ML', tr('Pendidikan', 'Education')], 'image' => 'project-hers.webp'],
    ['slug' => 'spark', 'name' => 'SPARK', 'description' => tr('Pembantu parkir pintar untuk pengalaman kampus yang lancar.', 'Smart parking assistant for a seamless campus experience.'), 'tags' => ['IoT', tr('Kampus Pintar', 'Smart Campus')], 'image' => 'project-spark.webp'],
    ['slug' => 'durian-radar', 'name' => 'Durian Radar', 'description' => tr('Cerapan AI untuk perancangan hasil durian yang lebih baik.', 'AI insights for better durian harvest planning.'), 'tags' => ['AI/ML', tr('Pertanian', 'Agriculture')], 'image' => 'project-durian-radar.webp'],
    ['slug' => 'cms-quest', 'name' => 'CMS Quest', 'description' => tr('Chatbot pintar untuk perkhidmatan kampus dan sokongan pelajar.', 'Smart chatbot for campus services and student support.'), 'tags' => ['NLP', 'Chatbot'], 'image' => 'project-cms-quest.webp'],
    ['slug' => 'ecotrack', 'name' => 'EcoTrack', 'description' => tr('Jejak, kurangkan dan laporkan jejak karbon kampus.', 'Track, reduce and report the campus carbon footprint.'), 'tags' => [tr('Kelestarian', 'Sustainability'), 'IoT'], 'image' => 'project-ecotrack.webp'],
    ['slug' => 'medbuddy', 'name' => 'MedBuddy', 'description' => tr('Rakan kesihatan untuk kesejahteraan pelajar.', 'A health companion for student well-being.'), 'tags' => [tr('Kesihatan', 'Health'), tr('Mudah Alih', 'Mobile')], 'image' => 'project-medbuddy.webp'],
];

$solutionAreas = hub_solution_areas();

$innovators = [
    ['name' => 'Muhammad Aidil Wafiy', 'role' => tr('UI/UX Designer & Student Developer', 'UI/UX Designer & Student Developer'), 'project' => 'SPARK', 'bio' => tr('Membangunkan pengalaman aplikasi SPARK melalui reka bentuk antaramuka, sistem data dan pembangunan aplikasi.', 'Developing the SPARK app experience through interface design, data systems and application development.'), 'image' => 'innovator-aidil-wafiy.webp'],
    ['name' => 'Anniq Darwisy', 'role' => tr('Student Developer & Website Designer', 'Student Developer & Website Designer'), 'project' => 'SPARK', 'bio' => tr('Menyumbang kepada reka bentuk website, fungsi sistem dan kerjasama pasukan SPARK.', 'Contributes to website design, system functions and SPARK team collaboration.'), 'image' => 'innovator-anniq-darwisy.webp'],
    ['name' => 'Khadijah Hanum', 'role' => tr('Multi-Project Innovator', 'Multi-Project Innovator'), 'project' => 'e-Competency KVKS · Durian Radar · HERS', 'bio' => tr('Menyumbang kepada pembangunan sistem, data dan aplikasi merentas beberapa projek inovasi.', 'Contributes to system, data and application development across several innovation projects.'), 'image' => 'innovator-khadijah-hanum.webp'],
    [
        'name' => 'Nazlah Nazifa',
        'full' => 'Nur Nazlah Nazifa binti Mohd Ridzuan',
        'role' => tr('Inovator Pelbagai Projek', 'Multi-Project Innovator'),
        'project' => 'e-Competency KVKS · Durian Radar · HERS',
        'image' => 'assets/images/home/innovator-nazlah-nazifa.webp',
        'url' => 'innovator-nazlah-nazifa.php',
        'bio' => tr(
            'Menyumbang kepada pembangunan sistem, data dan aplikasi merentas e-Competency KVKS, Durian Radar dan HERS.',
            'Contributes to systems, data and application development across e-Competency KVKS, Durian Radar and HERS.'
        ),
        'skills' => ['Dashboard', 'Data', 'Google Workspace'],
    ],
    [
        'name' => 'Nur Damia Atiqah',
        'full' => 'Nur Damia Atiqah binti Anwar Sadat',
        'role' => tr('Inovator CAReS', 'CAReS Innovator'),
        'project' => 'CAReS · IIE2025 Gold Award',
        'image' => 'assets/images/home/innovator-damia-atiqah.webp',
        'url' => 'innovator-damia-atiqah.php',
        'bio' => tr(
            'Ahli pasukan inovasi CAReS yang membantu membina sistem pelaporan aktiviti kolej yang lebih tersusun dan bersedia sebagai eviden pertandingan.',
            'A CAReS innovation team member helping build a clearer college activity reporting system for review and competition evidence.'
        ),
        'skills' => ['Pelaporan Aktiviti', 'Dokumentasi', 'UI/UX'],
    ],
];

require __DIR__ . '/includes/header.php';
?>

<main id="main-content" class="launchpad-home">
    <section class="launchpad-hero" aria-labelledby="launchpad-hero-title">
        <div class="container launchpad-hero__grid">
            <div class="launchpad-hero__copy">
                <p class="launchpad-kicker"><?= e(tr('Platform Inovasi Pelajar', 'Student Innovation Launchpad')) ?></p>
                <h1 id="launchpad-hero-title"><?= tr('Idea wajar mendapat<br><span>lebih daripada</span><br>sekadar folder penghantaran.', 'Ideas deserve<br><span>more than</span><br>a submission folder.') ?></h1>
                <p><?= e(tr('Platform cerah yang mengubah projek pelajar menjadi kisah inovasi dan idea produk dunia sebenar.', 'A bright launchpad where student projects become real-world innovation stories and product ideas.')) ?></p>
                <div class="launchpad-actions">
                    <a class="launchpad-button launchpad-button--primary" href="explore.php"><?= e(tr('Teroka Penyelesaian', 'Explore Solutions')) ?> <span aria-hidden="true">→</span></a>
                    <a class="launchpad-button launchpad-button--outline" href="submit-project.php"><?= e(tr('Hantar Projek', 'Submit a Project')) ?> <span aria-hidden="true">→</span></a>
                </div>
            </div>
            <div class="launchpad-hero__art">
                <img src="assets/images/home/hero-devices.webp" width="647" height="350" alt="<?= e(tr('Pratonton produk inovasi pelajar pada komputer riba, tablet dan telefon', 'Preview of student innovation products displayed across laptop, tablet and mobile devices')) ?>">
            </div>
        </div>
        <span class="launchpad-dots launchpad-dots--hero" aria-hidden="true"></span>
    </section>

    <section class="launchpad-stats" aria-label="<?= e(tr('Statistik impak HubInovasi', 'HubInovasi impact statistics')) ?>">
        <div class="container launchpad-stats__grid">
            <?php foreach ($stats as $stat): ?>
                <article class="launchpad-stat">
                    <span class="launchpad-stat__icon launchpad-stat__icon--<?= e($stat['icon']) ?>"><?= launchpad_icon($stat['icon']) ?></span>
                    <div><strong><?= e($stat['value']) ?></strong><h2><?= e($stat['label']) ?></h2><p><?= e($stat['description']) ?></p></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="launchpad-section launchpad-featured" aria-labelledby="featured-solution-title">
        <div class="container">
            <div class="launchpad-section__heading launchpad-section__heading--single"><h2 id="featured-solution-title"><?= e(tr('Penyelesaian Pilihan', 'Featured Solution')) ?></h2></div>
            <article class="launchpad-featured__card">
                <div class="launchpad-featured__image"><img src="assets/images/home/featured-durian-radar.webp" width="463" height="192" alt="<?= e(tr('Dashboard Durian Radar pada komputer riba dan telefon', 'Durian Radar dashboard displayed on a laptop and mobile phone')) ?>"></div>
                <div class="launchpad-featured__content">
                    <span class="launchpad-badge"><?= e(tr('Pilihan', 'Featured')) ?></span>
                    <h3>Durian Radar</h3>
                    <p><?= e(tr('Kecerdasan tanaman berkuasa AI yang membantu petani meramal masa tuaian, memantau kualiti dan meningkatkan hasil.', 'AI-powered crop intelligence that helps farmers predict harvest timing, monitor quality and increase yield.')) ?></p>
                    <ul class="launchpad-tags" aria-label="<?= e(tr('Tag teknologi', 'Technology tags')) ?>"><li>AI/ML</li><li>IoT</li><li><?= e(tr('Pertanian', 'Agriculture')) ?></li><li><?= e(tr('Analitik Data', 'Data Analytics')) ?></li></ul>
                    <a class="launchpad-button launchpad-button--primary launchpad-button--small" href="<?= e(launchpad_project_url('durian-radar', $publicProjectSlugs)) ?>"><?= e(tr('Lihat Pitch Projek', 'View Project Pitch')) ?> <span aria-hidden="true">→</span></a>
                </div>
            </article>
        </div>
    </section>

    <section class="launchpad-section launchpad-trending" aria-labelledby="trending-projects-title">
        <div class="container">
            <div class="launchpad-section__heading"><h2 id="trending-projects-title"><?= e(tr('Projek Popular', 'Trending Projects')) ?></h2><a href="explore.php"><?= e(tr('Lihat semua projek', 'View all projects')) ?> <span aria-hidden="true">→</span></a></div>
            <div class="launchpad-project-grid">
                <?php foreach ($trendingProjects as $project): ?>
                    <article class="launchpad-project-card">
                        <a class="launchpad-project-card__image" href="<?= e(launchpad_project_url($project['slug'], $publicProjectSlugs)) ?>" aria-label="<?= e(tr('Lihat ', 'View ') . $project['name']) ?>">
                            <img src="assets/images/home/<?= e($project['image']) ?>" width="145" height="87" alt="<?= e($project['name'] . ' ' . tr('pratonton projek', 'project preview')) ?>">
                        </a>
                        <div class="launchpad-project-card__body">
                            <h3><?= e($project['name']) ?></h3><p><?= e($project['description']) ?></p>
                            <div class="launchpad-project-card__footer"><ul class="launchpad-tags launchpad-tags--compact"><?php foreach ($project['tags'] as $tag): ?><li><?= e($tag) ?></li><?php endforeach; ?></ul><a class="launchpad-card-arrow" href="<?= e(launchpad_project_url($project['slug'], $publicProjectSlugs)) ?>" aria-label="<?= e(tr('Buka ', 'Open ') . $project['name']) ?>">›</a></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="launchpad-section launchpad-areas" aria-labelledby="solution-areas-title">
        <div class="container">
            <div class="launchpad-section__heading launchpad-section__heading--single"><h2 id="solution-areas-title"><?= e(tr('Bidang Penyelesaian', 'Solution Areas')) ?></h2></div>
            <div class="launchpad-area-grid"><?php foreach ($solutionAreas as $areaSlug => $area): ?><a class="launchpad-area-card" href="explore.php?area=<?= rawurlencode($areaSlug) ?>"><span><?= launchpad_icon($area['icon']) ?></span><h3><?= e($area['name']) ?></h3><p><?= e($area['description']) ?></p></a><?php endforeach; ?></div>
        </div>
    </section>

    <section class="launchpad-section launchpad-innovators" aria-labelledby="innovators-title">
        <div class="container">
            <div class="launchpad-section__heading"><h2 id="innovators-title"><?= e(tr('Kenali Inovator', 'Meet the Innovators')) ?></h2><a href="innovator.php"><?= e(tr('Kenali semua inovator', 'Meet all innovators')) ?> <span aria-hidden="true">→</span></a></div>
            <div class="launchpad-innovator-grid">
                <?php foreach ($innovators as $innovator): ?>
                    <article class="launchpad-innovator-card"><img src="assets/images/home/<?= e($innovator['image']) ?>" width="101" height="124" alt="<?= e(tr('Potret ', 'Portrait of ') . $innovator['name']) ?>"><div><h3><?= e($innovator['name']) ?></h3><strong><?= e($innovator['role']) ?></strong><span class="launchpad-badge"><?= e($innovator['project']) ?></span><p><?= e($innovator['bio']) ?></p><div class="launchpad-socials" aria-label="<?= e(tr('Pautan sosial', 'Social links')) ?>"><span aria-hidden="true">in</span><span aria-hidden="true">●</span></div></div></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="launchpad-festival" aria-labelledby="festival-title">
        <div class="container launchpad-festival__inner">
            <div class="launchpad-festival__brand" aria-hidden="true"><span><?= e(tr('Festival Inovasi', 'Innovation Fest')) ?></span><strong>2026</strong></div>
            <div class="launchpad-festival__copy"><h2 id="festival-title"><?= e(tr('Bersedia untuk membentangkan idea anda?', 'Ready to pitch your idea?')) ?></h2><p><?= tr('Sertai inovator, mentor dan pemimpin industri.<br>Pamerkan. Bekerjasama. Cipta impak.', 'Join innovators, mentors and industry leaders.<br>Showcase. Collaborate. Create impact.') ?></p></div>
            <a class="launchpad-button launchpad-button--light" href="competitions-impact.php"><?= e(tr('Sertai Pameran Seterusnya', 'Join the Next Showcase')) ?> <span aria-hidden="true">→</span></a>
            <span class="launchpad-dots launchpad-dots--festival" aria-hidden="true"></span>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
