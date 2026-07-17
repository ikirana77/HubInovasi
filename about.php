<?php
$pageTitle = 'Tentang Kami';
$activePage = 'about';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="inner-page about-page">
    <section class="page-hero page-hero--about">
        <div class="container page-hero__inner">
            <p class="eyebrow">Kenali HubInovasi</p>
            <h1>Tentang Kami</h1>
            <p>HubInovasi KVKS menghubungkan idea pelajar dengan ruang untuk berkembang, dikongsi dan mencipta impak yang nyata.</p>
        </div>
    </section>

    <section class="about-story" aria-labelledby="about-story-title">
        <div class="container about-story__grid">
            <div class="about-story__copy">
                <p class="eyebrow eyebrow--project">Visi kami</p>
                <h2 id="about-story-title">Membina ruang di mana idea pelajar boleh dilihat, diperkukuh dan dibawa ke tindakan.</h2>
                <p>HubInovasi bukan sekadar ruang paparan projek. Ia adalah ekosistem untuk mengumpul persoalan yang relevan, mencatat penyelesaian yang berani, dan mengangkat suara pelajar yang berusaha mencipta sesuatu yang bermakna.</p>
                <p>Di sini, inovasi dilihat sebagai satu perjalanan—daripada persoalan yang muncul dalam bilik darjah, kepada konsep yang disusun, kemudian menjadi sistem, produk atau pengalaman yang memberi manfaat kepada komuniti.</p>
            </div>
            <div class="about-story__panel">
                <p class="about-story__label">Apa yang kami perjuangkan</p>
                <p>Idea yang jelas.<br>Inovasi yang berani.<br>Impak yang boleh diukur.</p>
            </div>
        </div>
    </section>

    <section class="about-values" aria-labelledby="about-values-title">
        <div class="container">
            <div class="about-values__heading">
                <p class="eyebrow eyebrow--project">Nilai yang kami pegang</p>
                <h2 id="about-values-title">Satu pendekatan yang praktikal, manusiawi dan berani.</h2>
            </div>
            <div class="about-values__grid">
                <article class="about-card">
                    <h3>Berfokus kepada masalah sebenar</h3>
                    <p>Setiap projek bermula daripada pengalaman, cabaran atau keperluan yang dekat dengan kehidupan sebenar.</p>
                </article>
                <article class="about-card">
                    <h3>Menyokong pertumbuhan pelajar</h3>
                    <p>HubInovasi memberi ruang untuk mencuba, berkongsi, memperbaiki dan belajar daripada setiap langkah.</p>
                </article>
                <article class="about-card">
                    <h3>Melihat impak sebagai matlamat</h3>
                    <p>Walaupun masih dalam pembinaan, setiap idea dihargai kerana berjaya menyatakan keperluan yang perlu diselesaikan.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="about-cta" aria-labelledby="about-cta-title">
        <div class="container about-cta__inner">
            <div>
                <p class="eyebrow eyebrow--project">Perjalanan seterusnya</p>
                <h2 id="about-cta-title">Mari bangunkan ruang inovasi bersama.</h2>
            </div>
            <a class="button button--primary" href="submit-project.php">Hantar Projek Anda</a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
