<?php
/** Pitch Builder dengan draft MySQL dan aliran pending review. */
require_once __DIR__ . '/includes/submission-repository.php';

$submissionToken = trim((string) ($_GET['token'] ?? $_POST['submission_token'] ?? ''));
$submission = $submissionToken !== '' ? find_submission_by_token($submissionToken) : null;
$serverMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $serverMessage = ['type' => 'error', 'text' => 'Sesi borang tidak sah. Sila muat semula halaman.'];
    } else {
        $payload = submission_payload($_POST);
        $intent = (string) ($_POST['intent'] ?? 'draft');
        $targetStatus = $intent === 'submit_review' ? 'pending_review' : 'draft';

        if ($targetStatus === 'pending_review' && !submission_is_complete($payload)) {
            $serverMessage = ['type' => 'error', 'text' => 'Lengkapkan semua medan wajib dan email yang sah sebelum menghantar untuk semakan.'];
        } else {
            try {
                $submission = save_submission($payload, $submissionToken ?: null, $targetStatus);
                header('Location: submit-project.php?token=' . rawurlencode($submission['public_token']) . '&saved=' . rawurlencode($targetStatus));
                exit;
            } catch (Throwable $exception) {
                error_log('Submission save failed: ' . $exception->getMessage());
                $serverMessage = ['type' => 'error', 'text' => 'Submission tidak dapat disimpan. Sila cuba lagi.'];
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $serverMessage = $_GET['saved'] === 'pending_review'
        ? ['type' => 'success', 'text' => 'Submission telah dihantar dan kini berstatus pending review.']
        : ['type' => 'success', 'text' => 'Draft berjaya disimpan. Simpan URL halaman ini untuk menyambung kemudian.'];
}

function submission_value(?array $submission, string $column, string $fallback = ''): string
{
    return e($submission[$column] ?? $fallback);
}

$pageTitle = 'Hantar Projek';
$activePage = 'submit';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="inner-page submission-page">
    <section class="page-hero page-hero--accent">
        <div class="container page-hero__inner">
            <p class="eyebrow">Pitch Builder • Mod Demo</p>
            <h1>Bentuk cerita<br><span>projek anda.</span></h1>
            <p>Susun masalah, penyelesaian dan bukti sebelum projek diterbitkan sebagai cerita produk HubInovasi.</p>
        </div>
    </section>

    <aside class="demo-notice" aria-label="Makluman penting">
        <div class="container demo-notice__inner">
            <strong>Aliran semakan aktif.</strong>
            <p>Draft disimpan dalam MySQL dan hanya boleh diterbitkan selepas semakan admin. Tiada submission dipaparkan secara automatik.</p>
        </div>
    </aside>

    <section class="submission-section" aria-labelledby="submission-title">
        <div class="container submission-layout">
            <aside class="pitch-guide">
                <p class="eyebrow">Urutan Pitch</p>
                <h2 id="submission-title">Mulakan dengan sebab projek ini penting.</h2>
                <ol>
                    <li><span>01</span> Identiti</li>
                    <li><span>02</span> Masalah</li>
                    <li><span>03</span> Penyelesaian</li>
                    <li><span>04</span> Cara Ia Berfungsi</li>
                    <li><span>05</span> Impak &amp; Bukti</li>
                    <li><span>06</span> Pasukan &amp; Perjalanan</li>
                </ol>
                <p class="pitch-guide__tip"><strong>Tip:</strong> gunakan bahasa aktif, ayat pendek dan hasil yang boleh difahami sebelum menyebut teknologi.</p>
            </aside>

            <form id="submission-form" class="pitch-form" method="post" action="submit-project.php<?= $submissionToken ? '?token=' . rawurlencode($submissionToken) : '' ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="submission_token" value="<?= e($submissionToken) ?>">
                <?php if ($serverMessage): ?>
                    <div class="form-message <?= $serverMessage['type'] === 'error' ? 'form-message--error' : '' ?>" role="status"><?= e($serverMessage['text']) ?></div>
                <?php endif; ?>
                <fieldset class="form-section">
                    <legend><span>01</span> Identiti Projek</legend>
                    <div class="form-row">
                        <label class="form-field"><span>Nama penuh <em>*</em></span><input type="text" name="name" value="<?= submission_value($submission, 'submitter_name') ?>" autocomplete="name" required></label>
                        <label class="form-field"><span>Email <em>*</em></span><input type="email" name="email" value="<?= submission_value($submission, 'submitter_email') ?>" autocomplete="email" required></label>
                    </div>
                    <div class="form-row">
                        <label class="form-field"><span>Nama projek <em>*</em></span><input type="text" name="project_name" value="<?= submission_value($submission, 'project_name') ?>" required maxlength="80"></label>
                        <label class="form-field"><span>Institusi / kolej</span><input type="text" name="institution" value="<?= submission_value($submission, 'institution', 'Kolej Vokasional Kuala Selangor') ?>"></label>
                    </div>
                    <div class="form-row">
                        <label class="form-field">
                            <span>Bidang penyelesaian <em>*</em></span>
                            <select name="category" required>
                                <option value="">Pilih bidang</option>
                                <?php foreach (['Kehidupan Kampus','Komuniti & Kesejahteraan','Pembelajaran Masa Hadapan','Bidang lain'] as $area): ?>
                                    <option <?= ($submission['solution_area'] ?? '') === $area ? 'selected' : '' ?>><?= e($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Tahap pembangunan <em>*</em></span>
                            <select name="development_status" required>
                                <option value="">Pilih tahap</option>
                                <?php foreach (['Concept and Design Stage','Functional Prototype','Functional Prototype / Active Development','Functional Pilot / Release Candidate','Institutional System / Pilot Implementation'] as $developmentStatus): ?>
                                    <option <?= ($submission['project_development_status'] ?? '') === $developmentStatus ? 'selected' : '' ?>><?= e($developmentStatus) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="form-row">
                        <label class="form-field"><span>Tagline berorientasikan manfaat <em>*</em></span><input type="text" name="tagline" value="<?= submission_value($submission, 'tagline') ?>" required maxlength="120" placeholder="Contoh: Kehadiran lebih pantas dan mudah dijejak."></label>
                    </div>
                </fieldset>

                <fieldset class="form-section">
                    <legend><span>02</span> Masalah</legend>
                    <label class="form-field">
                        <span>Apakah masalah sebenar yang berlaku? <em>*</em></span>
                        <textarea name="problem" rows="5" required minlength="30" maxlength="700" aria-describedby="problem-help"><?= submission_value($submission, 'problem') ?></textarea>
                        <small id="problem-help">Nyatakan siapa yang terkesan, keadaan semasa dan mengapa ia penting.</small>
                    </label>
                </fieldset>

                <fieldset class="form-section">
                    <legend><span>03</span> Penyelesaian</legend>
                    <label class="form-field">
                        <span>Bagaimanakah projek menyelesaikan masalah itu? <em>*</em></span>
                        <textarea name="solution" rows="5" required minlength="30" maxlength="700"><?= submission_value($submission, 'solution') ?></textarea>
                    </label>
                    <label class="form-field">
                        <span>Cara ia berfungsi <em>*</em></span>
                        <textarea name="how_it_works" rows="4" required minlength="20" maxlength="600" placeholder="Terangkan aliran pengguna dalam beberapa langkah ringkas."><?= submission_value($submission, 'how_it_works') ?></textarea>
                    </label>
                    <label class="form-field">
                        <span>Ciri utama</span>
                        <textarea name="features" rows="3" maxlength="500" placeholder="Satu ciri pada setiap baris."><?= submission_value($submission, 'key_features') ?></textarea>
                    </label>
                </fieldset>

                <fieldset class="form-section">
                    <legend><span>04</span> Impak &amp; Bukti</legend>
                    <label class="form-field">
                        <span>Apakah hasil atau impak yang dijangka? <em>*</em></span>
                        <textarea name="impact" rows="5" required minlength="20" maxlength="700"><?= submission_value($submission, 'impact') ?></textarea>
                    </label>
                    <label class="form-field">
                        <span>Bukti yang tersedia</span>
                        <select name="evidence_status">
                            <?php foreach (['Belum diuji','Maklum balas awal','Ujian prototaip','Data rintis'] as $evidenceStatus): ?>
                                <option <?= ($submission['evidence_status'] ?? '') === $evidenceStatus ? 'selected' : '' ?>><?= e($evidenceStatus) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="form-field"><span>Ringkasan bukti</span><textarea name="evidence" rows="3" maxlength="500" placeholder="Labelkan anggaran, sasaran dan data sebenar dengan jelas."><?= submission_value($submission, 'evidence_summary') ?></textarea></label>
                </fieldset>

                <fieldset class="form-section">
                    <legend><span>05</span> Sokongan Projek</legend>
                    <label class="form-field"><span>Teknologi / alat</span><input type="text" name="technologies" value="<?= submission_value($submission, 'technologies') ?>" placeholder="Contoh: Flutter, Firebase, QR Code"></label>
                    <label class="form-field"><span>Pasukan dan peranan</span><textarea name="team" rows="3" maxlength="500" placeholder="Nama dan sumbangan setiap ahli."><?= submission_value($submission, 'team_details') ?></textarea></label>
                    <label class="form-field"><span>Perjalanan projek</span><textarea name="journey" rows="3" maxlength="500" placeholder="Milestone yang telah dicapai dan langkah seterusnya."><?= submission_value($submission, 'project_journey') ?></textarea></label>
                    <label class="form-field">
                        <span>Tindakan yang anda mahu daripada pelawat <em>*</em></span>
                        <select name="call_to_action" required>
                            <option value="">Pilih tindakan utama</option>
                            <?php foreach (['Tonton demonstrasi projek','Muat turun bahan projek','Hubungi pasukan projek','Sokong pembangunan seterusnya'] as $cta): ?>
                                <option value="<?= e($cta) ?>" <?= ($submission['call_to_action'] ?? '') === $cta ? 'selected' : '' ?>><?= e($cta) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </fieldset>

                <label class="form-check">
                    <input type="checkbox" name="consent" required>
                    <span>Saya bersetuju maklumat ini disimpan sebagai submission dan disemak oleh admin HubInovasi. <em>*</em></span>
                </label>

                <div class="form-actions">
                    <button class="button button--secondary" type="submit" name="intent" value="draft" formnovalidate>Simpan Draft</button>
                    <button class="button button--primary" type="submit" name="intent" value="submit_review">Hantar untuk Semakan</button>
                    <button class="button button--text" id="preview-pitch" type="button">Pratonton Pitch</button>
                </div>
                <div id="form-message" class="form-message" role="status" aria-live="polite" hidden></div>
            </form>
        </div>
    </section>

    <section id="pitch-preview" class="pitch-preview" aria-labelledby="pitch-preview-title" hidden>
        <div class="container pitch-preview__inner">
            <div class="pitch-preview__heading"><p class="eyebrow">Pratonton Tempatan</p><h2 id="pitch-preview-title">Ringkasan pitch anda</h2></div>
            <div class="pitch-preview__story">
                <header><p id="preview-category"></p><h3 id="preview-name"></h3><p id="preview-tagline"></p></header>
                <article><span>01</span><div><h4>Masalah</h4><p id="preview-problem"></p></div></article>
                <article><span>02</span><div><h4>Penyelesaian</h4><p id="preview-solution"></p></div></article>
                <article><span>03</span><div><h4>Cara Ia Berfungsi</h4><p id="preview-process"></p></div></article>
                <article data-preview-optional="features"><span>04</span><div><h4>Ciri Utama</h4><p id="preview-features"></p></div></article>
                <article><span>05</span><div><h4>Impak</h4><p id="preview-impact"></p><small id="preview-evidence"></small></div></article>
                <article data-preview-optional="technologies"><span>06</span><div><h4>Teknologi</h4><p id="preview-technologies"></p></div></article>
                <article data-preview-optional="team"><span>07</span><div><h4>Pasukan</h4><p id="preview-team"></p></div></article>
                <article data-preview-optional="journey"><span>08</span><div><h4>Perjalanan Projek</h4><p id="preview-journey"></p></div></article>
                <article class="pitch-preview__cta"><span>09</span><div><h4>Langkah Seterusnya</h4><p id="preview-cta"></p></div></article>
            </div>
            <p class="pitch-preview__notice">Pratonton ini dijana daripada kandungan borang semasa dan tidak mengubah status submission.</p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
