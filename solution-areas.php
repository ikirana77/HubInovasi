<?php
/**
 * Bidang Penyelesaian — projek dikumpulkan mengikut keperluan dunia sebenar.
 */
$pageTitle = 'Bidang Penyelesaian';
$activePage = 'areas';
require __DIR__ . '/data/projects.php';
require __DIR__ . '/includes/header.php';

$solutionAreas = [
    [
        'name' => 'Kehidupan Kampus',
        'statement' => 'Menjadikan pengalaman harian pelajar dan pengurusan institusi lebih lancar.',
        'categories' => ['Aplikasi Mudah Alih', 'Sistem Web'],
    ],
    [
        'name' => 'Komuniti & Kesejahteraan',
        'statement' => 'Menghubungkan teknologi dengan keperluan, keselamatan dan kesejahteraan komuniti.',
        'categories' => ['Komuniti'],
    ],
    [
        'name' => 'Pembelajaran Masa Hadapan',
        'statement' => 'Membina pengalaman pembelajaran yang lebih aktif, visual dan mudah diterokai.',
        'categories' => ['Pendidikan', 'Kecerdasan Buatan'],
    ],
];
?>
<main id="main-content" class="inner-page areas-page">
    <section class="page-hero page-hero--areas">
        <div class="container page-hero__inner">
            <p class="eyebrow">Bidang Penyelesaian</p>
            <h1>Mulakan dengan<br><span>keperluan sebenar.</span></h1>
            <p>Teroka inovasi mengikut cabaran yang ingin diselesaikan—bukan sekadar bahasa pengaturcaraan yang digunakan.</p>
        </div>
    </section>

    <section class="area-directory" aria-labelledby="area-directory-title">
        <div class="container">
            <div class="section-heading section-heading--compact">
                <p class="eyebrow">Peta Penyelesaian</p>
                <h2 id="area-directory-title">Idea yang berbeza. Matlamat yang sama: menghasilkan perubahan bermakna.</h2>
            </div>

            <div class="area-grid">
                <?php foreach ($solutionAreas as $index => $area):
                    $areaProjects = array_filter($projects, static function (array $project) use ($area): bool {
                        return $project['solution_area'] === $area['name'];
                    });
                ?>
                    <article class="area-card">
                        <div class="area-card__topline">
                            <span>0<?= $index + 1 ?></span>
                            <span><?= count($areaProjects) ?> projek</span>
                        </div>
                        <h2><?= htmlspecialchars($area['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars($area['statement'], ENT_QUOTES, 'UTF-8') ?></p>
                        <ul class="area-card__projects" aria-label="Projek dalam bidang <?= htmlspecialchars($area['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach ($areaProjects as $project): ?>
                                <li>
                                    <a href="project.php?slug=<?= rawurlencode($project['slug']) ?>">
                                        <span><?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <span aria-hidden="true">↗</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="cta-strip" aria-labelledby="areas-cta-title">
        <div class="container cta-strip__inner">
            <div><p class="eyebrow eyebrow--small">Semua inovasi</p><h2 id="areas-cta-title">Cari penyelesaian merentas semua bidang.</h2></div>
            <a class="button button--primary" href="explore.php">Teroka Projek</a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
