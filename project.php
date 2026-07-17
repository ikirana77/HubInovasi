<?php
$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
require_once __DIR__ . '/includes/project-repository.php';
$project = $slug !== '' ? get_public_project_by_slug($slug) : null;

$pageTitle = $project ? $project['name'] : 'Projek Tidak Dijumpai';
$activePage = 'explore';
require __DIR__ . '/includes/header.php';

$projectVisible = $project && !empty($project['detail_available']);
$showComingSoon = $project && !$projectVisible;
?>
<main id="main-content" class="inner-page project-page">
    <?php if ($project && $projectVisible): ?>
        <section class="project-hero project-hero--hers" aria-labelledby="project-title">
            <div class="container project-hero__inner">
                <div class="project-hero__topbar">
                    <a class="project-back-link" href="explore.php">← Kembali ke Teroka Inovasi</a>
                    <span class="project-number"><?= htmlspecialchars($project['number'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="project-hero__content">
                    <div class="project-hero__copy">
                        <p class="eyebrow eyebrow--project">PROJEK PILIHAN</p>
                        <p class="project-category"><?= htmlspecialchars(strtoupper($project['category']), ENT_QUOTES, 'UTF-8') ?></p>
                        <h1 id="project-title"><?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                        <p class="project-full-title"><?= htmlspecialchars($project['full_title'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="project-tagline">“<?= htmlspecialchars($project['tagline'], ENT_QUOTES, 'UTF-8') ?>”</p>
                        <p class="project-intro"><?= htmlspecialchars($project['short_description'], ENT_QUOTES, 'UTF-8') ?></p>

                        <div class="project-hero__actions">
                            <a class="button button--primary" href="#process">Lihat Cara Ia Berfungsi</a>
                            <a class="button button--secondary" href="#impact">Teroka Impaknya</a>
                        </div>

                        <div class="project-status-pill"><?= htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="project-hero__visual" aria-hidden="true">
                        <div class="mockup-shell">
                            <div class="mockup-topbar">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="mockup-body">
                                <div class="mockup-sidebar">
                                    <span></span><span></span><span></span>
                                </div>
                                <div class="mockup-main">
                                    <div class="mockup-card mockup-card--large">
                                        <strong><?= e($project['name']) ?></strong>
                                        <p><?= e($project['type'] ?: $project['category']) ?></p>
                                    </div>
                                    <div class="mockup-card mockup-card--small">
                                        <span>01</span>
                                        <p>Masalah</p>
                                    </div>
                                    <div class="mockup-card mockup-card--small alt">
                                        <span>02</span>
                                        <p>Impak</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="facts-strip" aria-label="Maklumat projek">
            <div class="container facts-strip__inner">
                <div><strong>Platform</strong><span><?= htmlspecialchars($project['platform'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><strong>Pengguna</strong><span><?= htmlspecialchars($project['users'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><strong>Lokasi</strong><span><?= htmlspecialchars($project['location'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><strong>Kaedah</strong><span><?= htmlspecialchars($project['method'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><strong>Status</strong><span><?= htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
        </section>

        <section class="project-section project-section--problem">
            <div class="container project-section__inner">
                <div class="project-section__intro">
                    <p class="eyebrow eyebrow--project">Masalah</p>
                    <h2>Masalah yang Perlu Diselesaikan</h2>
                    <p><?= e($project['problem']) ?></p>
                </div>
                <div class="problem-points">
                    <?php foreach ($project['problem_points'] as $point): ?>
                        <div class="editorial-block editorial-block--large"><p><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></p></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="project-section project-section--solution">
            <div class="container project-section__inner">
                <div class="project-section__intro">
                    <p class="eyebrow eyebrow--project">Penyelesaian</p>
                    <h2>Penyelesaian yang Dibina untuk Keadaan Sebenar</h2>
                    <p><?= e($project['solution']) ?></p>
                </div>
                <div class="solution-grid">
                    <?php foreach ($project['solution_points'] as $point): ?>
                        <div class="editorial-block"><p><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></p></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="project-section project-section--process" id="process">
            <div class="container project-section__inner">
                <div class="project-section__intro">
                    <p class="eyebrow eyebrow--project">Cara ia berfungsi</p>
                    <h2>Daripada Interaksi kepada Hasil yang Boleh Digunakan</h2>
                </div>
                <div class="process-timeline">
                    <?php foreach ($project['process_steps'] as $index => $step): ?>
                        <div class="process-step">
                            <span class="process-step__number">0<?= $index + 1 ?></span>
                            <h3><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($step['text'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="project-section project-section--features">
            <div class="container project-section__inner">
                <div class="project-section__intro">
                    <p class="eyebrow eyebrow--project">Ciri utama</p>
                    <h2>Direka untuk Keadaan Sebenar</h2>
                </div>
                <div class="features-grid">
                    <?php foreach ($project['features'] as $index => $feature): ?>
                        <div class="feature-block <?= $index === 0 || $index === 3 ? 'feature-block--large' : '' ?>">
                            <p><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="project-section project-section--impact" id="impact">
            <div class="container project-section__inner">
                <div class="project-section__intro">
                    <p class="eyebrow eyebrow--project">Impak</p>
                    <h2>Daripada Projek kepada Impak</h2>
                    <p><?= e($project['impact']) ?></p>
                </div>
                <div class="impact-grid">
                    <div class="impact-statement">
                        <p><?= e($project['impact']) ?></p>
                    </div>
                    <div class="impact-list">
                        <?php foreach ($project['impact_points'] as $point): ?>
                            <div class="editorial-block"><p><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></p></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="project-section project-section--tech">
            <div class="container project-section__inner">
                <div class="project-section__intro">
                    <p class="eyebrow eyebrow--project">Teknologi</p>
                    <h2>Teknologi di Sebalik <?= e($project['name']) ?></h2>
                </div>
                <div class="tech-list">
                    <?php foreach ($project['technology_stack'] as $item): ?>
                        <div class="editorial-block"><p><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></p></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="project-section project-section--team">
            <div class="container project-section__inner">
                <div class="project-section__intro">
                    <p class="eyebrow eyebrow--project">Pasukan</p>
                    <h2>Dibangunkan dengan Tujuan yang Jelas</h2>
                </div>
                <?php if ($project['team']): ?>
                    <div class="team-card">
                        <?php foreach ($project['team'] as $person): ?>
                            <p><strong><?= e($person['full_name']) ?></strong><?= $person['role_title'] ? ' — ' . e($person['role_title']) : '' ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="team-card team-card--placeholder"><p>Maklumat pasukan belum disahkan untuk penerbitan.</p></div>
                <?php endif; ?>
            </div>
        </section>

        <section class="project-section project-section--journey">
            <div class="container project-section__inner">
                <div class="project-section__intro">
                    <p class="eyebrow eyebrow--project">Perjalanan</p>
                    <h2>Perjalanan Daripada Idea kepada Prototaip</h2>
                </div>
                <div class="timeline-list">
                    <?php foreach ($project['journey_milestones'] as $milestone): ?>
                        <div class="timeline-item"><p><?= htmlspecialchars($milestone, ENT_QUOTES, 'UTF-8') ?></p></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php if ($project['links']): ?>
            <section class="project-actions" aria-labelledby="project-actions-title">
                <div class="container project-actions__inner">
                    <div><p class="eyebrow eyebrow--project">Tindakan Projek</p><h2 id="project-actions-title">Teroka langkah seterusnya.</h2></div>
                    <div class="project-actions__links">
                        <?php foreach ($project['links'] as $index => $link): ?>
                            <a class="button <?= $index === 0 ? 'button--primary' : 'button--secondary' ?>" href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($link['label'] ?: ucwords(str_replace('_', ' ', $link['link_type']))) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="cta-strip project-cta" aria-labelledby="project-cta-title">
            <div class="container cta-strip__inner">
                <div>
                    <p class="eyebrow eyebrow--small">Setiap rekod membuka ruang</p>
                    <h2 id="project-cta-title">Setiap Rekod Membuka Ruang untuk Tindakan yang Lebih Baik.</h2>
                </div>
                <div class="cta-strip__actions">
                    <a class="button button--secondary" href="explore.php">Teroka Projek Lain</a>
                    <a class="button button--primary" href="submit-project.php">Hantar Projek Anda</a>
                </div>
            </div>
        </section>

    <?php elseif ($showComingSoon): ?>
        <section class="project-not-found" aria-labelledby="coming-soon-title">
            <div class="container project-not-found__inner">
                <p class="eyebrow eyebrow--project">Kisah projek sedang disediakan</p>
                <h1 id="coming-soon-title">Kisah untuk <?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') ?> sedang dalam penyediaan.</h1>
                <p>Maklumat penuh projek ini akan dikemaskini sebentar lagi. Sementara itu, anda boleh meneroka projek lain yang sudah tersedia.</p>
                <a class="button button--primary" href="explore.php">Teroka projek lain</a>
            </div>
        </section>
    <?php else: ?>
        <section class="project-not-found" aria-labelledby="not-found-title">
            <div class="container project-not-found__inner">
                <p class="eyebrow eyebrow--project">Projek tidak dijumpai</p>
                <h1 id="not-found-title">Kisah projek ini belum tersedia.</h1>
                <p>Halaman ini sedang disediakan. Kembali ke paparan projek untuk meneroka yang lain.</p>
                <a class="button button--primary" href="explore.php">Teroka projek lain</a>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
