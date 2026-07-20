<?php
require_once __DIR__.'/includes/bootstrap.php';
require_once __DIR__.'/includes/public-ui.php';

$pageTitle = 'Alya Qausar | '.tr('Multi-Project Innovator','Multi-Project Innovator');
$activePage = 'innovators';
enable_public_mockup('mockup-profile');

require __DIR__.'/includes/header.php';
?>
<main id="main-content" class="pm-shell"><div class="container">
 <nav class="pm-breadcrumbs">
  <a href="index.php"><?= e(tr('Utama','Home')) ?></a>
  <span>›</span>
  <a href="innovator.php"><?= e(tr('Inovator','Innovators')) ?></a>
  <span>›</span>
  <strong>Alya Qausar</strong>
 </nav>

 <section class="profile-top">
  <div class="profile-portrait">
   <img src="assets/images/home/innovator-qausar.webp" alt="Potret Nur Alya Qausar binti Azhar, innovator pelajar projek e-Competency KVKS, Durian Radar dan HERS">
  </div>

  <div class="profile-copy">
   <span class="pm-kicker"><?= e(tr('INOVATOR','INNOVATOR')) ?></span>
   <h1>Alya Qausar</h1>
   <p><strong class="profile-role">Multi-Project Innovator</strong> · <?= e(tr('Student Development Team','Student Development Team')) ?></p>
   <blockquote class="profile-quote"><?= e(tr('Saya percaya pengalaman membangunkan beberapa projek membantu saya memahami cara sistem digital boleh menyelesaikan masalah sebenar pengguna.','I believe working across multiple projects helps me understand how digital systems can solve real user problems.')) ?></blockquote>
   <div class="detail-meta">
    <span>▣ e-Competency KVKS</span>
    <span>▣ Durian Radar</span>
    <span>▣ HERS</span>
    <span>♙ Multi-Project Team</span>
   </div>
  </div>

  <aside>
   <div class="profile-facts">
    <?php foreach([['rocket','3',tr('Projek','Projects')],['chart','Data',tr('Fokus','Focus')],['book','System',tr('Bidang','Area')],['people','Team',tr('Peranan','Role')]] as $x): ?>
     <div class="pm-card">
      <span class="pm-icon" style="margin:auto;width:38px;height:38px"><?= ui_icon($x[0]) ?></span>
      <strong><?= e($x[1]) ?></strong>
      <span><?= e($x[2]) ?></span>
     </div>
    <?php endforeach; ?>
   </div>
   <div class="connect-card pm-card">
    <div>
     <h2><?= e(tr('Multi-project contributor','Multi-project contributor')) ?></h2>
     <p><?= e(tr('Menyumbang kepada pembangunan sistem dan aplikasi merentas e-Competency KVKS, Durian Radar dan HERS.','Contributes to system and app development across e-Competency KVKS, Durian Radar and HERS.')) ?></p>
    </div>
    <a class="pm-btn primary" href="project.php?slug=hers"><?= e(tr('HERS','HERS')) ?> ↗</a>
   </div>
  </aside>
 </section>

 <section class="skill-row" id="skills">
  <div class="skill-box pm-card">
   <h2><?= e(tr('Kemahiran','Skills')) ?></h2>
   <div class="skill-chips">
    <?php foreach(['System Development','Data Management','Dashboard Analytics','User Flow','QR-Based System','Map-Based Solution','Problem Solving','Team Collaboration','System Testing','Documentation'] as $skill): ?>
     <span><?= e($skill) ?></span>
    <?php endforeach; ?>
   </div>
  </div>

  <div class="skill-box pm-card">
   <h2><?= e(tr('Teknologi Yang Digunakan','Technologies Used')) ?></h2>
   <div class="skill-chips">
    <?php foreach(['Google Forms','Google Sheets','Looker Studio','Dashboard','Data Validation','Flutter','Dart','Firebase','Supabase','QR Code','VS Code'] as $tech): ?>
     <span><?= e($tech) ?></span>
    <?php endforeach; ?>
   </div>
  </div>
 </section>

 <section class="profile-content">
  <article class="project-showcase pm-card">
   <div class="pm-section-title"><h2><?= e(tr('Profil Ringkas','Profile Summary')) ?></h2></div>
   <p><?= e(tr('Nur Alya Qausar binti Azhar ialah innovator pelajar yang terlibat dalam pembangunan e-Competency KVKS, Durian Radar dan HERS. Penglibatan beliau merentas beberapa projek menunjukkan keupayaan untuk memahami keperluan pengguna, struktur data dan fungsi sistem dalam konteks yang berbeza.','Nur Alya Qausar binti Azhar is a student innovator involved in e-Competency KVKS, Durian Radar and HERS. Her involvement across multiple projects shows her ability to understand user needs, data structures and system functions in different contexts.')) ?></p>
   <p><?= e(tr('Dalam e-Competency KVKS, beliau merupakan sebahagian daripada pasukan yang membangunkan inovasi digital berasaskan Google Workspace untuk pengurusan rekod, pensijilan dan penglibatan pensyarah secara masa nyata. Pengalaman ini diteruskan melalui Durian Radar dan HERS yang melibatkan penyelesaian komuniti, data, aplikasi dan rekod operasi sebenar.','In e-Competency KVKS, she was part of the team that developed a Google Workspace-based digital innovation for managing lecturer records, certification and involvement in real time. This experience continued through Durian Radar and HERS, involving community solutions, data, applications and real operational records.')) ?></p>
  </article>

  <aside class="achievement-list pm-card">
   <div class="pm-section-title"><h2><?= e(tr('Peranan Projek','Project Roles')) ?></h2></div>
   <ul>
    <?php foreach([[tr('Menyokong pembangunan e-Competency KVKS','Supporting e-Competency KVKS development'),'e-Competency'],[tr('Menyumbang kepada projek peta komuniti','Contributing to community map project'),'Durian Radar'],[tr('Menyokong sistem rekod berasaskan QR','Supporting QR-based record system'),'HERS'],[tr('Memahami aliran pengguna dan data','Understanding user and data flow'),'UX/Data'],[tr('Bekerja dalam pasukan pembangunan pelajar','Working in a student development team'),'Team']] as $x): ?>
     <li><span class="pm-icon" style="width:32px;height:32px"><?= ui_icon('award') ?></span><strong><?= e($x[0]) ?></strong><time><?= e($x[1]) ?></time></li>
    <?php endforeach; ?>
   </ul>
  </aside>
 </section>

 <section class="pm-ribbon pm-card">
  <div>
   <h2><?= tr('Pengalaman merentas projek membina <em style="color:var(--pm-pink);font-style:normal">kematangan inovator.</em>','Cross-project experience builds <em style="color:var(--pm-pink);font-style:normal">innovator maturity.</em>') ?></h2>
   <p><?= e(tr('Qausar menunjukkan bagaimana pelajar boleh berkembang melalui projek sistem, data dan aplikasi sebenar.','Qausar shows how students can grow through real system, data and application projects.')) ?></p>
  </div>
  <a class="pm-btn primary" href="project.php?slug=hers"><?= e(tr('Lihat HERS','View HERS')) ?> ↗</a>
  <a class="pm-btn" href="project.php?slug=durian-radar"><?= e(tr('Lihat Durian Radar','View Durian Radar')) ?> →</a>
 </section>
</div></main>
<?php require __DIR__.'/includes/footer.php'; ?>

