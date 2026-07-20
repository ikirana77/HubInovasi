<?php $homeLaunchpad = $homeLaunchpad ?? false; ?>
<?php if ($homeLaunchpad): ?>
    <footer class="site-footer site-footer--launchpad">
        <div class="container launchpad-footer__grid">
            <div class="launchpad-footer__brand">
                <a class="brand" href="index.php">
                    <span class="brand__name"><span>HubInovasi</span><small>KVKS</small></span>
                </a>
                <p>A student innovation launchpad where ideas become impact.</p>
                <div class="launchpad-footer__socials" aria-label="Social media">
                    <span aria-hidden="true">◎</span><span aria-hidden="true">in</span><span aria-hidden="true">▶</span><span aria-hidden="true">●</span>
                </div>
            </div>
            <nav class="launchpad-footer__column" aria-label="Explore">
                <h2>Explore</h2>
                <ul>
                    <li><a href="explore.php">Discover Solutions</a></li>
                    <li><a href="solution-areas.php">Solution Areas</a></li>
                    <li><a href="explore.php">Trending Projects</a></li>
                    <li><a href="innovator.php">Innovators</a></li>
                </ul>
            </nav>
            <nav class="launchpad-footer__column" aria-label="Participate">
                <h2>Participate</h2>
                <ul>
                    <li><a href="submit-project.php">Submit Project</a></li>
                    <li><a href="competitions-impact.php">Competitions</a></li>
                    <li><a href="competitions-impact.php">Events</a></li>
                    <li><a href="mentor.php">Mentors</a></li>
                </ul>
            </nav>
            <nav class="launchpad-footer__column" aria-label="About">
                <h2>About</h2>
                <ul>
                    <li><a href="about.php">About HubInovasi KVKS</a></li>
                    <li><a href="competitions-impact.php">Our Impact</a></li>
                    <li><a href="about.php">Partners</a></li>
                    <li><a href="about.php">Contact Us</a></li>
                </ul>
            </nav>
            <div class="launchpad-footer__column">
                <h2>Stay Connected</h2>
                <p>HubInovasi KVKS<br>Kolej Vokasional Kuala Selangor<br>Selangor, Malaysia</p>
            </div>
        </div>
        <div class="container launchpad-footer__bottom">
            <small>&copy; <?= date('Y') ?> HubInovasi KVKS. All rights reserved.</small>
            <div class="launchpad-footer__legal"><a href="about.php">Privacy Policy</a><a href="about.php">Terms of Use</a></div>
        </div>
    </footer>
<?php else: ?>
    <footer class="site-footer">
        <div class="container site-footer__grid">
            <div>
                <a class="brand brand--footer" href="index.php">
                    <span class="brand__mark" aria-hidden="true">H<span>+</span></span>
                    <span class="brand__name">Hub<span>Inovasi</span><small>KVKS</small></span>
                </a>
                <p>Idea Dicipta. Inovasi Dilancarkan.</p>
            </div>
            <nav aria-label="Navigasi kaki halaman">
                <ul>
                    <li><a href="explore.php">Teroka Inovasi</a></li>
                    <li><a href="solution-areas.php">Bidang Penyelesaian</a></li>
                    <li><a href="competitions-impact.php">Pertandingan &amp; Impak</a></li>
                    <li><a href="innovator.php">Inovator</a></li>
                    <li><a href="mentor.php">Mentor</a></li>
                    <li><a href="about.php">Tentang Kami</a></li>
                </ul>
            </nav>
        </div>
        <div class="container site-footer__bottom">
            <small>&copy; <?= date('Y') ?> HubInovasi KVKS. Dibina untuk idea yang berani.</small>
            <span aria-hidden="true">DARI KVKS, UNTUK MASA HADAPAN ✦</span>
        </div>
    </footer>
<?php endif; ?>
</body>
</html>
