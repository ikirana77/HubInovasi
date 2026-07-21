<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/public-ui.php';
require_once __DIR__ . '/includes/taxonomy.php';
require __DIR__ . '/data/projects.php';
$pageTitle = tr('Bidang Penyelesaian', 'Solution Areas');
$activePage = 'areas';
enable_public_mockup('mockup-areas');
$areas = hub_solution_areas();
$areaCounts = array_fill_keys(array_keys($areas), 0);
foreach ($projects as $listedProject) {
    $listedSlug = $listedProject['solution_area_slug'] ?? solution_area_slug($listedProject['solution_area'] ?? '');
    if (isset($areaCounts[$listedSlug])) $areaCounts[$listedSlug]++;
}
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="pm-shell"><div class="container">
 <nav class="pm-breadcrumbs"><a href="index.php"><?= e(tr('Utama','Home')) ?></a><span>›</span><strong><?= e(tr('Penyelesaian','Solutions')) ?></strong></nav>
 <section class="areas-hero areas-hero--image"><div class="areas-hero__copy"><h1 class="pm-display"><?= tr('Bidang <em>Penyelesaian</em>','Solution <em>Areas</em>') ?></h1><p class="pm-lead"><?= e(tr('Lapan bidang merentas tujuh program KVKS—projek dikumpulkan mengikut masalah dunia sebenar, bukan diasingkan mengikut jabatan.','Eight areas spanning all seven KVKS programmes—projects are grouped by real-world problems, not separated by department.')) ?></p></div><div class="areas-hero__image"><img src="assets/images/home/solution-areas-hero.webp" width="1600" height="900" alt="<?= e(tr('Pelajar dan mentor memetakan bidang penyelesaian inovasi di dinding idea','Students and mentor mapping innovation solution areas on an idea wall')) ?>"></div><aside class="areas-summary"><div><span class="pm-icon"><?= ui_icon('star') ?></span><p><strong><?= count($areas) ?></strong><?= e(tr('Bidang Penyelesaian','Solution Areas')) ?></p></div><div><span class="pm-icon orange"><?= ui_icon('people') ?></span><p><strong><?= count($projects) ?></strong><?= e(tr('Projek Disahkan','Verified Projects')) ?></p></div><div><span class="pm-icon"><?= ui_icon('bulb') ?></span><p><?= tr('7 program.<br><strong>1 ekosistem inovasi.</strong>','7 programmes.<br><strong>1 innovation ecosystem.</strong>') ?></p></div></aside></section>
 <section class="areas-grid">
  <?php $areaNumber = 0; foreach($areas as $areaSlug => $area): $areaNumber++; ?>
   <article class="area-tile pm-card <?= $areaNumber%2===0?'orange':'' ?>">
    <div class="area-tile__head"><span class="pm-icon <?= $areaNumber%2===0?'orange':'' ?>"><?= ui_icon($area['icon']) ?></span><div><b>0<?= $areaNumber ?></b><h2><?= e($area['name']) ?></h2><p><?= e($area['description']) ?></p><p class="area-programmes" aria-label="<?= e(tr('Program berkaitan','Related programmes')) ?>"><?php foreach($area['programmes'] as $code): ?><span><?= e($code) ?></span><?php endforeach; ?></p></div></div>
    <a href="explore.php?area=<?= rawurlencode($areaSlug) ?>"><span><?= e($areaCounts[$areaSlug].' '.tr('Projek Disahkan','Verified Projects')) ?></span><span>→</span></a>
   </article>
  <?php endforeach; ?>
 </section>
 <section class="pm-ribbon pm-card"><div><h2><?= tr('Tujuh program, <em style="color:var(--pm-pink);font-style:normal">satu pentas inovasi.</em>','Seven programmes, <em style="color:var(--pm-pink);font-style:normal">one innovation stage.</em>') ?></h2><p><?= e(tr('Satu projek boleh melibatkan lebih daripada satu program dan tetap mempunyai satu bidang penyelesaian utama.','A project may involve more than one programme while retaining one primary solution area.')) ?></p><p style="margin-top:12px"><a class="pm-btn primary" href="explore.php"><?= e(tr('TEROKA PROJEK','EXPLORE PROJECTS')) ?> →</a></p></div><?php foreach([["globe",tr('Masalah Dunia Sebenar','Real-World Problems')],["people",tr('Kolaborasi Rentas Program','Cross-Programme Collaboration')],["award",tr('Bukti yang Disahkan','Verified Evidence')],["rocket",tr('Idea kepada Impak','Ideas into Impact')]] as $x): ?><div><span class="pm-icon"><?= ui_icon($x[0]) ?></span><strong><?= e($x[1]) ?></strong><p><?= e(tr('Penyelesaian praktikal yang boleh diterangkan, diuji dan ditambah baik.','Practical solutions that can be explained, tested and improved.')) ?></p></div><?php endforeach; ?></section>
</div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
