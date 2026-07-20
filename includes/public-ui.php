<?php
declare(strict_types=1);

function ui_icon(string $name): string
{
    $icons = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'rocket' => '<path d="M14.5 5.5c2.7-2.7 5.8-2.4 5.8-2.4s.3 3.1-2.4 5.8l-4.7 4.7-3-.8-.8-3 5.1-4.3Z"/><path d="m10 10-4 .6-3 3 5.2 1M13.5 13.5l.6 4-3 3-1-5.2"/><circle cx="16.8" cy="6.7" r="1.3"/>',
        'people' => '<circle cx="9" cy="8" r="3"/><path d="M3.5 20c.3-4 2.2-6 5.5-6s5.2 2 5.5 6M16 11c2.8 0 4.7 1.8 5 5M17 5a3 3 0 1 1 0 6"/>',
        'award' => '<path d="M8 4h8v5a4 4 0 0 1-8 0V4Z"/><path d="M8 6H4v2a4 4 0 0 0 4 4M16 6h4v2a4 4 0 0 1-4 4M12 13v5M8 21h8M9 18h6"/>',
        'heart' => '<path d="M12 21S4 16.5 4 9.7C4 6.8 5.8 5 8.4 5c1.5 0 2.8.8 3.6 2 .8-1.2 2.1-2 3.6-2C18.2 5 20 6.8 20 9.7 20 16.5 12 21 12 21Z"/><path d="M8 12h2l1-2.5 2 5 1-2.5h2"/>',
        'leaf' => '<path d="M20 4C11 4 5 8.3 5 15c0 2.8 1.8 5 4.7 5C16.4 20 20 12.8 20 4Z"/><path d="M4 21c3.5-6 7.5-9.5 12-12"/>',
        'book' => '<path d="M3 5.5c3-1 6-.8 9 1.2v12c-3-2-6-2.2-9-1.2v-12ZM21 5.5c-3-1-6-.8-9 1.2v12c3-2 6-2.2 9-1.2v-12Z"/>',
        'home' => '<path d="m3 11 9-7 9 7v9H3v-9Z"/><path d="M9 20v-6h6v6"/>',
        'shield' => '<path d="M12 3 4.5 6v5.2c0 4.4 3 7.8 7.5 9.8 4.5-2 7.5-5.4 7.5-9.8V6L12 3Z"/><path d="m9 12 2 2 4-4"/>',
        'chart' => '<path d="M4 20V10M10 20V5M16 20v-8M22 20H2"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/><path d="m14 10 6-6"/>',
        'bulb' => '<path d="M9 18h6M10 21h4M8.5 14.5A7 7 0 1 1 15.5 14.5c-1 .8-1.5 1.7-1.5 3h-4c0-1.3-.5-2.2-1.5-3Z"/>',
        'code' => '<path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 4l-4 16"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
        'pin' => '<path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13Z"/><circle cx="12" cy="9" r="2"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14 0 18M12 3c-3 3.5-3 14 0 18"/>',
    ];
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($icons[$name] ?? $icons['star']) . '</svg>';
}

function project_image(string $slug): string
{
    $map = [
        'hers' => 'project-hers.webp?v=' . filemtime(__DIR__ . '/../assets/images/home/project-hers.webp'),
        'spark' => 'project-spark.webp',
        'durian-radar' => 'project-durian-radar.webp',
        'cms-quest' => 'project-cms-quest.webp',
        'ecotrack' => 'project-ecotrack.webp',
        'medbuddy' => 'project-medbuddy.webp',
    ];
    return 'assets/images/home/' . ($map[$slug] ?? 'featured-durian-radar.webp');
}

function project_detail_image(array $project): string
{
    foreach (($project['assets'] ?? []) as $asset) {
        $path = ltrim((string) ($asset['file_path'] ?? ''), '/');
        if (($asset['asset_type'] ?? '') === 'application_screenshot'
            && preg_match('#^(?:assets|uploads)/[A-Za-z0-9_./-]+$#', $path)) {
            return $path;
        }
    }
    return project_image((string) ($project['slug'] ?? ''));
}

function enable_public_mockup(string $bodyClass): void
{
    $GLOBALS['publicMockup'] = true;
    $GLOBALS['bodyClass'] = trim('public-mockup-page ' . $bodyClass);
    $GLOBALS['extraStylesheets'] = ['assets/css/public-mockup.css'];
}
