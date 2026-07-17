<?php
/**
 * HubInovasi KVKS — Homepage / Startup Pitch Launchpad.
 */
$pageTitle = 'Utama';
$activePage = 'home';
require __DIR__ . '/data/projects.php';

$featuredProject = null;
foreach ($projects as $entry) {
    if (!empty($entry['featured']) && !empty($entry['detail_available'])) {
        $featuredProject = $entry;
        break;
    }
}

$projectCount = count($projects);
$availablePitchCount = count(array_filter($projects, static fn (array $project): bool => !empty($project['detail_available'])));
require __DIR__ . '/includes/header.php';
?>

<main id="main-content">
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero__decoration hero__decoration--circle" aria-hidden="true"></div>
        <div class="hero__decoration hero__decoration--spark" aria-hidden="true">✦</div>
        <div class="container hero__grid">
            <div class="hero__content">
                <p class="announcement"><span aria-hidden="true">●</span> Startup Pitch Launchpad • KVKS</p>
                <p class="eyebrow">Idea Dicipta. Inovasi Dilancarkan.</p>
                <h1 id="hero-title">Idea layak mendapat<br>lebih daripada <span>folder penghantaran.</span></h1>
                <p class="hero__intro">HubInovasi KVKS mempersembahkan projek pelajar sebagai produk dan penyelesaian dunia sebenar—dengan cerita yang jelas, bukti bermakna dan potensi untuk berkembang.</p>
                <div class="hero__actions" aria-label="Tindakan utama">
                    <a class="button button--primary" href="explore.php">Teroka Inovasi <span aria-hidden="true">↗</span></a>
                    <a class="button button--secondary" href="submit-project.php">Hantar Projek <span aria-hidden="true">→</span></a>
                </div>
                <dl class="hero__facts" aria-label="Maklumat semasa HubInovasi">
                    <div><dt>Projek direkodkan</dt><dd><?= $projectCount ?></dd></div>
                    <div><dt>Pitch lengkap tersedia</dt><dd><?= $availablePitchCount ?></dd></div>
                </dl>
            </div>
            <div class="hero__visual" aria-hidden="true">
                <div class="idea-card idea-card--main">
                    <span class="idea-card__number">CERITA PRODUK</span>
                    <span class="idea-card__icon">↗</span>
                    <strong>Masalah.<br>Penyelesaian.<br><em>Impak.</em></strong>
                </div>
                <div class="idea-card idea-card--note">DIBINA OLEH<br>INOVATOR KVKS</div>
            </div>
        </div>
    </section>

    <?php if ($featuredProject): ?>
        <section class="home-feature" aria-labelledby="home-feature-title">
            <div class="container">
                <div class="section-heading section-heading--compact">
                    <p class="eyebrow">Penyelesaian Pilihan</p>
                    <h2 id="home-feature-title">Satu masalah sebenar. Satu penyelesaian yang boleh diuji.</h2>
                </div>
                <article class="feature-pitch">
                    <div class="feature-pitch__copy">
                        <p class="feature-pitch__category"><?= htmlspecialchars($featuredProject['category'], ENT_QUOTES, 'UTF-8') ?></p>
                        <h3><?= htmlspecialchars($featuredProject['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="feature-pitch__tagline"><?= htmlspecialchars($featuredProject['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p><?= htmlspecialchars($featuredProject['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="button button--primary" href="project.php?slug=<?= rawurlencode($featuredProject['slug']) ?>">Lihat Cerita Produk <span aria-hidden="true">↗</span></a>
                    </div>
                    <div class="feature-pitch__visual" aria-label="Ringkasan nilai projek <?= htmlspecialchars($featuredProject['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <span>MASALAH</span><strong>→</strong><span>PENYELESAIAN</span><strong>→</strong><span>IMPAK</span>
                    </div>
                </article>
            </div>
        </section>
    <?php endif; ?>

    <section class="journey" aria-labelledby="journey-title">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Cara Inovasi Bergerak</p>
                <h2 id="journey-title">Daripada Masalah kepada Penyelesaian.<br>Daripada Projek kepada Impak.</h2>
            </div>
            <ol class="journey__steps">
                <li class="journey__step journey__step--problem"><span class="journey__number">01</span><div><h3>Masalah</h3><p>Mulakan dengan keperluan sebenar yang dekat dengan pengguna dan komuniti.</p></div></li>
                <li class="journey__step journey__step--solution"><span class="journey__number">02</span><div><h3>Penyelesaian</h3><p>Terangkan idea, cara ia berfungsi dan manfaatnya dalam bahasa yang mudah.</p></div></li>
                <li class="journey__step journey__step--impact"><span class="journey__number">03</span><div><h3>Impak</h3><p>Tunjukkan bukti, hasil atau potensi perubahan yang boleh dibawa oleh projek.</p></div></li>
            </ol>
        </div>
    </section>

    <section class="home-areas" aria-labelledby="home-areas-title">
        <div class="container home-areas__grid">
            <div>
                <p class="eyebrow">Bidang Penyelesaian</p>
                <h2 id="home-areas-title">Teroka mengikut masalah yang mahu diselesaikan.</h2>
                <p>Kami menghubungkan projek dengan keperluan kampus, komuniti dan pembelajaran masa hadapan.</p>
                <a href="solution-areas.php">Lihat semua bidang <span aria-hidden="true">→</span></a>
            </div>
            <ol class="home-areas__list">
                <li><span>01</span><strong>Kehidupan Kampus</strong></li>
                <li><span>02</span><strong>Komuniti &amp; Kesejahteraan</strong></li>
                <li><span>03</span><strong>Pembelajaran Masa Hadapan</strong></li>
            </ol>
        </div>
    </section>

    <section class="people-impact" aria-labelledby="people-impact-title">
        <div class="container people-impact__grid">
            <div class="people-impact__statement">
                <p class="eyebrow">Manusia di Sebalik Idea</p>
                <h2 id="people-impact-title">Pelajar membina.<br>Mentor menggerakkan.</h2>
                <p>HubInovasi mengiktiraf pencipta, pembangun, pereka, penyelidik dan pembimbing di sebalik setiap penyelesaian.</p>
            </div>
            <div class="people-impact__links">
                <a href="innovator.php"><span>Profil Inovator</span><span aria-hidden="true">↗</span></a>
                <a href="mentor.php"><span>Profil Mentor</span><span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </section>

    <section class="competition-callout" aria-labelledby="competition-title">
        <div class="container competition-callout__inner">
            <div><p class="eyebrow">Pertandingan &amp; Impak</p><h2 id="competition-title">Sedia untuk membawa prototaip kepada bukti?</h2></div>
            <p>Jejaki peluang, dokumentasikan hasil rintis dan bina tuntutan impak yang jujur.</p>
            <a class="button button--secondary" href="competitions-impact.php">Teroka Peluang <span aria-hidden="true">→</span></a>
        </div>
    </section>

    <section class="launch-strip" aria-label="Seruan untuk menghantar projek">
        <div class="container launch-strip__inner">
            <p><span aria-hidden="true">✦</span> Ada idea yang patut dilihat dunia?</p>
            <a href="submit-project.php">Mulakan di sini <span aria-hidden="true">→</span></a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
