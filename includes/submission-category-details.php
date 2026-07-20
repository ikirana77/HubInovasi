<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/taxonomy.php';

/**
 * CP10B field definitions. Keys are stable storage identifiers and form an
 * explicit allowlist for submission_category_details writes.
 */
function submission_category_field_definitions(): array
{
    return [
        'digital-systems-intelligence' => [
            ['key' => 'target_users', 'label' => tr('Pengguna sasaran sistem', 'Target system users'), 'help' => tr('Siapa yang menggunakan sistem dan dalam konteks apa?', 'Who uses the system and in what context?'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'platform_environment', 'label' => tr('Platform dan persekitaran', 'Platform and environment'), 'help' => tr('Contoh: web, mudah alih, IoT atau rangkaian setempat.', 'Example: web, mobile, IoT or local network.'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'core_workflow', 'label' => tr('Aliran kerja digital utama', 'Core digital workflow'), 'help' => tr('Terangkan input, pemprosesan dan output sistem.', 'Describe the system input, processing and output.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'data_security', 'label' => tr('Data, privasi dan keselamatan', 'Data, privacy and security'), 'help' => tr('Nyatakan data yang dikendalikan dan langkah perlindungannya.', 'State the data handled and how it is protected.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
        ],
        'business-finance-entrepreneurship' => [
            ['key' => 'target_customers', 'label' => tr('Pelanggan sasaran', 'Target customers'), 'help' => tr('Nyatakan segmen pelanggan utama.', 'State the primary customer segment.'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'value_proposition', 'label' => tr('Proposisi nilai', 'Value proposition'), 'help' => tr('Mengapa pelanggan memilih inovasi ini?', 'Why would customers choose this innovation?'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'business_model', 'label' => tr('Model perniagaan / kewangan', 'Business / financial model'), 'help' => tr('Terangkan kos, harga atau cara nilai dijana.', 'Describe costs, pricing or how value is generated.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'market_validation', 'label' => tr('Pengesahan pasaran', 'Market validation'), 'help' => tr('Nyatakan temu bual, jualan, tempahan atau maklum balas yang ada.', 'State available interviews, sales, orders or feedback.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
        ],
        'food-culinary-nutrition' => [
            ['key' => 'product_format', 'label' => tr('Jenis dan format produk makanan', 'Food product type and format'), 'help' => tr('Contoh: sedia dimakan, sejuk beku atau minuman.', 'Example: ready-to-eat, frozen or beverage.'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'ingredients_allergens', 'label' => tr('Bahan utama dan alergen', 'Key ingredients and allergens'), 'help' => tr('Senaraikan bahan penting serta alergen yang diketahui.', 'List key ingredients and known allergens.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'food_safety', 'label' => tr('Kaedah keselamatan makanan', 'Food safety method'), 'help' => tr('Terangkan kawalan kebersihan, suhu atau penyimpanan.', 'Describe hygiene, temperature or storage controls.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'shelf_life_packaging', 'label' => tr('Jangka hayat dan pembungkusan', 'Shelf life and packaging'), 'help' => tr('Nyatakan anggaran jangka hayat serta kaedah pembungkusan.', 'State estimated shelf life and packaging method.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
        ],
        'hospitality-service-experience' => [
            ['key' => 'target_guests', 'label' => tr('Tetamu / pelanggan sasaran', 'Target guests / customers'), 'help' => tr('Siapa yang menerima pengalaman perkhidmatan ini?', 'Who receives this service experience?'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'service_touchpoints', 'label' => tr('Titik sentuh perkhidmatan', 'Service touchpoints'), 'help' => tr('Senaraikan interaksi penting sebelum, semasa dan selepas perkhidmatan.', 'List key interactions before, during and after service.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'service_flow', 'label' => tr('Aliran operasi perkhidmatan', 'Service operation flow'), 'help' => tr('Terangkan bagaimana staf dan pelanggan melalui proses.', 'Describe how staff and customers move through the process.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'experience_standard', 'label' => tr('Standard pengalaman', 'Experience standard'), 'help' => tr('Bagaimana kualiti, masa atau kepuasan akan dinilai?', 'How will quality, time or satisfaction be evaluated?'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
        ],
        'product-furniture-manufacturing' => [
            ['key' => 'intended_use', 'label' => tr('Kegunaan dan pengguna produk', 'Product use and users'), 'help' => tr('Terangkan fungsi utama dan pengguna sasaran.', 'Describe the primary function and intended users.'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'materials', 'label' => tr('Bahan dan komponen', 'Materials and components'), 'help' => tr('Senaraikan bahan utama dan sebab pemilihannya.', 'List key materials and why they were selected.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'manufacturing_process', 'label' => tr('Proses pembuatan', 'Manufacturing process'), 'help' => tr('Terangkan teknik, mesin atau langkah pemasangan.', 'Describe techniques, machinery or assembly steps.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'dimensions_safety', 'label' => tr('Dimensi, ergonomik dan keselamatan', 'Dimensions, ergonomics and safety'), 'help' => tr('Nyatakan ukuran penting dan pertimbangan keselamatan.', 'State key measurements and safety considerations.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
        ],
        'sustainability-circular-economy' => [
            ['key' => 'resource_input', 'label' => tr('Sumber, bahan atau sisa sasaran', 'Target resource, material or waste'), 'help' => tr('Nyatakan input yang dikurangkan, diguna semula atau dipulihkan.', 'State the input being reduced, reused or recovered.'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'circular_strategy', 'label' => tr('Strategi kitaran', 'Circular strategy'), 'help' => tr('Terangkan mekanisme kurangkan, guna semula, baiki atau kitar semula.', 'Describe the reduce, reuse, repair or recycle mechanism.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'environmental_benefit', 'label' => tr('Manfaat alam sekitar', 'Environmental benefit'), 'help' => tr('Apakah perubahan yang boleh diukur atau diperhatikan?', 'What measurable or observable change is expected?'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'lifecycle_plan', 'label' => tr('Pelan kitar hayat', 'Lifecycle plan'), 'help' => tr('Terangkan sumber bahan hingga pelupusan atau penggunaan semula.', 'Describe sourcing through disposal or reuse.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
        ],
        'community-education-wellbeing' => [
            ['key' => 'beneficiaries', 'label' => tr('Kumpulan penerima manfaat', 'Beneficiary group'), 'help' => tr('Siapa yang menerima manfaat secara langsung?', 'Who benefits directly?'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'intervention_method', 'label' => tr('Kaedah intervensi / pembelajaran', 'Intervention / learning method'), 'help' => tr('Terangkan aktiviti atau pengalaman yang diberikan.', 'Describe the activity or experience delivered.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'accessibility_inclusion', 'label' => tr('Aksesibiliti dan inklusiviti', 'Accessibility and inclusion'), 'help' => tr('Bagaimana keperluan pengguna berbeza dipertimbangkan?', 'How are different user needs considered?'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'success_outcome', 'label' => tr('Hasil kejayaan peserta', 'Participant success outcome'), 'help' => tr('Apakah perubahan pengetahuan, tingkah laku atau kesejahteraan?', 'What change in knowledge, behaviour or well-being is expected?'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
        ],
        'smart-campus-safety-operations' => [
            ['key' => 'operational_area', 'label' => tr('Operasi kampus terlibat', 'Campus operation involved'), 'help' => tr('Contoh: kehadiran, keselamatan, aset atau aduan.', 'Example: attendance, safety, assets or reporting.'), 'type' => 'text', 'required' => true, 'maxlength' => 255],
            ['key' => 'campus_users', 'label' => tr('Pengguna dan pemilik proses', 'Users and process owner'), 'help' => tr('Nyatakan pengguna harian serta unit yang bertanggungjawab.', 'State daily users and the responsible unit.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'system_integration', 'label' => tr('Integrasi dengan operasi sedia ada', 'Existing-operation integration'), 'help' => tr('Bagaimana inovasi berhubung dengan proses atau sistem semasa?', 'How does the innovation connect to current processes or systems?'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
            ['key' => 'privacy_safety_controls', 'label' => tr('Kawalan privasi dan keselamatan', 'Privacy and safety controls'), 'help' => tr('Terangkan akses, data peribadi dan tindak balas risiko.', 'Describe access, personal data and risk response.'), 'type' => 'textarea', 'required' => true, 'maxlength' => 1000],
        ],
    ];
}

function submission_category_fields(string $categorySlug): array
{
    return submission_category_field_definitions()[$categorySlug] ?? [];
}

function submission_category_details_from_source(array $source, string $categorySlug): array
{
    $submitted = $source['category_details'] ?? [];
    if (!is_array($submitted)) return [];

    $details = [];
    foreach (submission_category_fields($categorySlug) as $field) {
        $value = trim((string) ($submitted[$field['key']] ?? ''));
        $maxlength = (int) ($field['maxlength'] ?? 1000);
        if (function_exists('mb_substr')) $value = mb_substr($value, 0, $maxlength);
        else $value = substr($value, 0, $maxlength);
        if ($value !== '') $details[$field['key']] = $value;
    }
    return $details;
}

function submission_category_details_are_complete(string $categorySlug, array $details): bool
{
    $fields = submission_category_fields($categorySlug);
    if (!$fields) return false;
    foreach ($fields as $field) {
        if (!empty($field['required']) && trim((string) ($details[$field['key']] ?? '')) === '') return false;
    }
    return true;
}

function submission_category_details_for_submission(int $submissionId): array
{
    if ($submissionId < 1) return [];
    $stmt = db()->prepare('SELECT detail_key, detail_value FROM submission_category_details WHERE submission_id = ? ORDER BY sort_order, id');
    $stmt->execute([$submissionId]);
    $details = [];
    foreach ($stmt->fetchAll() as $row) $details[$row['detail_key']] = (string) ($row['detail_value'] ?? '');
    return $details;
}

function save_submission_category_details(int $submissionId, string $categorySlug, array $details): void
{
    if ($submissionId < 1) throw new InvalidArgumentException('Submission tidak sah.');
    $fields = submission_category_fields($categorySlug);
    if ($categorySlug !== '' && !$fields) throw new InvalidArgumentException('Kategori submission tidak sah.');

    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM submission_category_details WHERE submission_id = ?')->execute([$submissionId]);
        $insert = $pdo->prepare('INSERT INTO submission_category_details (submission_id, category_slug, detail_key, detail_value, sort_order) VALUES (?, ?, ?, ?, ?)');
        foreach ($fields as $sortOrder => $field) {
            $value = trim((string) ($details[$field['key']] ?? ''));
            if ($value === '') continue;
            $insert->execute([$submissionId, $categorySlug, $field['key'], $value, $sortOrder]);
        }
        if ($ownsTransaction) $pdo->commit();
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}
