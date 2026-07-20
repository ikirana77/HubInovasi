<?php
/** CP10D adaptive, bilingual submission builder with the existing review workflow. */
require_once __DIR__ . '/includes/submission-repository.php';
require_once __DIR__ . '/includes/submission-category-details.php';
require_once __DIR__ . '/includes/submission-impact-details.php';
require_once __DIR__ . '/includes/submission-participants.php';
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
    header('Location: login.php?next=' . rawurlencode($next));
    exit;
}
if ($submission && !empty($submission['owner_user_id']) && (!$user || (int) $submission['owner_user_id'] !== (int) $user['id'])) {
    http_response_code(403);
    exit(tr('Anda tidak mempunyai akses kepada submission ini.', 'You do not have access to this submission.'));
}
if ($submission && !in_array($submission['status'], ['draft', 'needs_revision'], true)) {
    if ($user) {
        $_SESSION['user_flash'] = ['error', tr('Projek ini tidak lagi boleh disunting kerana sedang diproses oleh admin.', 'This project can no longer be edited because it is being processed by the administrator.')];
        header('Location: dashboard/index.php');
        exit;
    }
    http_response_code(403);
    exit(tr('Submission ini tidak lagi boleh disunting.', 'This submission can no longer be edited.'));
}

$requestedStep = (int) ($_POST['current_step'] ?? $_GET['step'] ?? 1);
$initialStep = max(1, min(8, $requestedStep));
$categoryDetails = [];
$impactDetails = ['metrics' => [], 'evidence' => [], 'recognitions' => []];
$participantDetails = ['students' => [], 'mentors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $serverMessage = ['type' => 'error', 'text' => tr('Sesi borang tidak sah. Sila muat semula halaman.', 'The form session is invalid. Reload the page.')];
    } else {
        $payload = submission_payload($_POST);
        $categoryDetails = submission_category_details_from_source($_POST, (string) $payload['solution_area']);
        $impactDetails = submission_impact_payload($_POST);
        $participantDetails = submission_participants_payload($_POST);
        if ($user) {
            $payload['submitter_name'] = $user['full_name'];
            $payload['submitter_email'] = $user['email'];
            if (empty($payload['institution']) && !empty($user['institution'])) {
                $payload['institution'] = $user['institution'];
            }
        }
        $intent = (string) ($_POST['intent'] ?? 'draft');
        $targetStatus = $intent === 'submit_review' ? 'pending_review' : 'draft';
        if ($targetStatus === 'pending_review' && (empty($_POST['consent']) || !submission_is_complete($payload))) {
            $initialStep = 8;
            $serverMessage = ['type' => 'error', 'text' => tr('Lengkapkan semua medan wajib dan pengesahan persetujuan sebelum menghantar untuk semakan.', 'Complete all required fields and consent confirmation before submitting for review.')];
        } elseif ($targetStatus === 'pending_review' && !submission_category_details_are_complete((string) $payload['solution_area'], $categoryDetails)) {
            $initialStep = 3;
            $serverMessage = ['type' => 'error', 'text' => tr('Lengkapkan semua butiran produk mengikut kategori sebelum menghantar untuk semakan.', 'Complete all category-specific product details before submitting for review.')];
        } elseif ($targetStatus === 'pending_review' && submission_impact_validation_errors($impactDetails, true)) {
            $initialStep = 4;
            $serverMessage = ['type' => 'error', 'text' => tr('Lengkapkan metrik dan bukti impak yang sah sebelum menghantar untuk semakan.', 'Complete valid impact metrics and evidence before submitting for review.')];
        } elseif ($targetStatus === 'pending_review' && submission_participants_validation_errors($participantDetails, true)) {
            $initialStep = 5;
            $serverMessage = ['type' => 'error', 'text' => tr('Lengkapkan maklumat pelajar dan mentor dalam Bahagian 05 sebelum menghantar untuk semakan.', 'Complete the student and mentor details in Section 05 before submitting for review.')];
        } else {
            $pdo = db();
            $ownsTransaction = !$pdo->inTransaction();
            $participantFilePlan = ['created' => [], 'obsolete' => []];
            try {
                if ($ownsTransaction) $pdo->beginTransaction();
                $submission = save_submission($payload, $submissionToken ?: null, $targetStatus, $user ? (int) $user['id'] : null);
                save_submission_category_details((int) $submission['id'], (string) $payload['solution_area'], $categoryDetails);
                save_submission_impact_details((int) $submission['id'], $impactDetails);
                $participantFilePlan = save_submission_participants((int) $submission['id'], $participantDetails, $_FILES);
                if ($ownsTransaction) $pdo->commit();
                finalize_submission_profile_files($participantFilePlan, true);
                if ($user && $targetStatus === 'pending_review') {
                    $_SESSION['user_flash'] = ['success', tr('Projek telah dihantar untuk semakan admin.', 'The project has been submitted for administrator review.')];
                    header('Location: dashboard/index.php');
                    exit;
                }
                $redirectStep = $targetStatus === 'draft' ? $initialStep : 8;
                header('Location: submit-project.php?token=' . rawurlencode($submission['public_token']) . '&saved=' . rawurlencode($targetStatus) . '&step=' . $redirectStep);
                exit;
            } catch (Throwable $exception) {
                if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
                finalize_submission_profile_files($participantFilePlan, false);
                error_log('Submission save failed: ' . $exception->getMessage());
                $serverMessage = $exception instanceof SubmissionPhotoException
                    ? ['type' => 'error', 'text' => tr($exception->getMessage(), 'The profile photo could not be processed. Check its format, size, and server image support.')]
                    : ['type' => 'error', 'text' => tr('Submission tidak dapat disimpan. Sila cuba lagi.', 'The submission could not be saved. Please try again.')];
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $serverMessage = $_GET['saved'] === 'pending_review'
        ? ['type' => 'success', 'text' => tr('Submission telah dihantar untuk semakan.', 'The submission has been sent for review.')]
        : ['type' => 'success', 'text' => tr('Draf berjaya disimpan dalam akaun anda.', 'The draft has been saved to your account.')];
}

function submission_value(?array $submission, string $column, string $fallback = ''): string
{
    return e($submission[$column] ?? $fallback);
}

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
$selectedArea = solution_area_slug($_POST['category'] ?? ($submission['solution_area'] ?? ''));
$categoryFieldDefinitions = submission_category_field_definitions();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $submission) {
    $categoryDetails = submission_category_details_for_submission((int) $submission['id']);
    $impactDetails = submission_impact_details_for_submission((int) $submission['id']);
    $participantDetails = submission_participants_for_submission((int) $submission['id']);
}
$studentProgrammeOptions = submission_student_programmes();
$studyYearOptions = submission_study_years();
$developmentOptions = ['Concept and Design Stage', 'Functional Prototype', 'Functional Prototype / Active Development', 'Functional Pilot / Release Candidate', 'Institutional System / Pilot Implementation'];
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
$stepTitles = [
    1 => tr('Identiti Projek', 'Project Identity'),
    2 => tr('Cerita & Nilai Inovasi', 'Innovation Story & Value'),
    3 => tr('Butiran Produk Mengikut Kategori', 'Category-specific Product Details'),
    4 => tr('Impak, Bukti & Anugerah', 'Impact, Evidence & Awards'),
    5 => tr('Peserta / Inovator', 'Participants / Innovators'),
    6 => tr('Bimbingan Mentor', 'Mentorship Details'),
    7 => tr('Media, Galeri & Perjalanan', 'Media, Gallery & Journey'),
    8 => tr('Semakan & Hantar', 'Review & Submit'),
];
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="inner-page submission-page">
    <section class="page-hero page-hero--accent">
        <div class="container page-hero__inner">
            <p class="eyebrow"><?= e(tr('Adaptive Submission Builder • CP10D', 'Adaptive Submission Builder • CP10D')) ?></p>
            <h1><?= tr('Bina cerita projek<br><span>langkah demi langkah.</span>', 'Build your project story<br><span>step by step.</span>') ?></h1>
            <p><?= e(tr('Lapan langkah yang jelas untuk menyediakan projek sebelum semakan pentadbir.', 'Eight clear steps to prepare your project before administrator review.')) ?></p>
        </div>
    </section>

    <aside class="demo-notice" aria-label="<?= e(tr('Makluman penting', 'Important notice')) ?>">
        <div class="container demo-notice__inner">
            <strong><?= $user ? e(tr('Akaun aktif: ', 'Active account: ') . $user['full_name']) : e(tr('Submission legasi berasaskan pautan.', 'Legacy link-based submission.')) ?></strong>
            <p><?= e(tr('Draf disimpan dalam MySQL dan hanya diterbitkan selepas semakan admin.', 'Drafts are stored in MySQL and published only after administrator review.')) ?> <?= $user ? '<a href="dashboard/index.php">' . e(tr('Kembali ke Dashboard Saya', 'Return to My Dashboard')) . ' →</a>' : '' ?></p>
        </div>
    </aside>

    <section class="submission-section" aria-labelledby="submission-title">
        <div class="container submission-layout">
            <aside class="submission-stepper" aria-label="<?= e(tr('Langkah submission', 'Submission steps')) ?>">
                <p class="eyebrow"><?= e(tr('Kemajuan Borang', 'Form Progress')) ?></p>
                <h2 id="submission-title"><?= e(tr('Lengkapkan mengikut rentak anda.', 'Complete it at your own pace.')) ?></h2>
                <ol>
                    <?php foreach ($stepTitles as $number => $title): ?>
                        <li><button type="button" data-step-target="<?= $number ?>" <?= $number === $initialStep ? 'aria-current="step"' : '' ?>><span><?= str_pad((string) $number, 2, '0', STR_PAD_LEFT) ?></span><span><?= e($title) ?></span></button></li>
                    <?php endforeach; ?>
                </ol>
                <p class="submission-stepper__status" aria-live="polite"><?= e(tr('Langkah', 'Step')) ?> <span id="current-step-number"><?= $initialStep ?></span> / 8</p>
            </aside>

            <form id="submission-form" class="pitch-form adaptive-submission-form" method="post" enctype="multipart/form-data" action="submit-project.php<?= $submissionToken ? '?token=' . rawurlencode($submissionToken) : '' ?>" novalidate data-initial-step="<?= $initialStep ?>" data-required-message="<?= e(tr('Lengkapkan medan wajib dalam langkah ini sebelum meneruskan.', 'Complete the required fields in this step before continuing.')) ?>" data-impact-message="<?= e(tr('Tambah sekurang-kurangnya satu metrik bernilai dan satu bukti berpautan.', 'Add at least one valued metric and one linked evidence item.')) ?>" data-participant-message="<?= e(tr('Tambah sekurang-kurangnya seorang pelajar dan lengkapkan semua rekod individu.', 'Add at least one student and complete every person record.')) ?>" data-leader-label="<?= e(tr('Ketua', 'Leader')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="submission_token" value="<?= e($submissionToken) ?>">
                <input type="hidden" name="current_step" id="current-step-input" value="<?= $initialStep ?>">
                <?php if ($serverMessage): ?><div class="form-message <?= $serverMessage['type'] === 'error' ? 'form-message--error' : '' ?>" role="status"><?= e($serverMessage['text']) ?></div><?php endif; ?>

                <?php for ($step = 1; $step <= 8; $step++): ?>
                    <?php require __DIR__ . '/partials/submission/step-' . str_pad((string) $step, 2, '0', STR_PAD_LEFT) . '.php'; ?>
                <?php endfor; ?>

                <div class="adaptive-form-actions">
                    <button class="button button--secondary" id="submission-previous" type="button"><?= e(tr('Sebelumnya', 'Previous')) ?></button>
                    <?php if (($submission['status'] ?? 'draft') === 'draft'): ?><button class="button button--secondary" type="submit" name="intent" value="draft" formnovalidate><?= e(tr('Simpan Draf', 'Save Draft')) ?></button><?php endif; ?>
                    <button class="button button--primary" id="submission-next" type="button"><?= e(tr('Seterusnya', 'Next')) ?></button>
                    <button class="button button--primary" id="submission-review" type="button"><?= e(tr('Semak', 'Review')) ?></button>
                </div>
                <div id="form-message" class="form-message" role="status" aria-live="polite" hidden></div>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
