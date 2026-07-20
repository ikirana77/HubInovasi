<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$failures = 0;
function cp073_assert(bool $condition, string $message): void
{
    global $failures;
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $message . PHP_EOL;
    if (!$condition) $failures++;
}

$_SESSION['app_language'] = 'ms';
cp073_assert(app_language() === 'ms', 'BM is available as the default language');
cp073_assert(tr('Utama', 'Home') === 'Utama', 'BM copy is returned in BM mode');

$_SESSION['app_language'] = 'en';
cp073_assert(app_language() === 'en', 'English mode can be activated');
cp073_assert(tr('Utama', 'Home') === 'Home', 'English copy is returned in English mode');
cp073_assert(t('nav.submit') === 'Submit Project', 'English dictionary is loaded');

$project = localize_project([
    'slug' => 'hers',
    'full_title' => 'Tajuk BM',
    'category' => 'Aplikasi Mudah Alih',
]);
cp073_assert(($project['category'] ?? '') === 'Mobile Application', 'Verified project copy is localised');

$_SERVER['REQUEST_URI'] = '/hubinovasi/project.php?slug=hers';
$url = language_switch_url('ms');
cp073_assert(str_contains($url, 'slug=hers') && str_contains($url, 'lang=ms'), 'Language switch preserves the current page and query');

$_SESSION['app_language'] = 'ms';
cp073_assert(app_language() === HUBINOVASI_DEFAULT_LANGUAGE, 'BM remains the default language');

exit($failures === 0 ? 0 : 1);
