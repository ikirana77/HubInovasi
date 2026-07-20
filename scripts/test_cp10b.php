<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/submission-repository.php';
require_once __DIR__ . '/../includes/submission-category-details.php';

function cp10b_check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo "[PASS] {$message}\n";
}

$pdo = db();
$pdo->beginTransaction();
try {
    $definitions = submission_category_field_definitions();
    cp10b_check(count($definitions) === 8, 'All eight solution areas have adaptive definitions');
    foreach ($definitions as $category => $fields) {
        cp10b_check(count($fields) >= 4, "{$category} has a complete adaptive field set");
    }

    $payload = array_fill_keys(SUBMISSION_FIELDS, null);
    $payload = array_merge($payload, [
        'submitter_name' => 'CP10B Tester',
        'submitter_email' => 'cp10b-' . bin2hex(random_bytes(4)) . '@example.test',
        'project_name' => 'CP10B Adaptive Project',
        'institution' => 'KVKS',
        'solution_area' => 'smart-campus-safety-operations',
        'innovation_type' => 'digital-solution',
        'programme_codes' => json_encode(['KPD']),
        'project_development_status' => 'Functional Prototype',
        'tagline' => 'Operasi kampus yang lebih jelas dan selamat.',
        'problem' => 'Operasi kampus memerlukan proses yang tersusun dan mudah dijejak oleh pemilik proses.',
        'solution' => 'Penyelesaian menyatukan langkah operasi dalam aliran kerja yang boleh dipantau.',
        'how_it_works' => "Daftar permintaan\nProses tindakan\nPantau hasil",
        'impact' => 'Masa tindak balas dapat dikurangkan dan rekod operasi menjadi lebih konsisten.',
        'call_to_action' => 'Hubungi pasukan projek',
    ]);
    $submission = save_submission($payload, null, 'draft', null);
    cp10b_check((int) $submission['id'] > 0, 'Parent draft is created');

    $source = ['category_details' => [
        'operational_area' => 'Keselamatan dan aduan',
        'campus_users' => 'Pelajar, pensyarah dan Unit Keselamatan',
        'system_integration' => 'Menggunakan proses aduan dan rondaan sedia ada',
        'privacy_safety_controls' => 'Akses mengikut peranan dan rekod audit',
        'unexpected_key' => 'must not be stored',
    ]];
    $details = submission_category_details_from_source($source, $payload['solution_area']);
    cp10b_check(!array_key_exists('unexpected_key', $details), 'Unknown detail keys are rejected by the allowlist');
    cp10b_check(submission_category_details_are_complete($payload['solution_area'], $details), 'Complete category detail payload passes validation');
    cp10b_check(!submission_category_details_are_complete($payload['solution_area'], ['operational_area' => 'Keselamatan']), 'Partial category detail payload remains valid only as a draft');

    save_submission_category_details((int) $submission['id'], $payload['solution_area'], $details);
    $stored = submission_category_details_for_submission((int) $submission['id']);
    cp10b_check($stored === $details, 'Section 03 details are stored and read in configured order');

    $newCategory = 'food-culinary-nutrition';
    $newDetails = [
        'product_format' => 'Produk sejuk beku',
        'ingredients_allergens' => 'Tepung gandum; mengandungi gluten',
        'food_safety' => 'Kawalan suhu dan sanitasi berjadual',
        'shelf_life_packaging' => 'Tiga bulan dalam pembungkusan kedap udara',
    ];
    save_submission_category_details((int) $submission['id'], $newCategory, $newDetails);
    $categoryRows = $pdo->prepare('SELECT DISTINCT category_slug FROM submission_category_details WHERE submission_id = ?');
    $categoryRows->execute([(int) $submission['id']]);
    cp10b_check($categoryRows->fetchAll(PDO::FETCH_COLUMN) === [$newCategory], 'Changing category replaces stale Section 03 records');

    save_submission_category_details((int) $submission['id'], '', []);
    cp10b_check(submission_category_details_for_submission((int) $submission['id']) === [], 'An incomplete draft may clear its category without blocking parent save');

    save_submission_category_details((int) $submission['id'], $newCategory, $newDetails);

    $pdo->prepare('DELETE FROM submissions WHERE id = ?')->execute([(int) $submission['id']]);
    $childCount = $pdo->prepare('SELECT COUNT(*) FROM submission_category_details WHERE submission_id = ?');
    $childCount->execute([(int) $submission['id']]);
    cp10b_check((int) $childCount->fetchColumn() === 0, 'Deleting the parent cascades to Section 03 records');

    $pdo->rollBack();
    echo "[PASS] CP10B test transaction rolled back\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "[FAIL] {$exception->getMessage()}\n");
    exit(1);
}
