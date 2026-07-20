<?php
require_once __DIR__ . '/includes/project-repository.php';
require_once __DIR__ . '/includes/public-ui.php';
$slug = trim((string)($_GET['slug'] ?? ''));
$project = $slug !== '' ? get_public_project_by_slug($slug) : null;
$pageTitle = $project ? $project['name'] : tr('Projek Tidak Dijumpai','Project Not Found');
$activePage = 'areas';
enable_public_mockup('mockup-project');
$visible = $project && !empty($project['detail_available']);
$programmeSummary = $project ? implode(', ', $project['programmes'] ?? []) : '';
$primaryCollaborator = $project['collaborators'][0]['name'] ?? '';
function pm_text($value): string { if(is_array($value)) return (string)($value['text'] ?? $value['title'] ?? implode(' ',array_filter($value,'is_string'))); return (string)$value; }
require __DIR__ . '/includes/header.php';


?>
<main id="main-content" class="pm-shell"><div class="container">
 <?php if($visible): ?>
  <nav class="pm-breadcrumbs"><a href="index.php"><?= e(tr('Utama','Home')) ?></a><span>›</span><a href="explore.php"><?= e(tr('Penyelesaian','Solutions')) ?></a><span>›</span><strong><?= e($project['name']) ?></strong></nav>
  <section class="detail-hero">
   <div class="detail-copy"><span class="pm-kicker"><?= e(tr('INOVASI','INNOVATION')) ?></span><h1 class="pm-display"><?= e($project['name']) ?></h1><p class="pm-lead"><?= e($project['tagline']) ?></p><div class="detail-meta"><?php if($programmeSummary): ?><span>♙ <?= e(tr('Program peneraju: ','Lead programme: ').$programmeSummary) ?></span><?php endif; ?><?php if($primaryCollaborator): ?><span>♢ <?= e(tr('Rakan kolaborasi: ','Collaborator: ').$primaryCollaborator) ?></span><?php endif; ?><span><?= e($project['innovation_type']) ?></span><span class="pm-kicker"><?= e($project['status']) ?></span></div><div class="detail-actions"><a class="pm-btn primary" href="#story"><?= e(tr('LIHAT PITCH','VIEW PITCH')) ?> ↗</a><?php if($project['links']): ?><a class="pm-btn" href="<?= e($project['links'][0]['url']) ?>" target="_blank" rel="noopener"><?= e(tr('DEMO LANGSUNG','LIVE DEMO')) ?> ↗</a><?php endif; ?><a class="pm-btn" href="#technology"><?= e(tr('TEKNOLOGI','TECHNOLOGY')) ?> ↓</a></div></div>
   <div class="detail-hero__visual"><div class="detail-device"><img src="<?= e(project_detail_image($project)) ?>" alt="<?= e(tr('Pratonton projek ','Project preview ').$project['name']) ?>"></div></div>
  </section>
  <section class="story-stack" id="story">
   <article class="story-row pm-card"><div class="story-index"><strong>01</strong><span class="pm-icon"><?= ui_icon('bulb') ?></span></div><div class="story-title"><h2><?= e(tr('Masalah','Problem')) ?></h2><p><?= e($project['problem']) ?></p></div><div class="story-content"><?php foreach(array_slice($project['problem_points'],0,3) as $i=>$point): ?><div><strong><?= ['3×',tr('Lewat','Late'),tr('Terhad','Limited')][$i] ?? '•' ?></strong><span><?= e(pm_text($point)) ?></span></div><?php endforeach; ?></div></article>
   <article class="story-row pm-card"><div class="story-index"><strong style="background:var(--pm-orange)">02</strong><span class="pm-icon orange"><?= ui_icon('target') ?></span></div><div class="story-title"><h2><?= e(tr('Penyelesaian','Solution')) ?></h2><p><?= e($project['solution']) ?></p></div><div class="story-content"><?php foreach(array_slice($project['solution_points'],0,4) as $point): ?><div><span class="pm-icon orange"><?= ui_icon('shield') ?></span><span><?= e(pm_text($point)) ?></span></div><?php endforeach; ?></div></article>
   <article class="story-row pm-card"><div class="story-index"><strong>03</strong><span class="pm-icon"><?= ui_icon('code') ?></span></div><div class="story-title"><h2><?= e(tr('Cara Ia Berfungsi','How It Works')) ?></h2></div><div class="story-content journey-line"><?php foreach(array_slice($project['process_steps'],0,5) as $step): ?><span><?= e(pm_text($step)) ?></span><?php endforeach; ?></div></article>
   <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px" class="detail-triptych">
    <article class="story-row pm-card" style="grid-template-columns:88px 1fr"><div class="story-index"><strong style="background:var(--pm-orange)">04</strong><span class="pm-icon orange"><?= ui_icon('star') ?></span></div><div class="story-title"><h2><?= e(tr('Ciri Utama','Key Features')) ?></h2><ul class="story-list"><?php foreach(array_slice($project['features'],0,6) as $x): ?><li><?= e(pm_text($x)) ?></li><?php endforeach; ?></ul></div></article>
    <article class="story-row pm-card" style="grid-template-columns:88px 1fr"><div class="story-index"><strong>05</strong><span class="pm-icon"><?= ui_icon('target') ?></span></div><div class="story-title"><h2><?= e(tr('Impak','Impact')) ?></h2><p><?= e($project['impact']) ?></p><ul class="story-list"><?php foreach(array_slice($project['impact_points'],0,4) as $x): ?><li><?= e(pm_text($x)) ?></li><?php endforeach; ?></ul></div></article>
    <article class="story-row pm-card" id="technology" style="grid-template-columns:88px 1fr"><div class="story-index"><strong style="background:var(--pm-orange)">06</strong><span class="pm-icon orange"><?= ui_icon('code') ?></span></div><div class="story-title"><h2><?= e(tr('Teknologi','Technology')) ?></h2><ul class="pm-tags"><?php foreach(array_slice($project['technology_stack'],0,8) as $x): ?><li><?= e(pm_text($x)) ?></li><?php endforeach; ?></ul></div></article>
   </div>
   <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" class="detail-duo">
    <article class="story-row pm-card" style="grid-template-columns:88px 1fr"><div class="story-index"><strong>07</strong><span class="pm-icon"><?= ui_icon('people') ?></span></div><div class="story-title"><h2><?= e(tr('Pasukan & Kolaborasi','Team & Collaboration')) ?></h2><?php if($project['team']): foreach($project['team'] as $person): ?><p><strong><?= e($person['full_name']) ?></strong><br><?= e($person['role_title'] ?: tr('Ahli Pasukan','Team Member')) ?></p><?php endforeach; else: ?><p><?= e(tr('Maklumat ahli pasukan sedang disahkan.','Team member information is being verified.')) ?></p><?php endif; ?><?php foreach($project['collaborators'] as $collaborator): ?><div class="project-collaborator"><span><?= e(tr('Rakan Kolaborasi','Collaborator')) ?></span><strong><?= e($collaborator['name']) ?></strong><p><?= e($collaborator['role_description']) ?></p></div><?php endforeach; ?></div></article>
    <article class="story-row pm-card" style="grid-template-columns:88px 1fr"><div class="story-index"><strong style="background:var(--pm-orange)">08</strong><span class="pm-icon orange"><?= ui_icon('pin') ?></span></div><div class="story-title"><h2><?= e(tr('Perjalanan Projek','Project Journey')) ?></h2><div class="journey-line"><?php foreach(array_slice($project['journey_milestones'],0,5) as $x): ?><span><?= e(pm_text($x)) ?></span><?php endforeach; ?></div></div></article>
   </div>
   <section class="detail-cta pm-card"><div><span class="pm-kicker">09 · <?= e(tr('SERUAN TINDAKAN','CALL TO ACTION')) ?></span><h2><?= e(tr('Mari bina penyelesaian yang lebih bermakna bersama.','Let’s build more meaningful solutions together.')) ?></h2><p><?= e($project['short_description']) ?></p></div><div class="detail-actions"><a class="pm-btn primary" href="about.php#contact"><?= e(tr('HUBUNGI PASUKAN','CONTACT TEAM')) ?> ✉</a><a class="pm-btn" href="explore.php"><?= e(tr('KONGSI','SHARE')) ?> ↗</a></div></section>
  </section>
 <?php else: ?><section style="padding:100px 0;text-align:center"><span class="pm-kicker"><?= e(tr('PROJEK','PROJECT')) ?></span><h1 class="pm-display"><?= e(tr('Kisah projek sedang disediakan.','Project story is being prepared.')) ?></h1><p class="pm-lead" style="margin-inline:auto"><?= e(tr('Kembali ke katalog untuk meneroka penyelesaian lain.','Return to the catalogue to explore other solutions.')) ?></p><p><a class="pm-btn primary" href="explore.php"><?= e(tr('Teroka Projek','Explore Projects')) ?></a></p></section><?php endif; ?>
</div></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
