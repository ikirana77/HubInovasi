<?php
/**
 * Profil Inovator — rangka profil awam tanpa data peribadi rekaan.
 */
$pageTitle = 'Profil Inovator';
$activePage = '';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="inner-page profile-page">
    <section class="profile-hero">
        <div class="container profile-hero__inner">
            <div class="profile-hero__mark" aria-hidden="true">I+</div>
            <div>
                <p class="eyebrow">Profil Inovator</p>
                <h1>Pelajar sebagai pembina,<br><span>pencipta dan penyelesai masalah.</span></h1>
                <p>Profil inovator individu akan diterbitkan selepas nama, peranan, kemahiran dan pencapaian disahkan.</p>
            </div>
        </div>
    </section>
    <section class="profile-blueprint" aria-labelledby="innovator-blueprint-title">
        <div class="container">
            <div class="section-heading section-heading--compact">
                <p class="eyebrow">Kandungan Profil</p>
                <h2 id="innovator-blueprint-title">Setiap profil akan menghubungkan individu dengan hasil kerja sebenar.</h2>
            </div>
            <div class="profile-fields">
                <article><h3>Peranan &amp; Kemahiran</h3><p>Sumbangan khusus dalam pembangunan, reka bentuk atau penyelidikan.</p></article>
                <article><h3>Projek</h3><p>Produk dan penyelesaian yang dibina bersama pasukan.</p></article>
                <article><h3>Pencapaian</h3><p>Pengiktirafan dan sijil yang telah disahkan.</p></article>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
