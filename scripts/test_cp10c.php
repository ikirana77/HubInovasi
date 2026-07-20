<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/submission-repository.php';
require_once __DIR__ . '/../includes/submission-impact-details.php';

function cp10c_check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo "[PASS] {$message}\n";
}

$pdo = db();
$pdo->beginTransaction();
try {
    $payload = array_fill_keys(SUBMISSION_FIELDS, null);
    $payload = array_merge($payload, [
        'submitter_name' => 'CP10C Tester',
        'submitter_email' => 'cp10c-' . bin2hex(random_bytes(4)) . '@example.test',
        'project_name' => 'CP10C Impact Project',
        'institution' => 'KVKS',
        'solution_area' => 'smart-campus-safety-operations',
        'innovation_type' => 'digital-solution',
        'programme_codes' => json_encode(['KPD']),
        'project_development_status' => 'Functional Prototype',
        'tagline' => 'Impak yang boleh diukur dan disahkan.',
        'problem' => 'Pasukan memerlukan kaedah tersusun untuk merekod hasil dan bukti inovasi.',
        'solution' => 'Koleksi metrik dan bukti disimpan sebagai rekod berstruktur mengikut submission.',
        'how_it_works' => "Tambah metrik\nPautkan bukti\nSemak hasil",
        'impact' => 'Keputusan projek boleh dinilai menggunakan nilai asas, sasaran dan bukti.',
        'call_to_action' => 'Hubungi pasukan projek',
    ]);
    $submission = save_submission($payload, null, 'draft', null);
    cp10c_check((int) $submission['id'] > 0, 'Parent draft is created');

    $source = [
        'impact_metrics' => [[
            'label' => 'Masa tindak balas', 'value' => '12', 'unit' => 'minit',
            'baseline' => '30', 'target' => '10', 'measured_at' => '2026-07-20',
            'evidence_notes' => 'Purata daripada ujian rintis.', 'unknown' => 'ignored',
        ]],
        'impact_evidence' => [[
            'title' => 'Laporan ujian rintis', 'url' => 'https://example.test/evidence/report',
            'description' => 'Ringkasan keputusan ujian pengguna.',
        ]],
        'recognitions' => [[
            'title' => 'Anugerah Inovasi', 'organiser' => 'KVKS', 'level' => 'Institusi',
            'award_date' => '2026-06-10', 'description' => 'Pengiktirafan projek terbaik.',
            'evidence_url' => 'https://example.test/evidence/award',
        ]],
    ];
    $impact = submission_impact_payload($source);
    cp10c_check(!array_key_exists('unknown', $impact['metrics'][0]), 'Unknown metric keys are rejected');
    cp10c_check(submission_impact_validation_errors($impact, true) === [], 'Complete CP10C payload passes review validation');

    $invalid = submission_impact_payload([
        'impact_metrics' => [['label' => 'Metrik tanpa nilai']],
        'impact_evidence' => [['title' => 'Bukti rosak', 'url' => 'javascript:alert(1)']],
    ]);
    cp10c_check(count(submission_impact_validation_errors($invalid, true)) === 2, 'Missing metric value and unsafe evidence URL are rejected');

    $pdo->prepare("INSERT INTO submission_media (submission_id,media_type,external_url,alt_text,sort_order) VALUES (?,'gallery','https://example.test/gallery','Galeri sedia ada',0)")
        ->execute([(int) $submission['id']]);
    save_submission_impact_details((int) $submission['id'], $impact);
    $stored = submission_impact_details_for_submission((int) $submission['id']);
    cp10c_check(count($stored['metrics']) === 1 && $stored['metrics'][0]['label'] === 'Masa tindak balas', 'Impact metrics are persisted');
    cp10c_check(count($stored['evidence']) === 1 && $stored['evidence'][0]['title'] === 'Laporan ujian rintis', 'Impact evidence is persisted');
    cp10c_check(count($stored['recognitions']) === 1 && $stored['recognitions'][0]['title'] === 'Anugerah Inovasi', 'Awards and recognitions are persisted');

    save_submission_impact_details((int) $submission['id'], ['metrics' => [], 'evidence' => [], 'recognitions' => []]);
    $cleared = submission_impact_details_for_submission((int) $submission['id']);
    cp10c_check($cleared === ['metrics' => [], 'evidence' => [], 'recognitions' => []], 'Draft collections can be cleared');
    $galleryCount = $pdo->prepare("SELECT COUNT(*) FROM submission_media WHERE submission_id = ? AND media_type = 'gallery'");
    $galleryCount->execute([(int) $submission['id']]);
    cp10c_check((int) $galleryCount->fetchColumn() === 1, 'Section 04 saves do not delete media owned by future sections');

    save_submission_impact_details((int) $submission['id'], $impact);
    $pdo->prepare('DELETE FROM submissions WHERE id = ?')->execute([(int) $submission['id']]);
    foreach (['submission_metrics', 'submission_awards', 'submission_media'] as $table) {
        $count = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE submission_id = ?");
        $count->execute([(int) $submission['id']]);
        cp10c_check((int) $count->fetchColumn() === 0, "Cascade delete clears {$table}");
    }

    $pdo->rollBack();
    echo "[PASS] CP10C test transaction rolled back\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "[FAIL] {$exception->getMessage()}\n");
    exit(1);
}
