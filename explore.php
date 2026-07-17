<?php
$pageTitle = 'Teroka Inovasi';
$activePage = 'explore';
require __DIR__ . '/data/projects.php';
require __DIR__ . '/includes/header.php';
$publicProjectCount = count($projects);
?>
<main id="main-content" class="inner-page explore-page">
    <section class="page-hero page-hero--explore">
        <div class="container page-hero__inner">
            <p class="eyebrow">TEROKA INOVASI</p>
            <h1>Idea Berani.<br> Penyelesaian Sebenar.</h1>
            <p>Temui projek inovasi pelajar yang dibina untuk menyelesaikan masalah sebenar, menghasilkan impak dan membuka peluang baharu.</p>
            <div class="page-hero__meta">
                <span class="project-count-pill"><?= $publicProjectCount ?> PROJEK DIPAMERKAN</span>
            </div>
        </div>
    </section>

    <section class="explore-toolbar" aria-labelledby="explore-toolbar-title">
        <div class="container explore-toolbar__inner">
            <div class="explore-toolbar__head">
                <div>
                    <p class="eyebrow eyebrow--small">Peta inovasi</p>
                    <h2 id="explore-toolbar-title">Cari projek yang sesuai</h2>
                </div>
                <p class="toolbar-count" id="project-count" aria-live="polite"><?= $publicProjectCount ?> projek dipaparkan</p>
            </div>

            <div class="explore-toolbar__controls">
                <label class="search-field" for="project-search">
                    <span class="sr-only">Cari projek, teknologi atau pasukan</span>
                    <input type="search" id="project-search" name="project-search" placeholder="Cari projek, teknologi atau pasukan…" autocomplete="off">
                </label>

                <div class="filter-group" role="group" aria-label="Tapisan kategori">
                    <button type="button" class="filter-chip is-active" data-category="Semua" aria-pressed="true">Semua</button>
                    <button type="button" class="filter-chip" data-category="Aplikasi Mudah Alih" aria-pressed="false">Aplikasi Mudah Alih</button>
                    <button type="button" class="filter-chip" data-category="Sistem Web" aria-pressed="false">Sistem Web</button>
                    <button type="button" class="filter-chip" data-category="Kecerdasan Buatan" aria-pressed="false">Kecerdasan Buatan</button>
                    <button type="button" class="filter-chip" data-category="Komuniti" aria-pressed="false">Komuniti</button>
                    <button type="button" class="filter-chip" data-category="Pendidikan" aria-pressed="false">Pendidikan</button>
                </div>
            </div>
        </div>
    </section>

    <section class="explore-insight" aria-labelledby="explore-insight-title">
        <div class="container explore-insight__inner">
            <div>
                <p class="eyebrow eyebrow--small">Kenapa ini penting</p>
                <h2 id="explore-insight-title">Setiap projek mencerminkan satu bentuk penyelesaian yang berbeza untuk masalah yang nyata.</h2>
            </div>
            <p>Daripada aplikasi mudah alih dan sistem web kepada penyelesaian komuniti dan pendidikan, laman ini menampilkan idea yang membina pengalaman yang lebih baik untuk pengguna dan komuniti.</p>
        </div>
    </section>

    <section class="explore-grid-section" aria-labelledby="project-grid-title">
        <div class="container">
            <div class="project-grid" id="project-grid">
                <?php foreach ($projects as $index => $project):
                    $searchText = strtolower($project['name'] . ' ' . $project['tagline'] . ' ' . $project['category'] . ' ' . implode(' ', $project['technologies']));
                    $detailUrl = 'project.php?slug=' . rawurlencode($project['slug']);
                ?>
                    <article class="project-card <?= $project['featured'] ? 'project-card--featured' : '' ?> <?= $project['accent'] ?>"
                             data-category="<?= htmlspecialchars($project['category'], ENT_QUOTES, 'UTF-8') ?>"
                             data-search="<?= htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="project-card__visual" aria-hidden="true">
                            <span class="project-card__number"><?= htmlspecialchars($project['number'], ENT_QUOTES, 'UTF-8') ?></span>
                            <div class="project-card__visual-mark">✦</div>
                        </div>
                        <div class="project-card__body">
                            <?php if ($project['featured']): ?>
                                <span class="project-card__label">PROJEK PILIHAN</span>
                            <?php endif; ?>
                            <span class="project-card__category"><?= htmlspecialchars($project['category'], ENT_QUOTES, 'UTF-8') ?></span>
                            <h3><?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($project['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
                            <ul class="project-card__tags">
                                <?php foreach ($project['technologies'] as $tech): ?>
                                    <li><?= htmlspecialchars($tech, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a class="project-card__link" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>">Lihat Projek</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="empty-state" id="empty-state" hidden>
                <h3>Tiada inovasi ditemui.</h3>
                <p>Cuba gunakan kata kunci atau kategori yang berbeza.</p>
            </div>
        </div>
    </section>

    <section class="cta-strip" aria-labelledby="cta-strip-title">
        <div class="container cta-strip__inner">
            <div>
                <p class="eyebrow eyebrow--small">Kongsi inspirasi</p>
                <h2 id="cta-strip-title">Ada projek yang patut diketahui ramai?</h2>
            </div>
            <a class="button button--primary" href="submit-project.php">Hantar Projek</a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
