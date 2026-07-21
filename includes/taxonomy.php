<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

/** Locked CP09 taxonomy: solution-led categories, separate from academic programmes. */
function hub_solution_areas(): array
{
    return [
        'digital-systems-intelligence' => [
            'icon' => 'code', 'name' => tr('Sistem Digital & Kecerdasan Pintar', 'Digital Systems & Intelligence'),
            'description' => tr('Aplikasi, AI, pangkalan data, IoT, rangkaian dan automasi yang menyelesaikan masalah sebenar.', 'Applications, AI, databases, IoT, networking and automation solving real problems.'),
            'programmes' => ['KPD','KMK'],
        ],
        'business-finance-entrepreneurship' => [
            'icon' => 'chart', 'name' => tr('Perniagaan, Kewangan & Keusahawanan', 'Business, Finance & Entrepreneurship'),
            'description' => tr('Fintech, perakaunan, pemasaran, inventori dan model perniagaan yang lebih berkesan.', 'Fintech, accounting, marketing, inventory and more effective business models.'),
            'programmes' => ['BAK','BPM'],
        ],
        'food-culinary-nutrition' => [
            'icon' => 'heart', 'name' => tr('Inovasi Makanan, Kulinari & Nutrisi', 'Food, Culinary & Nutrition Innovation'),
            'description' => tr('Produk makanan, resipi, pembungkusan, nutrisi dan keselamatan makanan yang dipertingkat.', 'Improved food products, recipes, packaging, nutrition and food safety.'),
            'programmes' => ['HSK','HBP'],
        ],
        'hospitality-service-experience' => [
            'icon' => 'people', 'name' => tr('Hospitaliti, Perkhidmatan & Reka Bentuk Pengalaman', 'Hospitality, Service & Experience Design'),
            'description' => tr('Pengalaman pelanggan, tempahan, acara dan operasi perkhidmatan yang lebih baik.', 'Better customer experiences, bookings, events and service operations.'),
            'programmes' => ['HSK','HBP','BPM'],
        ],
        'product-furniture-manufacturing' => [
            'icon' => 'home', 'name' => tr('Reka Bentuk Produk, Perabot & Pembuatan', 'Product, Furniture & Manufacturing Innovation'),
            'description' => tr('Produk ergonomik, perabot modular, bahan dan proses pembuatan yang lebih pintar.', 'Smarter ergonomic products, modular furniture, materials and manufacturing processes.'),
            'programmes' => ['OPP','KMK'],
        ],
        'sustainability-circular-economy' => [
            'icon' => 'leaf', 'name' => tr('Kelestarian & Ekonomi Kitaran', 'Sustainability & Circular Economy'),
            'description' => tr('Pengurangan sisa, guna semula bahan, penjimatan tenaga dan penyelesaian hijau.', 'Waste reduction, material reuse, energy savings and green solutions.'),
            'programmes' => ['KPD','KMK','BAK','BPM','HSK','HBP','OPP'],
        ],
        'community-education-wellbeing' => [
            'icon' => 'book', 'name' => tr('Komuniti, Pendidikan & Kesejahteraan', 'Community, Education & Well-being'),
            'description' => tr('Pembelajaran, inklusiviti, kesejahteraan dan penyelesaian untuk komuniti.', 'Learning, inclusion, well-being and solutions for communities.'),
            'programmes' => ['KPD','KMK','BAK','BPM','HSK','HBP','OPP'],
        ],
        'smart-campus-safety-operations' => [
            'icon' => 'shield', 'name' => tr('Kampus Pintar, Keselamatan & Operasi', 'Smart Campus, Safety & Operations'),
            'description' => tr('Kehadiran, parkir, aduan, keselamatan, aset dan operasi institusi yang lebih lancar.', 'Smoother attendance, parking, reporting, safety, assets and institutional operations.'),
            'programmes' => ['KPD','KMK','BAK','BPM','HSK','HBP','OPP'],
        ],
    ];
}

function hub_programmes(): array
{
    return [
        'KPD' => tr('KPD — Teknologi Sistem Pengurusan Pangkalan Data dan Aplikasi Web', 'KPD — Database Management Systems and Web Applications Technology'),
        'KMK' => tr('KMK — Teknologi Sistem Komputer dan Rangkaian', 'KMK — Computer Systems and Networking Technology'),
        'BAK' => tr('BAK — Perakaunan', 'BAK — Accounting'),
        'BPM' => tr('BPM — Pengurusan Perniagaan', 'BPM — Business Management'),
        'HSK' => tr('HSK — Seni Kulinari', 'HSK — Culinary Arts'),
        'HBP' => tr('HBP — Bakeri dan Pastri', 'HBP — Bakery and Pastry'),
        'OPP' => tr('OPP — Operasi Pembuatan Perabot', 'OPP — Furniture Manufacturing Operations'),
        'PTK' => tr('PTK — Pentadbiran Kolej', 'PTK — College Administration'),
        'BADAR' => tr('BADAR — BADAR', 'BADAR — BADAR'),
        'HEP' => tr('HEP — Hal Ehwal Pelajar', 'HEP — Student Affairs'),
        'KAUN' => tr('KAUN — Kaunseling', 'KAUN — Counselling'),
    ];
}

function hub_innovation_types(): array
{
    return [
        'digital-solution' => tr('Penyelesaian Digital', 'Digital Solution'),
        'physical-product' => tr('Produk Fizikal', 'Physical Product'),
        'service-process' => tr('Inovasi Perkhidmatan atau Proses', 'Service or Process Innovation'),
        'food-product' => tr('Produk Makanan', 'Food Product'),
        'hybrid' => tr('Hibrid: Produk + Digital + Perkhidmatan', 'Hybrid: Product + Digital + Service'),
        'research-prototype' => tr('Eksperimen atau Prototaip Penyelidikan', 'Experiment or Research Prototype'),
    ];
}

function solution_area_slug(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') return '';
    $areas = hub_solution_areas();
    if (isset($areas[$value])) return $value;
    $legacy = [
        'kehidupan kampus' => 'smart-campus-safety-operations',
        'komuniti & kesejahteraan' => 'community-education-wellbeing',
        'pembelajaran masa hadapan' => 'community-education-wellbeing',
        'pendidikan' => 'community-education-wellbeing',
        'komuniti' => 'community-education-wellbeing',
        'alam sekitar' => 'sustainability-circular-economy',
        'perniagaan & ekonomi' => 'business-finance-entrepreneurship',
        'keselamatan' => 'smart-campus-safety-operations',
    ];
    $lower = function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
    if (isset($legacy[$lower])) return $legacy[$lower];
    foreach ($areas as $slug => $area) if ($value === $area['name']) return $slug;
    return '';
}

function solution_area_label(?string $value): string
{
    $slug = solution_area_slug($value);
    return $slug !== '' ? hub_solution_areas()[$slug]['name'] : trim((string) $value);
}

function innovation_type_label(?string $value): string
{
    $value = trim((string) $value);
    return hub_innovation_types()[$value] ?? $value;
}

function valid_programme_codes(array $codes): array
{
    $allowed = hub_programmes();
    $clean = [];
    foreach ($codes as $code) {
        $code = strtoupper(trim((string) $code));
        if (isset($allowed[$code]) && !in_array($code, $clean, true)) $clean[] = $code;
    }
    return $clean;
}

function decode_programme_codes(?string $json): array
{
    if (!$json) return [];
    $decoded = json_decode($json, true);
    return valid_programme_codes(is_array($decoded) ? $decoded : explode(',', $json));
}
