<?php
/**
 * Profil Mentor — rangka profil awam tanpa data peribadi rekaan.
 */
$pageTitle = 'Profil Mentor';
$activePage = '';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="inner-page profile-page">
    <section class="profile-hero profile-hero--mentor">
        <div class="container profile-hero__inner">
            <div class="profile-hero__mark" aria-hidden="true">M+</div>
            <div>
                <p class="eyebrow">Profil Mentor</p>
                <h1>Kepakaran yang membantu<br><span>idea bergerak ke hadapan.</span></h1>
                <p>Profil mentor individu akan diterbitkan selepas bidang kepakaran, projek seliaan dan pencapaian disahkan.</p>
            </div>
        </div>
    </section>
    <section class="profile-blueprint" aria-labelledby="mentor-blueprint-title">
        <div class="container">
            <div class="section-heading section-heading--compact">
                <p class="eyebrow">Kandungan Profil</p>
                <h2 id="mentor-blueprint-title">Mentor diiktiraf melalui kepakaran dan sumbangan kepada perjalanan projek.</h2>
            </div>
            <div class="profile-fields">
                <article><h3>Bidang Kepakaran</h3><p>Bidang teknikal, reka bentuk atau penyelidikan yang dibimbing.</p></article>
                <article><h3>Projek Seliaan</h3><p>Inovasi pelajar yang menerima panduan dan sokongan.</p></article>
                <article><h3>Sumbangan</h3><p>Pencapaian dan peranan mentor yang telah disahkan.</p></article>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
