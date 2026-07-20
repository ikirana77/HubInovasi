<?php $homeLaunchpad = $homeLaunchpad ?? false; $publicMockup = $publicMockup ?? false; ?>
<?php if ($homeLaunchpad || $publicMockup): ?>
    <footer class="site-footer site-footer--launchpad">
        <div class="container brand-endorsement">
            <a class="brand-endorsement__hub" href="index.php"><img src="assets/images/branding/hubinovasi-kvks-primary.png" alt="HubInovasi KVKS — <?= e(tr('Platform Inovasi Pelajar', 'Student Innovation Platform')) ?>"></a>
            <span class="brand-endorsement__divider" aria-hidden="true"></span>
            <div class="brand-endorsement__institution"><span><?= e(tr('Sebuah inisiatif rasmi oleh', 'An official initiative by')) ?></span><img src="assets/images/branding/kvks-official.png" alt="Kolej Vokasional Kuala Selangor"></div>
        </div>
        <div class="container launchpad-footer__grid">
            <div class="launchpad-footer__brand">
                <p><?= e(t('footer.tagline')) ?></p>
                <div class="launchpad-footer__socials" aria-label="<?= e(tr('Media sosial', 'Social media')) ?>">
                    <span aria-hidden="true">◎</span><span aria-hidden="true">in</span><span aria-hidden="true">▶</span><span aria-hidden="true">●</span>
                </div>
            </div>
            <nav class="launchpad-footer__column" aria-label="<?= e(t('footer.explore')) ?>">
                <h2><?= e(t('footer.explore')) ?></h2>
                <ul>
                    <li><a href="explore.php"><?= e(t('footer.discover')) ?></a></li>
                    <li><a href="solution-areas.php"><?= e(t('footer.areas')) ?></a></li>
                    <li><a href="explore.php"><?= e(t('footer.trending')) ?></a></li>
                    <li><a href="innovator.php"><?= e(t('footer.innovators')) ?></a></li>
                </ul>
            </nav>
            <nav class="launchpad-footer__column" aria-label="<?= e(t('footer.participate')) ?>">
                <h2><?= e(t('footer.participate')) ?></h2>
                <ul>
                    <li><a href="submit-project.php"><?= e(t('footer.submit')) ?></a></li>
                    <li><a href="competitions-impact.php"><?= e(t('footer.competitions')) ?></a></li>
                    <li><a href="competitions-impact.php"><?= e(t('footer.events')) ?></a></li>
                    <li><a href="mentor.php"><?= e(t('footer.mentors')) ?></a></li>
                </ul>
            </nav>
            <nav class="launchpad-footer__column" aria-label="<?= e(t('footer.about')) ?>">
                <h2><?= e(t('footer.about')) ?></h2>
                <ul>
                    <li><a href="about.php"><?= e(t('footer.about_platform')) ?></a></li>
                    <li><a href="competitions-impact.php"><?= e(t('footer.impact')) ?></a></li>
                    <li><a href="about.php"><?= e(t('footer.partners')) ?></a></li>
                    <li><a href="about.php"><?= e(t('footer.contact')) ?></a></li>
                </ul>
            </nav>
            <div class="launchpad-footer__column">
                <h2><?= e(t('footer.connect')) ?></h2>
                <p>HubInovasi KVKS<br>Kolej Vokasional Kuala Selangor<br>Selangor, Malaysia</p>
            </div>
        </div>
        <div class="container launchpad-footer__bottom">
            <small>&copy; <?= date('Y') ?> HubInovasi KVKS. <?= e(t('footer.rights')) ?></small>
            <div class="launchpad-footer__legal"><a href="about.php"><?= e(t('footer.privacy')) ?></a><a href="about.php"><?= e(t('footer.terms')) ?></a></div>
        </div>
    </footer>
<?php else: ?>
    <footer class="site-footer">
        <div class="container brand-endorsement brand-endorsement--dark">
            <a class="brand-endorsement__hub" href="index.php"><img src="assets/images/branding/hubinovasi-kvks-primary.png" alt="HubInovasi KVKS — <?= e(tr('Platform Inovasi Pelajar', 'Student Innovation Platform')) ?>"></a>
            <span class="brand-endorsement__divider" aria-hidden="true"></span>
            <div class="brand-endorsement__institution"><span><?= e(tr('Sebuah inisiatif rasmi oleh', 'An official initiative by')) ?></span><img src="assets/images/branding/kvks-official.png" alt="Kolej Vokasional Kuala Selangor"></div>
        </div>
        <div class="container site-footer__grid">
            <div>
                <p><?= e(tr('Idea Dicipta. Inovasi Dilancarkan.', 'Ideas Created. Innovation Launched.')) ?></p>
            </div>
            <nav aria-label="<?= e(tr('Navigasi kaki halaman', 'Footer navigation')) ?>">
                <ul>
                    <li><a href="explore.php"><?= e(t('footer.discover')) ?></a></li>
                    <li><a href="solution-areas.php"><?= e(t('footer.areas')) ?></a></li>
                    <li><a href="competitions-impact.php"><?= e(t('nav.impact')) ?></a></li>
                    <li><a href="innovator.php"><?= e(t('nav.innovators')) ?></a></li>
                    <li><a href="mentor.php"><?= e(t('nav.mentors')) ?></a></li>
                    <li><a href="about.php"><?= e(t('nav.about')) ?></a></li>
                </ul>
            </nav>
        </div>
        <div class="container site-footer__bottom">
            <small>&copy; <?= date('Y') ?> HubInovasi KVKS. <?= e(tr('Dibina untuk idea yang berani.', 'Built for bold ideas.')) ?></small>
            <span aria-hidden="true"><?= e(tr('DARI KVKS, UNTUK MASA HADAPAN ✦', 'FROM KVKS, FOR THE FUTURE ✦')) ?></span>
        </div>
    </footer>
<?php endif; ?>
</body>
</html>
