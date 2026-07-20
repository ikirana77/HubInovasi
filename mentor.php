<?php
require_once __DIR__.'/includes/bootstrap.php';
require_once __DIR__.'/includes/public-ui.php';

$pageTitle = tr('Mentor HubInovasi','HubInovasi Mentors');
$activePage = 'mentor';
enable_public_mockup('mockup-mentor');

$mentors = [
    [
        'slug' => 'intan-keristina',
        'name' => 'Pn. Intan Keristina',
        'role' => tr('Pensyarah IT','IT Lecturer'),
        'institution' => 'Kolej Vokasional Kuala Selangor',
        'image' => 'assets/images/home/mentor-intan-keristina.webp',
        'tagline' => tr('Mentor berpengalaman yang membimbing idea pelajar menjadi penyelesaian sebenar.','Experienced mentor guiding student ideas into real solutions.'),
        'fields' => ['Web Development','Mobile Apps','AI & Computer Vision','UI/UX','Pitch'],
        'projects' => ['HERS','Durian Radar','CAReS','SPARK'],
        'stats' => ['48+', tr('Projek Diselia','Projects Supervised')],
        'featured' => true,
    ],
];

$featured = array_values(array_filter($mentors, fn($m) => !empty($m['featured'])));
$others = array_values(array_filter($mentors, fn($m) => empty($m['featured'])));

require __DIR__.'/includes/header.php';
?>
<main id="main-content" class="pm-shell mentor-directory"><div class="container">
    <nav class="pm-breadcrumbs">
        <a href="index.php"><?= e(tr('Utama','Home')) ?></a>
        <span>›</span>
        <strong><?= e(tr('Mentor','Mentors')) ?></strong>
    </nav>

    <section class="mentor-directory-hero">
        <div>
            <span class="pm-kicker"><?= e(tr('MENTOR HUBINOVASI','HUBINOVASI MENTORS')) ?></span>
            <h1 class="pm-display"><?= tr('Barisan mentor yang membimbing idea pelajar menjadi <em>projek berdampak.</em>','Meet the mentors guiding student ideas into <em>impactful projects.</em>') ?></h1>
            <p class="pm-lead"><?= e(tr('Teroka mentor mengikut bidang kepakaran, projek seliaan dan peranan mereka dalam membina inovator pelajar KVKS.','Explore mentors by expertise, supervised projects and their role in developing KVKS student innovators.')) ?></p>
        </div>
        <aside class="mentor-directory-stats pm-card">
            <div><strong><?= count($mentors) ?></strong><span><?= e(tr('Mentor Dipaparkan','Mentors Showcased')) ?></span></div>
            <div><strong>8+</strong><span><?= e(tr('Bidang Bimbingan','Mentoring Areas')) ?></span></div>
            <div><strong>70+</strong><span><?= e(tr('Projek Diselia','Projects Supervised')) ?></span></div>
        </aside>
    </section>

    <section class="mentor-filter pm-card" aria-label="<?= e(tr('Carian mentor','Mentor search')) ?>">
        <input type="search" placeholder="<?= e(tr('Cari nama mentor atau bidang kepakaran...','Search mentor name or expertise...')) ?>">
        <div>
            <?php foreach(['Web Development','Mobile Apps','AI','UI/UX','Database','Pitch'] as $field): ?>
                <span><?= e($field) ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="mentor-featured">
        <div class="pm-section-title"><h2><?= e(tr('Mentor Pilihan','Featured Mentor')) ?></h2></div>
        <?php foreach($featured as $m): ?>
            <article class="mentor-featured-card pm-card">
                <img src="<?= e($m['image']) ?>" alt="<?= e($m['name']) ?>">
                <div>
                    <span class="pm-kicker"><?= e(tr('MENTOR UTAMA','FEATURED MENTOR')) ?></span>
                    <h2><?= e($m['name']) ?></h2>
                    <p><strong><?= e($m['role']) ?></strong> · <?= e($m['institution']) ?></p>
                    <p><?= e($m['tagline']) ?></p>
                    <div class="skill-chips"><?php foreach($m['fields'] as $field): ?><span><?= e($field) ?></span><?php endforeach; ?></div>
                    <div class="mentor-project-strip"><?php foreach($m['projects'] as $project): ?><b><?= e($project) ?></b><?php endforeach; ?></div>
                    <a class="pm-btn primary" href="mentor-detail.php?slug=<?= e($m['slug']) ?>"><?= e(tr('Lihat Profil','View Profile')) ?> ↗</a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="mentor-grid-section">
        <div class="pm-section-title"><h2><?= e(tr('Semua Mentor','All Mentors')) ?></h2></div>
        <div class="mentor-directory-grid">
            <?php foreach($others as $m): ?>
                <article class="mentor-card pm-card">
                    <img src="<?= e($m['image']) ?>" alt="<?= e($m['name']) ?>">
                    <div>
                        <h3><?= e($m['name']) ?></h3>
                        <p><strong><?= e($m['role']) ?></strong><br><?= e($m['institution']) ?></p>
                        <p><?= e($m['tagline']) ?></p>
                        <div class="skill-chips"><?php foreach(array_slice($m['fields'],0,3) as $field): ?><span><?= e($field) ?></span><?php endforeach; ?></div>
                        <div class="mentor-card__bottom">
                            <span><strong><?= e($m['stats'][0]) ?></strong> <?= e($m['stats'][1]) ?></span>
                            <a href="mentor-detail.php?slug=<?= e($m['slug']) ?>"><?= e(tr('Profil','Profile')) ?> →</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="pm-ribbon pm-card">
        <div>
            <h2><?= tr('Ingin membimbing projek inovasi pelajar?','Want to mentor student innovation projects?') ?></h2>
            <p><?= e(tr('Hubungi pasukan HubInovasi untuk cadangan mentor, bidang kepakaran atau sokongan pertandingan.','Contact the HubInovasi team for mentor suggestions, expertise areas or competition support.')) ?></p>
        </div>
        <a class="pm-btn primary" href="about.php#contact"><?= e(tr('Hubungi Admin','Contact Admin')) ?> ↗</a>
        <a class="pm-btn" href="explore.php"><?= e(tr('Lihat Projek','View Projects')) ?> →</a>
    </section>
</div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
