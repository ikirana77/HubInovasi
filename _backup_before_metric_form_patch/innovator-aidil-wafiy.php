<?php
require_once __DIR__.'/includes/bootstrap.php';
require_once __DIR__.'/includes/public-ui.php';

$pageTitle = 'Muhammad Aidil Wafiy | '.tr('Innovator SPARK','SPARK Innovator');
$activePage = 'innovators';
enable_public_mockup('mockup-profile');

require __DIR__.'/includes/header.php';
?>
<main id="main-content" class="pm-shell"><div class="container">
 <nav class="pm-breadcrumbs">
  <a href="index.php"><?= e(tr('Utama','Home')) ?></a>
  <span>›</span>
  <a href="index.php#innovators"><?= e(tr('Inovator','Innovators')) ?></a>
  <span>›</span>
  <strong>Muhammad Aidil Wafiy</strong>
 </nav>

 <section class="profile-top">
  <div>
   <div class="profile-portrait">
    <img src="assets/images/home/innovator-aidil-wafiy.webp" alt="Potret Muhammad Aidil Wafiy bin Mohd Adli, innovator pelajar projek SPARK">
   </div>
   <div class="detail-actions" style="margin-top:12px">
    <a class="pm-btn primary" href="project.php?slug=spark"><?= e(tr('Lihat Projek SPARK','View SPARK Project')) ?> ↗</a>
    <a class="pm-btn" href="#skills"><?= e(tr('Kemahiran','Skills')) ?> ↓</a>
   </div>
  </div>

  <div class="profile-copy">
   <span class="pm-kicker"><?= e(tr('INOVATOR PELAJAR','STUDENT INNOVATOR')) ?></span>
   <h1>Muhammad Aidil Wafiy bin Mohd Adli</h1>
   <p><strong class="profile-role">UI/UX Designer & Student Developer</strong> · 2DVM KPD</p>
   <blockquote class="profile-quote"><?= e(tr('Saya mahu membina aplikasi yang bukan sahaja berfungsi, tetapi mudah difahami dan membantu pengguna menyelesaikan masalah sebenar.','I want to build applications that do not only work, but are easy to understand and help users solve real problems.')) ?></blockquote>
   <div class="detail-meta">
    <span>▣ 2DVM KPD</span>
    <span>♢ SPARK</span>
    <span>⌖ KVKS</span>
    <span>↗ UI/UX + Development</span>
   </div>
  </div>

  <aside>
   <div class="profile-facts">
    <?php foreach([['rocket','SPARK',tr('Projek','Project')],['book','2DVM',tr('Kelas','Class')],['target','UI/UX',tr('Fokus','Focus')],['chart','SDLC',tr('Proses','Process')]] as $x): ?>
     <div class="pm-card"><span class="pm-icon" style="margin:auto;width:38px;height:38px"><?= ui_icon($x[0]) ?></span><strong><?= e($x[1]) ?></strong><span><?= e($x[2]) ?></span></div>
    <?php endforeach; ?>
   </div>
   <div class="connect-card pm-card">
    <div>
     <h2><?= e(tr('Innovator SPARK','SPARK Innovator')) ?></h2>
     <p><?= e(tr('Membangunkan pengalaman aplikasi asrama digital melalui reka bentuk antaramuka, sistem data dan pembangunan aplikasi.','Developing a digital hostel app experience through interface design, data systems and application development.')) ?></p>
    </div>
    <a class="pm-btn primary" href="project.php?slug=spark"><?= e(tr('PROJEK','PROJECT')) ?> ↗</a>
   </div>
  </aside>
 </section>

 <section class="profile-content">
  <article class="project-showcase pm-card">
   <div class="pm-section-title"><h2><?= e(tr('Profil Ringkas','Profile Summary')) ?></h2></div>
   <p><?= e(tr('Muhammad Aidil Wafiy bin Mohd Adli ialah pelajar 2DVM KPD yang terlibat dalam pembangunan projek SPARK, sebuah aplikasi pengurusan keluar masuk pelajar asrama berasaskan sistem digital. Beliau memberi fokus kepada reka bentuk antaramuka, pengalaman pengguna dan pembangunan fungsi aplikasi supaya sistem lebih mudah digunakan, tersusun dan sesuai dengan keperluan pengguna sebenar di persekitaran kolej.','Muhammad Aidil Wafiy bin Mohd Adli is a 2DVM KPD student involved in developing SPARK, a digital hostel student movement management application. His focus includes interface design, user experience and application functionality so the system is easier to use, structured and suitable for real users in a college environment.')) ?></p>
   <p><?= e(tr('Sebagai innovator pelajar, Aidil Wafiy menunjukkan kekuatan dalam menggabungkan reka bentuk antaramuka dengan pembangunan sistem. Beliau bukan sahaja memberi perhatian kepada fungsi aplikasi, tetapi juga kepada cara pengguna berinteraksi dengan sistem supaya pengalaman penggunaan menjadi lebih jelas, kemas dan praktikal.','As a student innovator, Aidil Wafiy shows strength in combining interface design with system development. He pays attention not only to application functionality, but also to how users interact with the system so the overall experience becomes clearer, cleaner and more practical.')) ?></p>
   <div class="profile-mantra">
    <div><h2><?= e(tr('Fokus Pembangunan','Development Focus')) ?></h2><p><?= e(tr('Daripada idea kepada sistem yang boleh digunakan.','From idea to usable system.')) ?></p></div>
    <div><strong>UI/UX</strong><p><?= e(tr('Reka bentuk pengalaman pengguna','User experience design')) ?></p></div>
    <div><strong>Database</strong><p><?= e(tr('SQL dan pengurusan data','SQL and data management')) ?></p></div>
    <div><strong>Mobile</strong><p>Flutter & Dart</p></div>
    <div><strong>AI</strong><p><?= e(tr('Eksplorasi teknologi pintar','Smart technology exploration')) ?></p></div>
   </div>
  </article>

  <aside class="achievement-list pm-card">
   <div class="pm-section-title"><h2><?= e(tr('Peranan Dalam SPARK','Role in SPARK')) ?></h2></div>
   <ul>
    <?php foreach([[tr('Mereka bentuk antaramuka aplikasi','Designing application interfaces'),'UI/UX'],[tr('Menyusun aliran pengguna dan fungsi sistem','Structuring user flow and system functions'),'SDLC'],[tr('Membangunkan komponen sistem dan data','Developing system and data components'),'PHP/SQL'],[tr('Menggunakan tool pembangunan moden','Using modern development tools'),'Codex/VS Code'],[tr('Menyokong prototaip aplikasi mudah alih','Supporting mobile app prototype'),'Flutter/Dart']] as $x): ?>
     <li><span class="pm-icon" style="width:32px;height:32px"><?= ui_icon('award') ?></span><strong><?= e($x[0]) ?></strong><time><?= e($x[1]) ?></time></li>
    <?php endforeach; ?>
   </ul>
  </aside>
 </section>

 <section class="skill-row" id="skills">
  <div class="skill-box pm-card">
   <h2><?= e(tr('Kemahiran','Skills')) ?></h2>
   <div class="skill-chips">
    <?php foreach(['UI/UX Design','Figma','SQL','PHP','Java','JavaScript','HTML5/CSS','AI','Supabase','SDLC'] as $skill): ?>
     <span><?= e($skill) ?></span>
    <?php endforeach; ?>
   </div>
  </div>
  <div class="skill-box pm-card">
   <h2><?= e(tr('Teknologi Digunakan','Technologies Used')) ?></h2>
   <div class="skill-chips">
    <?php foreach(['Codex','HTML5/CSS','SQL','XAMPP','VS Code','NetBeans IDE','Flutter','Dart'] as $tech): ?>
     <span><?= e($tech) ?></span>
    <?php endforeach; ?>
   </div>
  </div>
 </section>

 <section class="pm-ribbon pm-card">
  <div>
   <h2><?= tr('SPARK membuktikan idea pelajar boleh menjadi <em style="color:var(--pm-pink);font-style:normal">produk digital sebenar.</em>','SPARK proves student ideas can become <em style="color:var(--pm-pink);font-style:normal">real digital products.</em>') ?></h2>
   <p><?= e(tr('Teroka projek SPARK untuk melihat bagaimana penyelesaian asrama digital dibangunkan oleh pelajar KVKS.','Explore SPARK to see how a digital hostel solution is developed by KVKS students.')) ?></p>
  </div>
  <a class="pm-btn primary" href="project.php?slug=spark"><?= e(tr('Lihat SPARK','View SPARK')) ?> ↗</a>
  <a class="pm-btn" href="mentor.php"><?= e(tr('Lihat Mentor','View Mentors')) ?> →</a>
 </section>
</div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
