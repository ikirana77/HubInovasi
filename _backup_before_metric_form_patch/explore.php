<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/public-ui.php';
require_once __DIR__ . '/includes/taxonomy.php';
require __DIR__ . '/data/projects.php';
$pageTitle = tr('Teroka Penyelesaian', 'Discover Solutions');
$activePage = 'explore';
enable_public_mockup('mockup-discover');
$allCategory = tr('Semua Projek', 'All Projects');
$displayProjects = $projects;
$areas = hub_solution_areas();
$programmes = hub_programmes();
$categoryCounts = array_fill_keys(array_keys($areas), 0);
foreach ($displayProjects as $listedProject) {
    $listedCategory = $listedProject['solution_area_slug'] ?? solution_area_slug($listedProject['solution_area'] ?? '');
    if (isset($categoryCounts[$listedCategory])) $categoryCounts[$listedCategory]++;
}
$initialArea = solution_area_slug((string) ($_GET['area'] ?? ''));
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="pm-shell">
 <section class="discover-hero"><div class="container"><h1 class="pm-display"><?= tr('Teroka <em>Penyelesaian</em>', 'Discover <em>Solutions</em>') ?></h1><p class="pm-lead"><?= e(tr('Teroka inovasi pelajar yang menyelesaikan masalah sebenar dan menghasilkan impak positif.','Explore student innovations that solve real problems and create positive impact.')) ?></p></div></section>
 <div class="container">
  <section class="discover-toolbar pm-card" aria-label="<?= e(tr('Carian dan tapisan projek','Project search and filters')) ?>">
   <label class="discover-search"><span class="sr-only"><?= e(tr('Cari projek','Search projects')) ?></span><input id="project-search" type="search" placeholder="<?= e(tr('Cari projek, kata kunci atau masalah…','Search projects, keywords or problems…')) ?>"></label>
   <select id="area-filter" aria-label="<?= e(tr('Bidang penyelesaian','Solution area')) ?>"><option value="<?= e($allCategory) ?>"><?= e(tr('Semua Bidang','All Solution Areas')) ?></option><?php foreach($areas as $slug=>$area): ?><option value="<?= e($slug) ?>" <?= $initialArea===$slug?'selected':'' ?>><?= e($area['name']) ?></option><?php endforeach; ?></select>
   <select id="programme-filter" aria-label="<?= e(tr('Program penyumbang','Contributing programme')) ?>"><option value=""><?= e(tr('Semua Program','All Programmes')) ?></option><?php foreach($programmes as $code=>$label): ?><option value="<?= e($code) ?>"><?= e($code) ?></option><?php endforeach; ?></select>
   <select aria-label="<?= e(tr('Peringkat','Level')) ?>"><option><?= e(tr('Semua Peringkat','All Levels')) ?></option></select>
   <select aria-label="Status"><option><?= e(tr('Semua Status','All Status')) ?></option></select>
   <select aria-label="<?= e(tr('Susunan','Sort order')) ?>"><option><?= e(tr('Terkini','Latest')) ?></option></select>
  </section>
  <ul class="discover-categories" aria-label="<?= e(tr('Kategori projek','Project categories')) ?>">
   <li><button type="button" class="filter-chip <?= $initialArea===''?'is-active':'' ?>" data-category="<?= e($allCategory) ?>" aria-pressed="<?= $initialArea===''?'true':'false' ?>">▦ <?= e($allCategory) ?><span><?= count($displayProjects) ?></span></button></li>
   <?php $areaIndex=0; foreach($areas as $slug=>$area): ?><li><button type="button" class="filter-chip <?= $initialArea===$slug?'is-active':'' ?>" data-category="<?= e($slug) ?>" aria-pressed="<?= $initialArea===$slug?'true':'false' ?>"><?= ['▱','♧','♡','✿','⌂','◇','◉','⬡'][$areaIndex++ % 8] ?> <?= e($area['name']) ?><span><?= e((string) $categoryCounts[$slug]) ?></span></button></li><?php endforeach; ?>
  </ul>
  <section class="discover-grid" id="project-grid" data-all-category="<?= e($allCategory) ?>" data-initial-category="<?= e($initialArea ?: $allCategory) ?>" data-count-template="<?= e(tr('{count} projek dipaparkan','{count} projects shown')) ?>">
   <?php foreach($displayProjects as $project): $searchText=strtolower($project['name'].' '.$project['tagline'].' '.$project['solution_area'].' '.implode(' ',$project['programmes']).' '.implode(' ',$project['technologies'])); ?>
    <article class="discover-card pm-card" data-category="<?= e($project['solution_area_slug']) ?>" data-programmes="<?= e(implode(' ',$project['programmes'])) ?>" data-search="<?= e($searchText) ?>">
     <a class="discover-card__image" href="project.php?slug=<?= rawurlencode($project['slug']) ?>"><img src="<?= e(project_image($project['slug'])) ?>" alt="<?= e($project['name']) ?>"><span class="discover-card__save">♡</span></a>
     <div class="discover-card__body"><h3><?= e($project['name']) ?></h3><p><?= e($project['tagline']) ?></p><ul class="pm-tags"><?php foreach(array_slice($project['technologies'],0,3) as $tech): ?><li><?= e($tech) ?></li><?php endforeach; ?></ul><div class="discover-card__meta"><span>♙ <?= e(tr('Inovator KVKS · Diploma','KVKS Innovator · Diploma')) ?></span><a href="project.php?slug=<?= rawurlencode($project['slug']) ?>" aria-label="<?= e(tr('Buka projek','Open project')) ?>">→</a></div></div>
    </article>
   <?php endforeach; ?>
  </section>
  <div class="empty-state" id="empty-state" hidden><h2><?= e(tr('Tiada projek ditemui','No projects found')) ?></h2></div>
  <div class="discover-footer"><p class="toolbar-count" id="project-count"><?= count($displayProjects) ?> <?= e(tr('projek dipaparkan','projects shown')) ?></p><div class="discover-pagination"><span class="active">1</span></div><span><?= e(tr('Paparan Grid','Grid View')) ?> ▦</span></div>
  <section class="pm-metrics pm-card"><?php foreach([['rocket',(string)count($displayProjects),tr('Projek Disahkan','Verified Projects'),tr('Hanya projek diterbitkan dipaparkan','Only published projects are shown')],['people','7',tr('Program KVKS','KVKS Programmes'),tr('Kolaborasi merentas program','Cross-programme collaboration')],['award','8',tr('Bidang Penyelesaian','Solution Areas'),tr('Dikumpulkan mengikut keperluan sebenar','Grouped by real-world needs')],['home','1',tr('Ekosistem Inovasi','Innovation Ecosystem'),tr('Satu pentas untuk semua program','One stage for every programme')]] as $i=>$m): ?><div class="pm-metric"><span class="pm-icon <?= $i%2?'orange':'' ?>"><?= ui_icon($m[0]) ?></span><div><strong><?= e($m[1]) ?></strong><span><?= e($m[2]) ?></span><small><?= e($m[3]) ?></small></div></div><?php endforeach; ?></section>
 </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
