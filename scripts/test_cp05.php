<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/project-repository.php';
require_once __DIR__ . '/../includes/submission-repository.php';

$pdo = db();
$pdo->beginTransaction();

try {
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "[PASS] Database connected: MySQL {$version}\n";

    $adminEmail = 'cp05-admin-' . bin2hex(random_bytes(4)) . '@example.test';
    $adminStmt = $pdo->prepare("INSERT INTO admin_users (full_name,email,password_hash,role,is_active) VALUES (?,?,?,'admin',1)");
    $adminStmt->execute(['CP05 Test Admin', $adminEmail, password_hash('Temporary-Test-Password-123!', PASSWORD_DEFAULT)]);
    $adminId = (int) $pdo->lastInsertId();

    $payload = array_fill_keys(SUBMISSION_FIELDS, null);
    $payload = array_merge($payload, [
        'submitter_name' => 'CP05 Test User',
        'submitter_email' => 'cp05-test@example.test',
        'project_name' => 'CP05 Transaction Test',
        'solution_area' => 'Kehidupan Kampus',
        'project_development_status' => 'Functional Prototype',
        'tagline' => 'Ujian transaksi tanpa data kekal.',
        'problem' => 'Ujian memerlukan pengesahan operasi create, read dan update.',
        'solution' => 'Gunakan transaksi MySQL dan rollback selepas semua assertion selesai.',
        'how_it_works' => 'Cipta draft, baca token, kemas kini status dan rollback.',
        'impact' => 'Mengesahkan workflow tanpa meninggalkan submission ujian.',
        'call_to_action' => 'Hubungi pasukan projek',
    ]);

    $draft = save_submission($payload, null, 'draft');
    if (($draft['status'] ?? null) !== 'draft') throw new RuntimeException('Draft create gagal.');
    echo "[PASS] Submission created as draft\n";

    $read = find_submission_by_token($draft['public_token']);
    if (($read['project_name'] ?? null) !== 'CP05 Transaction Test') throw new RuntimeException('Submission read gagal.');
    echo "[PASS] Submission read by secure token\n";

    $pending = save_submission($payload, $draft['public_token'], 'pending_review');
    if (($pending['status'] ?? null) !== 'pending_review') throw new RuntimeException('Pending update gagal.');
    echo "[PASS] Submission updated to pending_review\n";

    if (!admin_update_submission((int) $pending['id'], 'needs_revision', 'Automated CP05 test', $adminId)) throw new RuntimeException('Admin update gagal.');
    $reviewed = find_submission_by_token($draft['public_token']);
    if (($reviewed['status'] ?? null) !== 'needs_revision') throw new RuntimeException('Admin status readback gagal.');
    echo "[PASS] Admin changed status to needs_revision\n";

    $public = get_public_projects();
    $slugs = array_column($public, 'slug');
    if ($slugs !== ['hers']) throw new RuntimeException('Public visibility expected only HERS, got: ' . implode(', ', $slugs));
    echo "[PASS] Public catalogue contains published HERS only\n";

    if (in_array('visionlab', $slugs, true)) throw new RuntimeException('VisionLab leaked to public catalogue.');
    echo "[PASS] Unverified VisionLab is hidden\n";

    $hers = get_public_project_by_slug('hers');
    if (!$hers || empty($hers['problem']) || empty($hers['solution'])) throw new RuntimeException('HERS pitch database read gagal.');
    if (!empty($hers['links'])) throw new RuntimeException('HERS must not expose unprovided CTA links.');
    echo "[PASS] HERS pitch loaded from database with no dummy CTA\n";

    $pending = save_submission($payload, $draft['public_token'], 'pending_review');
    if (!admin_update_submission((int) $pending['id'], 'published', 'Automated publication test', $adminId)) throw new RuntimeException('Admin publication gagal.');
    $publishedSubmission = find_submission_by_token($draft['public_token']);
    if (empty($publishedSubmission['linked_project_id'])) throw new RuntimeException('Published submission tidak dipautkan kepada projek.');
    $publishedProject = $pdo->prepare("SELECT review_status FROM projects WHERE id = ?");
    $publishedProject->execute([$publishedSubmission['linked_project_id']]);
    if ($publishedProject->fetchColumn() !== 'published') throw new RuntimeException('Project publication gagal.');
    echo "[PASS] Admin publication creates and links a published project\n";

    $pdo->rollBack();
    echo "[PASS] Test transaction rolled back\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "[FAIL] {$exception->getMessage()}\n");
    exit(1);
}
