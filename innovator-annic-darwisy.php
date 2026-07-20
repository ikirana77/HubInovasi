<?php
require_once __DIR__.'/includes/bootstrap.php';
require_once __DIR__.'/includes/public-ui.php';

$pageTitle = 'Anniq Darwisy | '.tr('Innovator SPARK','SPARK Innovator');
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
  <strong>Anniq Darwisy</strong>
 </nav>

 <section class="profile-top">
  <div class="profile-portrait">
   <img src="assets/images/home/innovator-anniq-darwisy.webp" alt="Potret Anniq Darwisy bin Amrin, innovator pelajar projek SPARK">
  </div>

  <div class="profile-copy">
   <span class="pm-kicker"><?= e(tr('INOVATOR','INNOVATOR')) ?></span>
   <h1>Anniq Darwisy</h1>
   <p><strong class="profile-role">Student Developer</strong> · 2DVM KPD</p>
   <blockquote class="profile-quote"><?= e(tr('Saya percaya sistem yang baik perlu mudah digunakan, jelas fungsinya dan mampu membantu pengguna menyelesaikan urusan harian dengan lebih cepat.','I believe a good system should be easy to use, clear in function and able to help users complete daily tasks faster.')) ?></blockquote>
   <div class="detail-meta">
    <span>♢ 2DVM KPD</span>
    <span>▣ SPARK</span>
    <span>⌖ KVKS</span>
    <span>♙ Team SPARK</span>
   </div>
  </div>

  <aside>
   <div class="profile-facts">
    <?php foreach([['rocket','SPARK',tr('Projek','Project')],['award','2DVM',tr('Kelas','Class')],['people','Team',tr('Kolaborasi','Collaboration')],['chart','Web',tr('Fokus','Focus')]] as $x): ?>
     <div class="pm-card">
      <span class="pm-icon" style="margin:auto;width:38px;height:38px"><?= ui_icon($x[0]) ?></span>
      <strong><?= e($x[1]) ?></strong>
      <span><?= e($x[2]) ?></span>
     </div>
    <?php endforeach; ?>
   </div>
   <div class="connect-card pm-card">
    <div>
     <h2><?= e(tr('Mari berhubung','Let’s connect')) ?></h2>
     <p><?= e(tr('Menyumbang kepada reka bentuk website, fungsi sistem dan kerjasama pasukan SPARK.','Contributes to website design, system functions and SPARK team collaboration.')) ?></p>
    </div>
    <a class="pm-btn primary" href="project.php?slug=spark"><?= e(tr('PROJEK','PROJECT')) ?> ↗</a>
   </div>
  </aside>
 </section>

 <section class="skill-row" id="skills">
  <div class="skill-box pm-card">
   <h2><?= e(tr('Kemahiran','Skills')) ?></h2>
   <div class="skill-chips">
    <?php foreach(['HTML','CSS','JavaScript','PHP','MySQL','Website Design','Problem Solving',tr('Senang Berkomunikasi','Communication'),tr('Mudah Bekerjasama','Teamwork')] as $skill): ?>
     <span><?= e($skill) ?></span>
    <?php endforeach; ?>
   </div>
  </div>

  <div class="skill-box pm-card">
   <h2><?= e(tr('Teknologi Yang Digunakan','Technologies Used')) ?></h2>
   <div class="skill-chips">
    <?php foreach(['VS Code','PHP','ChatGPT AI','Flutter','Dart'] as $tech): ?>
     <span><?= e($tech) ?></span>
    <?php endforeach; ?>
   </div>
  </div>
 </section>

 <section class="profile-content">
  <article class="project-showcase pm-card">
   <div class="pm-section-title"><h2><?= e(tr('Profil Ringkas','Profile Summary')) ?></h2></div>
   <p><?= e(tr('Anniq Darwisy bin Amrin ialah pelajar 2DVM KPD yang terlibat dalam pembangunan projek SPARK, sebuah aplikasi pengurusan keluar masuk pelajar asrama secara digital. Beliau menyumbang kepada pembangunan antaramuka, reka bentuk web, logik sistem dan fungsi aplikasi menggunakan kemahiran pembangunan web serta asas aplikasi mudah alih.','Anniq Darwisy bin Amrin is a 2DVM KPD student involved in developing SPARK, a digital hostel student movement management application. He contributes to interface development, website design, system logic and application functionality using web development skills and mobile app fundamentals.')) ?></p>
   <p><?= e(tr('Anniq mempunyai kekuatan dalam komunikasi, kerjasama pasukan dan penyelesaian masalah. Gabungan kemahiran teknikal dan soft skills ini membantu beliau menyumbang kepada pembangunan SPARK secara lebih teratur, terutama dalam aspek reka bentuk laman, fungsi sistem dan pengalaman pengguna.','Anniq has strengths in communication, teamwork and problem solving. This combination of technical ability and soft skills helps him contribute to SPARK in a more organised way, especially in website design, system functionality and user experience.')) ?></p>
  </article>

  <aside class="achievement-list pm-card">
   <div class="pm-section-title"><h2><?= e(tr('Peranan Dalam SPARK','Role in SPARK')) ?></h2></div>
   <ul>
    <?php foreach([[tr('Menyokong reka bentuk website','Supporting website design'),'Design'],[tr('Membangunkan fungsi web asas','Developing core web functions'),'PHP'],[tr('Mengurus struktur data sistem','Managing system data structure'),'MySQL'],[tr('Menyumbang kepada kerjasama pasukan','Contributing to teamwork'),'Team'],[tr('Membantu penyelesaian masalah projek','Helping solve project problems'),'Problem Solving']] as $x): ?>
     <li><span class="pm-icon" style="width:32px;height:32px"><?= ui_icon('award') ?></span><strong><?= e($x[0]) ?></strong><time><?= e($x[1]) ?></time></li>
    <?php endforeach; ?>
   </ul>
  </aside>
 </section>
</div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
