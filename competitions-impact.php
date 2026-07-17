<?php
/**
 * Pertandingan & Impak — peluang, bukti dan hasil projek.
 */
$pageTitle = 'Pertandingan & Impak';
$activePage = 'impact';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="inner-page impact-page">
    <section class="page-hero page-hero--impact">
        <div class="container page-hero__inner">
            <p class="eyebrow">Pertandingan &amp; Impak</p>
            <h1>Daripada prototaip<br>kepada <span>bukti.</span></h1>
            <p>Ruang untuk peluang pertandingan, pengiktirafan, hasil rintis dan perubahan yang dapat dibuktikan.</p>
        </div>
    </section>

    <section class="impact-framework" aria-labelledby="impact-framework-title">
        <div class="container">
            <div class="section-heading section-heading--compact">
                <p class="eyebrow">Bina Keyakinan</p>
                <h2 id="impact-framework-title">Impak yang baik bermula dengan tuntutan yang jujur.</h2>
            </div>
            <div class="evidence-grid">
                <article><span>01</span><h3>Hasil Rintis</h3><p>Catat pemerhatian awal, maklum balas pengguna dan perkara yang telah diuji.</p></article>
                <article><span>02</span><h3>Data &amp; Bukti</h3><p>Bezakan data sebenar, anggaran dan sasaran supaya setiap tuntutan boleh dipercayai.</p></article>
                <article><span>03</span><h3>Pengiktirafan</h3><p>Rekod penyertaan, anugerah dan pencapaian hanya selepas ia disahkan.</p></article>
            </div>
        </div>
    </section>

    <section class="opportunity-board" aria-labelledby="opportunity-title">
        <div class="container opportunity-board__inner">
            <div>
                <p class="eyebrow">Papan Peluang</p>
                <h2 id="opportunity-title">Peluang pertandingan akan diumumkan di sini.</h2>
            </div>
            <p>Belum ada pertandingan rasmi diterbitkan. Maklumat tarikh, penganjur, kelayakan dan pautan penyertaan akan dipaparkan selepas pengesahan.</p>
        </div>
    </section>

    <section class="cta-strip" aria-labelledby="impact-cta-title">
        <div class="container cta-strip__inner">
            <div><p class="eyebrow eyebrow--small">Sediakan pitch</p><h2 id="impact-cta-title">Bawa masalah, penyelesaian dan bukti anda.</h2></div>
            <a class="button button--primary" href="submit-project.php">Hantar Projek</a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
