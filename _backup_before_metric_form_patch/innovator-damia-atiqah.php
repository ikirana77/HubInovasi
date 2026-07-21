<?php
require_once __DIR__.'/includes/bootstrap.php';
require_once __DIR__.'/includes/public-ui.php';

$pageTitle = 'Nur Damia Atiqah | '.tr('Multi-Project Innovator','Multi-Project Innovator');
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
  <strong>Nur Damia Atiqah</strong>
 </nav>

 <section class="profile-top">
  <div class="profile-portrait">
   <img src="assets/images/home/innovator-damia-atiqah.webp" alt="Potret Nur Damia Atiqah binti Mohamad Nizam, innovator pelajar projek e-Competency KVKS, Durian Radar dan HERS">
  </div>

  <div class="profile-copy">
   <span class="pm-kicker"><?= e(tr('INOVATOR','INNOVATOR')) ?></span>
   <h1>Nur Damia Atiqah</h1>
   <p><strong class="profile-role">Multi-Project Innovator</strong> · 2DVM KPD</p>
   <blockquote class="profile-quote"><?= e(tr('Saya belajar bahawa sistem yang baik perlu dibina berdasarkan keperluan sebenar pengguna, bukan sekadar fungsi yang nampak menarik.','I learned that a good system must be built around real user needs, not only attractive-looking features.')) ?></blockquote>
   <div class="detail-meta">
    <span>♢ 2DVM KPD</span>
    <span>▣ e-Competency KVKS</span>
    <span>▣ Durian Radar</span>
    <span>▣ HERS</span>
   </div>
  </div>

  <aside>
   <div class="profile-facts">
    <?php foreach([['rocket','3',tr('Projek','Projects')],['book','2DVM',tr('Kelas','Class')],['chart','System',tr('Fokus','Focus')],['people','Team',tr('Peranan','Role')]] as $x): ?>
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
    <?php foreach(['System Development','Application Development','Data Management','Dashboard Analytics','User Flow','QR-Based System','Map-Based Solution','Problem Solving','Team Collaboration','System Testing','Documentation'] as $skill): ?>
     <span><?= e($skill) ?></span>
    <?php endforeach; ?>
   </div>
  </div>

  <div class="skill-box pm-card">
   <h2><?= e(tr('Teknologi Yang Digunakan','Technologies Used')) ?></h2>
   <div class="skill-chips">
    <?php foreach(['Google Forms','Google Sheets','Looker Studio','Dashboard','Data Validation','PHP','MySQL','Flutter','Dart','Firebase','Supabase','QR Code','VS Code'] as $tech): ?>
     <span><?= e($tech) ?></span>
    <?php endforeach; ?>
   </div>
  </div>
 </section>

 <section class="profile-content">
  <article class="project-showcase pm-card">
   <div class="pm-section-title"><h2><?= e(tr('Profil Ringkas','Profile Summary')) ?></h2></div>
   <p><?= e(tr('Nur Damia Atiqah binti Mohamad Nizam ialah pelajar 2DVM KPD yang mempunyai pengalaman membangunkan beberapa projek sistem digital merentas keperluan pengguna yang berbeza. Beliau merupakan ahli kumpulan pembangunan e-Competency KVKS, sebuah inovasi digital berasaskan Google Workspace untuk pengurusan rekod, pensijilan dan penglibatan pensyarah secara bersepadu dan masa nyata, sebelum terus menyumbang kepada projek Durian Radar dan HERS.','Nur Damia Atiqah binti Mohamad Nizam is a 2DVM KPD student with experience developing multiple digital system projects across different user needs. She was part of the e-Competency KVKS development team, a Google Workspace-based innovation for managing lecturer records, certification and involvement in an integrated real-time system, before continuing to contribute to Durian Radar and HERS.')) ?></p>
   <p><?= e(tr('Penglibatan dalam tiga projek ini menunjukkan perkembangan beliau sebagai innovator pelajar yang mampu memahami keperluan sistem, aliran pengguna, pengurusan data dan fungsi aplikasi dalam pelbagai konteks. Dalam e-Competency KVKS, projek ini menggabungkan Google Forms, Google Sheets dan Looker Studio untuk membantu pentadbiran kolej mengakses data kompetensi pensyarah dengan lebih cepat, tersusun dan berasaskan bukti.','Her involvement in these three projects shows her growth as a student innovator who can understand system requirements, user flows, data management and application functionality across different contexts. In e-Competency KVKS, the project combines Google Forms, Google Sheets and Looker Studio to help college administrators access lecturer competency data faster, in a structured and evidence-based way.')) ?></p>
   <div class="profile-mantra">
    <div><h2><?= e(tr('Trek Projek','Project Track')) ?></h2><p><?= e(tr('Pengalaman merentas sistem, aplikasi dan data.','Experience across systems, apps and data.')) ?></p></div>
    <div><strong>e-Competency KVKS</strong><p><?= e(tr('Google Workspace, dashboard dan rekod kompetensi pensyarah','Google Workspace, dashboard and lecturer competency records')) ?></p></div>
    <div><strong>Durian Radar</strong><p><?= e(tr('Penyelesaian komuniti berasaskan lokasi','Location-based community solution')) ?></p></div>
    <div><strong>HERS</strong><p><?= e(tr('Rekod harian dan aliran QR','Daily records and QR flow')) ?></p></div>
    <div><strong>Data</strong><p><?= e(tr('Struktur dan pengurusan maklumat','Information structure and management')) ?></p></div>
   </div>
  </article>

  <aside class="achievement-list pm-card">
   <div class="pm-section-title"><h2><?= e(tr('Peranan Projek','Project Roles')) ?></h2></div>
   <ul>
    <?php foreach([[tr('Menyokong pembangunan e-Competency KVKS','Supporting e-Competency KVKS development'),'eKompetensi'],[tr('Menyumbang kepada projek peta komuniti','Contributing to community map project'),'Durian Radar'],[tr('Menyokong sistem rekod berasaskan QR','Supporting QR-based record system'),'HERS'],[tr('Memahami aliran pengguna sebenar','Understanding real user flows'),'UX'],[tr('Bekerja dalam pasukan pembangunan pelajar','Working in a student development team'),'Team']] as $x): ?>
     <li><span class="pm-icon" style="width:32px;height:32px"><?= ui_icon('award') ?></span><strong><?= e($x[0]) ?></strong><time><?= e($x[1]) ?></time></li>
    <?php endforeach; ?>
   </ul>
  </aside>
 </section>

 <section class="pm-ribbon pm-card">
  <div>
   <h2><?= tr('Tiga projek, satu perkembangan <em style="color:var(--pm-pink);font-style:normal">innovator pelajar.</em>','Three projects, one <em style="color:var(--pm-pink);font-style:normal">student innovator</em> growth journey.') ?></h2>
   <p><?= e(tr('Khadijah menunjukkan bagaimana pengalaman projek berulang boleh membina kematangan dalam pembangunan sistem sebenar.','Khadijah shows how repeated project experience can build maturity in real system development.')) ?></p>
  </div>
  <a class="pm-btn primary" href="project.php?slug=hers"><?= e(tr('Lihat HERS','View HERS')) ?> ↗</a>
  <a class="pm-btn" href="project.php?slug=durian-radar"><?= e(tr('Lihat Durian Radar','View Durian Radar')) ?> →</a>
 </section>
</div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
