<?php
/** Bilingual Pitch Builder with ownership and administrator review workflow. */
require_once __DIR__ . '/includes/submission-repository.php';
require_once __DIR__ . '/includes/user-auth.php';

$user = current_user();
$submissionToken = trim((string) ($_GET['token'] ?? $_POST['submission_token'] ?? ''));
$submission = $submissionToken !== '' ? find_submission_by_token($submissionToken) : null;
$serverMessage = null;

if ($submissionToken !== '' && !$submission) {
    http_response_code(404);
    exit(tr('Submission tidak dijumpai.', 'Submission not found.'));
}
if (!$user && (!$submission || !empty($submission['owner_user_id']))) {
    $next = (string) ($_SERVER['REQUEST_URI'] ?? 'submit-project.php');
    header('Location: login.php?next=' . rawurlencode($next)); exit;
}
if ($submission && !empty($submission['owner_user_id']) && (!$user || (int) $submission['owner_user_id'] !== (int) $user['id'])) {
    http_response_code(403); exit(tr('Anda tidak mempunyai akses kepada submission ini.', 'You do not have access to this submission.'));
}
if ($submission && !in_array($submission['status'], ['draft','needs_revision'], true)) {
    if ($user) {
        $_SESSION['user_flash'] = ['error', tr('Projek ini tidak lagi boleh disunting kerana sedang diproses oleh admin.', 'This project can no longer be edited because it is being processed by the administrator.')];
        header('Location: dashboard/index.php'); exit;
    }
    http_response_code(403); exit(tr('Submission ini tidak lagi boleh disunting.', 'This submission can no longer be edited.'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $serverMessage = ['type' => 'error', 'text' => tr('Sesi borang tidak sah. Sila muat semula halaman.', 'The form session is invalid. Reload the page.')];
    } else {
        $payload = submission_payload($_POST);
        if ($user) {
            $payload['submitter_name'] = $user['full_name'];
            $payload['submitter_email'] = $user['email'];
            if (empty($payload['institution']) && !empty($user['institution'])) $payload['institution'] = $user['institution'];
        }
        $intent = (string) ($_POST['intent'] ?? 'draft');
        $targetStatus = $intent === 'submit_review' ? 'pending_review' : 'draft';
        if ($targetStatus === 'pending_review' && (empty($_POST['consent']) || !submission_is_complete($payload))) {
            $serverMessage = ['type' => 'error', 'text' => tr('Lengkapkan semua medan wajib dan pengesahan persetujuan sebelum menghantar untuk semakan.', 'Complete all required fields and consent confirmation before submitting for review.')];
        } else {
            try {
                $submission = save_submission($payload, $submissionToken ?: null, $targetStatus, $user ? (int) $user['id'] : null);
                if ($user && $targetStatus === 'pending_review') {
                    $_SESSION['user_flash'] = ['success', tr('Projek telah dihantar untuk semakan admin.', 'The project has been submitted for administrator review.')];
                    header('Location: dashboard/index.php'); exit;
                }
                header('Location: submit-project.php?token=' . rawurlencode($submission['public_token']) . '&saved=' . rawurlencode($targetStatus)); exit;
            } catch (Throwable $exception) {
                error_log('Submission save failed: ' . $exception->getMessage());
                $serverMessage = ['type' => 'error', 'text' => tr('Submission tidak dapat disimpan. Sila cuba lagi.', 'The submission could not be saved. Please try again.')];
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $serverMessage = $_GET['saved'] === 'pending_review'
        ? ['type' => 'success', 'text' => tr('Submission telah dihantar untuk semakan.', 'The submission has been sent for review.')]
        : ['type' => 'success', 'text' => tr('Draf berjaya disimpan dalam akaun anda.', 'The draft has been saved to your account.')];
}

function submission_value(?array $submission, string $column, string $fallback = ''): string { return e($submission[$column] ?? $fallback); }

$defaultName = $submission['submitter_name'] ?? ($user['full_name'] ?? '');
$defaultEmail = $submission['submitter_email'] ?? ($user['email'] ?? '');
$defaultInstitution = $submission['institution'] ?? ($user['institution'] ?? 'Kolej Vokasional Kuala Selangor');
$pageTitle = $submission ? tr('Kemas Kini Projek', 'Update Project') : tr('Hantar Projek', 'Submit Project');
$activePage = 'submit';
$areaOptions = array_map(static fn (array $area): string => $area['name'], hub_solution_areas());
$programmeOptions = hub_programmes();
$innovationOptions = hub_innovation_types();
$selectedProgrammes = decode_programme_codes($submission['programme_codes'] ?? null);
$selectedLeadProgramme = $selectedProgrammes[0] ?? '';
$selectedContributingProgrammes = array_slice($selectedProgrammes, 1);
$selectedArea = solution_area_slug($submission['solution_area'] ?? '');
$developmentOptions = ['Concept and Design Stage','Functional Prototype','Functional Prototype / Active Development','Functional Pilot / Release Candidate','Institutional System / Pilot Implementation'];
$evidenceOptions = [
    'Belum diuji' => tr('Belum diuji', 'Not tested yet'),
    'Maklum balas awal' => tr('Maklum balas awal', 'Early feedback'),
    'Ujian prototaip' => tr('Ujian prototaip', 'Prototype testing'),
    'Data rintis' => tr('Data rintis', 'Pilot data'),
];
$ctaOptions = [
    'Tonton demonstrasi projek' => tr('Tonton demonstrasi projek', 'Watch the project demonstration'),
    'Muat turun bahan projek' => tr('Muat turun bahan projek', 'Download project materials'),
    'Hubungi pasukan projek' => tr('Hubungi pasukan projek', 'Contact the project team'),
    'Sokong pembangunan seterusnya' => tr('Sokong pembangunan seterusnya', 'Support the next development stage'),
];
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="inner-page submission-page">
    <section class="page-hero page-hero--accent"><div class="container page-hero__inner"><p class="eyebrow"><?= e(tr('Pitch Builder • Submission Rasmi', 'Pitch Builder • Official Submission')) ?></p><h1><?= tr('Bentuk cerita<br><span>projek anda.</span>', 'Shape the story of<br><span>your project.</span>') ?></h1><p><?= e(tr('Susun masalah, penyelesaian dan bukti sebelum projek diterbitkan sebagai cerita produk HubInovasi.', 'Structure the problem, solution and evidence before the project is published as a HubInovasi product story.')) ?></p></div></section>

    <aside class="demo-notice" aria-label="<?= e(tr('Makluman penting', 'Important notice')) ?>"><div class="container demo-notice__inner"><strong><?= $user ? e(tr('Akaun aktif: ', 'Active account: ') . $user['full_name']) : e(tr('Submission legasi berasaskan pautan.', 'Legacy link-based submission.')) ?></strong><p><?= e(tr('Draf disimpan dalam MySQL dan hanya diterbitkan selepas semakan admin.', 'Drafts are stored in MySQL and published only after administrator review.')) ?> <?= $user ? '<a href="dashboard/index.php">' . e(tr('Kembali ke Dashboard Saya', 'Return to My Dashboard')) . ' →</a>' : '' ?></p></div></aside>

    <section class="submission-section" aria-labelledby="submission-title"><div class="container submission-layout">
        <aside class="pitch-guide"><p class="eyebrow"><?= e(tr('Urutan Pitch', 'Pitch Sequence')) ?></p><h2 id="submission-title"><?= e(tr('Mulakan dengan sebab projek ini penting.', 'Begin with why this project matters.')) ?></h2><ol><li><span>01</span> <?= e(tr('Identiti', 'Identity')) ?></li><li><span>02</span> <?= e(tr('Masalah', 'Problem')) ?></li><li><span>03</span> <?= e(tr('Penyelesaian', 'Solution')) ?></li><li><span>04</span> <?= e(tr('Cara Ia Berfungsi', 'How It Works')) ?></li><li><span>05</span> <?= e(tr('Impak & Bukti', 'Impact & Evidence')) ?></li><li><span>06</span> <?= e(tr('Pasukan & Perjalanan', 'Team & Journey')) ?></li></ol><p class="pitch-guide__tip"><strong><?= e(tr('Tip:', 'Tip:')) ?></strong> <?= e(tr('gunakan bahasa aktif, ayat pendek dan hasil yang boleh difahami sebelum menyebut teknologi.', 'use active language, short sentences and clear outcomes before mentioning technology.')) ?></p></aside>

        <form id="submission-form" class="pitch-form" method="post" action="submit-project.php<?= $submissionToken ? '?token=' . rawurlencode($submissionToken) : '' ?>" novalidate
              data-evidence-prefix="<?= e(tr('Status bukti:', 'Evidence status:')) ?>"
              data-preview-message="<?= e(tr('Pratonton dijana daripada borang semasa. Simpan draf untuk menyimpan perubahan ke MySQL.', 'The preview was generated from the current form. Save the draft to store changes in MySQL.')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="submission_token" value="<?= e($submissionToken) ?>">
            <?php if ($serverMessage): ?><div class="form-message <?= $serverMessage['type'] === 'error' ? 'form-message--error' : '' ?>" role="status"><?= e($serverMessage['text']) ?></div><?php endif; ?>

            <fieldset class="form-section"><legend><span>01</span> <?= e(tr('Identiti Projek', 'Project Identity')) ?></legend>
                <div class="form-row"><label class="form-field"><span><?= e(tr('Nama penuh', 'Full name')) ?> <em>*</em></span><input type="text" name="name" value="<?= e($defaultName) ?>" autocomplete="name" required <?= $user ? 'readonly' : '' ?>></label><label class="form-field"><span>Email <em>*</em></span><input type="email" name="email" value="<?= e($defaultEmail) ?>" autocomplete="email" required <?= $user ? 'readonly' : '' ?>></label></div>
                <div class="form-row"><label class="form-field"><span><?= e(tr('Nama projek', 'Project name')) ?> <em>*</em></span><input type="text" name="project_name" value="<?= submission_value($submission, 'project_name') ?>" required maxlength="80"></label><label class="form-field"><span><?= e(tr('Institusi / kolej', 'Institution / college')) ?></span><input type="text" name="institution" value="<?= e($defaultInstitution) ?>"></label></div>
                <div class="form-row">
                    <label class="form-field"><span><?= e(tr('Bidang penyelesaian utama', 'Primary solution area')) ?> <em>*</em></span><select name="category" required><option value=""><?= e(tr('Pilih bidang', 'Select an area')) ?></option><?php foreach ($areaOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= $selectedArea === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
                    <label class="form-field"><span><?= e(tr('Tahap pembangunan', 'Development stage')) ?> <em>*</em></span><select name="development_status" required><option value=""><?= e(tr('Pilih tahap', 'Select a stage')) ?></option><?php foreach ($developmentOptions as $developmentStatus): ?><option value="<?= e($developmentStatus) ?>" <?= ($submission['project_development_status'] ?? '') === $developmentStatus ? 'selected' : '' ?>><?= e($developmentStatus) ?></option><?php endforeach; ?></select></label>
                </div>
                <div class="form-row"><label class="form-field"><span><?= e(tr('Jenis inovasi', 'Innovation type')) ?> <em>*</em></span><select name="innovation_type" required><option value=""><?= e(tr('Pilih jenis inovasi', 'Select an innovation type')) ?></option><?php foreach ($innovationOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= ($submission['innovation_type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label></div>
                <fieldset class="programme-selector"><legend><?= e(tr('Program projek', 'Project programmes')) ?> <em>*</em></legend><label class="form-field"><span><?= e(tr('Program peneraju', 'Lead programme')) ?> <em>*</em></span><select name="lead_programme" required><option value=""><?= e(tr('Pilih program peneraju','Select the lead programme')) ?></option><?php foreach($programmeOptions as $code=>$label): ?><option value="<?= e($code) ?>" <?= $selectedLeadProgramme===$code?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><p><?= e(tr('Jika projek dibangunkan secara kolaboratif, pilih program penyumbang tambahan di bawah.','For a collaborative project, select any additional contributing programmes below.')) ?></p><div><?php foreach($programmeOptions as $code=>$label): ?><label><input type="checkbox" name="programmes[]" value="<?= e($code) ?>" <?= in_array($code,$selectedContributingProgrammes,true)?'checked':'' ?>><span><strong><?= e($code) ?></strong><?= e(preg_replace('/^[A-Z]{3} — /','',$label)) ?></span></label><?php endforeach; ?></div></fieldset>
                <div class="form-row"><label class="form-field"><span><?= e(tr('Tagline berorientasikan manfaat', 'Benefit-led tagline')) ?> <em>*</em></span><input type="text" name="tagline" value="<?= submission_value($submission, 'tagline') ?>" required maxlength="120" placeholder="<?= e(tr('Contoh: Kehadiran lebih pantas dan mudah dijejak.', 'Example: Faster attendance that is easier to trace.')) ?>"></label></div>
            </fieldset>

            <fieldset class="form-section"><legend><span>02</span> <?= e(tr('Masalah', 'Problem')) ?></legend><label class="form-field"><span><?= e(tr('Apakah masalah sebenar yang berlaku?', 'What real problem is occurring?')) ?> <em>*</em></span><textarea name="problem" rows="5" required minlength="30" maxlength="700" aria-describedby="problem-help"><?= submission_value($submission, 'problem') ?></textarea><small id="problem-help"><?= e(tr('Nyatakan siapa yang terkesan, keadaan semasa dan mengapa ia penting.', 'State who is affected, the current situation and why it matters.')) ?></small></label></fieldset>

            <fieldset class="form-section"><legend><span>03</span> <?= e(tr('Penyelesaian', 'Solution')) ?></legend>
                <label class="form-field"><span><?= e(tr('Bagaimanakah projek menyelesaikan masalah itu?', 'How does the project solve the problem?')) ?> <em>*</em></span><textarea name="solution" rows="5" required minlength="30" maxlength="700"><?= submission_value($submission, 'solution') ?></textarea></label>
                <label class="form-field"><span><?= e(tr('Cara ia berfungsi', 'How it works')) ?> <em>*</em></span><textarea name="how_it_works" rows="4" required minlength="20" maxlength="600" placeholder="<?= e(tr('Terangkan aliran pengguna dalam beberapa langkah ringkas.', 'Explain the user flow in a few concise steps.')) ?>"><?= submission_value($submission, 'how_it_works') ?></textarea></label>
                <label class="form-field"><span><?= e(tr('Ciri utama', 'Key features')) ?></span><textarea name="features" rows="3" maxlength="500" placeholder="<?= e(tr('Satu ciri pada setiap baris.', 'One feature per line.')) ?>"><?= submission_value($submission, 'key_features') ?></textarea></label>
            </fieldset>

            <fieldset class="form-section"><legend><span>04</span> <?= e(tr('Impak & Bukti', 'Impact & Evidence')) ?></legend>
                <label class="form-field"><span><?= e(tr('Apakah hasil atau impak yang dijangka?', 'What outcome or impact is expected?')) ?> <em>*</em></span><textarea name="impact" rows="5" required minlength="20" maxlength="700"><?= submission_value($submission, 'impact') ?></textarea></label>
                <label class="form-field"><span><?= e(tr('Bukti yang tersedia', 'Available evidence')) ?></span><select name="evidence_status"><?php foreach ($evidenceOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= ($submission['evidence_status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
                <label class="form-field"><span><?= e(tr('Ringkasan bukti', 'Evidence summary')) ?></span><textarea name="evidence" rows="3" maxlength="500" placeholder="<?= e(tr('Labelkan anggaran, sasaran dan data sebenar dengan jelas.', 'Clearly label estimates, targets and verified data.')) ?>"><?= submission_value($submission, 'evidence_summary') ?></textarea></label>
            </fieldset>

            <fieldset class="form-section"><legend><span>05</span> <?= e(tr('Sokongan Projek', 'Project Support')) ?></legend>
                <label class="form-field"><span><?= e(tr('Teknologi / alat', 'Technologies / tools')) ?></span><input type="text" name="technologies" value="<?= submission_value($submission, 'technologies') ?>" placeholder="<?= e(tr('Contoh: Flutter, Firebase, QR Code', 'Example: Flutter, Firebase, QR Code')) ?>"></label>
                <label class="form-field"><span><?= e(tr('Pasukan dan peranan', 'Team and roles')) ?></span><textarea name="team" rows="3" maxlength="500" placeholder="<?= e(tr('Nama dan sumbangan setiap ahli.', 'The name and contribution of each member.')) ?>"><?= submission_value($submission, 'team_details') ?></textarea></label>
                <label class="form-field"><span><?= e(tr('Perjalanan projek', 'Project journey')) ?></span><textarea name="journey" rows="3" maxlength="500" placeholder="<?= e(tr('Milestone yang telah dicapai dan langkah seterusnya.', 'Milestones achieved and the next steps.')) ?>"><?= submission_value($submission, 'project_journey') ?></textarea></label>
                <label class="form-field"><span><?= e(tr('Tindakan yang anda mahu daripada pelawat', 'Action you want visitors to take')) ?> <em>*</em></span><select name="call_to_action" required><option value=""><?= e(tr('Pilih tindakan utama', 'Select the primary action')) ?></option><?php foreach ($ctaOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= ($submission['call_to_action'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
            </fieldset>

            <label class="form-check"><input type="checkbox" name="consent" required><span><?= e(tr('Saya bersetuju maklumat ini disimpan sebagai submission dan disemak oleh admin HubInovasi.', 'I agree that this information may be stored as a submission and reviewed by the HubInovasi administrator.')) ?> <em>*</em></span></label>
            <div class="form-actions"><?php if (($submission['status'] ?? 'draft') === 'draft'): ?><button class="button button--secondary" type="submit" name="intent" value="draft" formnovalidate><?= e(tr('Simpan Draf', 'Save Draft')) ?></button><?php endif; ?><button class="button button--primary" type="submit" name="intent" value="submit_review"><?= e(tr('Hantar untuk Semakan', 'Submit for Review')) ?></button><button class="button button--text" id="preview-pitch" type="button"><?= e(tr('Pratonton Pitch', 'Preview Pitch')) ?></button></div>
            <div id="form-message" class="form-message" role="status" aria-live="polite" hidden></div>
        </form>
    </div></section>

    <section id="pitch-preview" class="pitch-preview" aria-labelledby="pitch-preview-title" hidden><div class="container pitch-preview__inner"><div class="pitch-preview__heading"><p class="eyebrow"><?= e(tr('Pratonton Tempatan', 'Local Preview')) ?></p><h2 id="pitch-preview-title"><?= e(tr('Ringkasan pitch anda', 'Your pitch summary')) ?></h2></div><div class="pitch-preview__story"><header><p id="preview-category"></p><h3 id="preview-name"></h3><p id="preview-tagline"></p></header><article><span>01</span><div><h4><?= e(tr('Masalah', 'Problem')) ?></h4><p id="preview-problem"></p></div></article><article><span>02</span><div><h4><?= e(tr('Penyelesaian', 'Solution')) ?></h4><p id="preview-solution"></p></div></article><article><span>03</span><div><h4><?= e(tr('Cara Ia Berfungsi', 'How It Works')) ?></h4><p id="preview-process"></p></div></article><article data-preview-optional="features"><span>04</span><div><h4><?= e(tr('Ciri Utama', 'Key Features')) ?></h4><p id="preview-features"></p></div></article><article><span>05</span><div><h4><?= e(tr('Impak', 'Impact')) ?></h4><p id="preview-impact"></p><small id="preview-evidence"></small></div></article><article data-preview-optional="technologies"><span>06</span><div><h4><?= e(tr('Teknologi', 'Technology')) ?></h4><p id="preview-technologies"></p></div></article><article data-preview-optional="team"><span>07</span><div><h4><?= e(tr('Pasukan', 'Team')) ?></h4><p id="preview-team"></p></div></article><article data-preview-optional="journey"><span>08</span><div><h4><?= e(tr('Perjalanan Projek', 'Project Journey')) ?></h4><p id="preview-journey"></p></div></article><article class="pitch-preview__cta"><span>09</span><div><h4><?= e(tr('Langkah Seterusnya', 'Next Step')) ?></h4><p id="preview-cta"></p></div></article></div><p class="pitch-preview__notice"><?= e(tr('Pratonton ini dijana daripada kandungan borang semasa dan tidak mengubah status submission.', 'This preview is generated from the current form and does not change the submission status.')) ?></p></div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
