<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/taxonomy.php';

function decode_list(?string $json): array
{
    if (!$json) return [];
    $value = json_decode($json, true);
    return is_array($value) ? $value : [];
}

function project_row_to_view(array $row): array
{
    $areaSlug = solution_area_slug($row['solution_area'] ?? '');
    $programmeCodes = isset($row['programme_codes'])
        ? decode_programme_codes((string) $row['programme_codes'])
        : get_project_programme_codes((int) $row['id']);
    $project = [
        'id' => (int) $row['id'],
        'slug' => $row['slug'],
        'number' => str_pad((string) $row['display_order'], 2, '0', STR_PAD_LEFT),
        'name' => $row['name'],
        'full_title' => $row['full_title'] ?: $row['name'],
        'category' => $row['category'] ?: tr('Belum diklasifikasikan', 'Unclassified'),
        'solution_area' => solution_area_label($row['solution_area'] ?? ''),
        'solution_area_slug' => $areaSlug,
        'type' => $row['project_type'],
        'innovation_type' => innovation_type_label($row['innovation_type'] ?? $row['project_type'] ?? ''),
        'innovation_type_slug' => $row['innovation_type'] ?? '',
        'programmes' => $programmeCodes,
        'tagline' => $row['tagline'] ?? '',
        'short_description' => $row['short_description'] ?? '',
        'technologies' => decode_list($row['technology_stack']),
        'accent' => $row['accent_class'] ?: 'project-card--cream',
        'status' => $row['development_status'],
        'review_status' => $row['review_status'],
        'verification_status' => $row['verification_status'],
        'featured' => (bool) $row['is_featured'],
        'detail_available' => !empty($row['problem']) && !empty($row['solution']),
        'platform' => $row['platform'],
        'users' => $row['target_users'],
        'location' => $row['project_location'],
        'method' => $row['method'],
        'problem' => $row['problem'],
        'problem_points' => decode_list($row['problem_points']),
        'solution' => $row['solution'],
        'solution_points' => decode_list($row['solution_points']),
        'process_steps' => decode_list($row['how_it_works']),
        'features' => decode_list($row['key_features']),
        'impact' => $row['impact'],
        'impact_points' => decode_list($row['impact_points']),
        'technology_stack' => decode_list($row['technology_details']),
        'journey_milestones' => decode_list($row['project_journey']),
        'team' => [],
        'collaborators' => [],
        'links' => [],
        'assets' => [],
    ];

    $project = localize_project($project);
    $project['solution_area'] = solution_area_label($areaSlug);
    $project['solution_area_slug'] = $areaSlug;
    $project['innovation_type'] = innovation_type_label($project['innovation_type_slug']);
    return $project;
}

function get_project_programme_codes(int $projectId): array
{
    if ($projectId < 1) return [];
    try {
        $stmt = db()->prepare('SELECT programme_code FROM project_programmes WHERE project_id = ? ORDER BY contribution_type = \'lead\' DESC, programme_code');
        $stmt->execute([$projectId]);
        return valid_programme_codes(array_column($stmt->fetchAll(), 'programme_code'));
    } catch (Throwable $exception) {
        return [];
    }
}

function get_public_projects(): array
{
    $stmt = db()->query("SELECT * FROM projects WHERE review_status = 'published' AND verification_status = 'verified' ORDER BY is_featured DESC, display_order ASC");
    return array_map('project_row_to_view', $stmt->fetchAll());
}

function get_public_project_by_slug(string $slug): ?array
{
    $stmt = db()->prepare("SELECT * FROM projects WHERE slug = ? AND review_status = 'published' AND verification_status = 'verified' LIMIT 1");
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if (!$row) return null;

    $project = project_row_to_view($row);
    $links = db()->prepare('SELECT link_type, url, label FROM project_links WHERE project_id = ? AND is_active = 1 ORDER BY display_order');
    $links->execute([$project['id']]);
    $project['links'] = array_values(array_filter($links->fetchAll(), static fn (array $link): bool => valid_external_url($link['url'])));

    $assets = db()->prepare("SELECT asset_type, file_path, alt_text, caption FROM project_assets WHERE project_id = ? AND verification_status = 'verified' ORDER BY display_order");
    $assets->execute([$project['id']]);
    $project['assets'] = $assets->fetchAll();

    $people = db()->prepare("SELECT p.full_name, p.profile_slug, pp.role_title, pp.contribution FROM project_people pp JOIN people p ON p.id = pp.person_id WHERE pp.project_id = ? AND p.verification_status = 'verified' ORDER BY pp.display_order");
    $people->execute([$project['id']]);
    $project['team'] = $people->fetchAll();

    try {
        $teamMembers = db()->prepare("SELECT member_name AS full_name, role_ms, role_en FROM project_team_members WHERE project_id = ? AND verification_status = 'verified' ORDER BY display_order, id");
        $teamMembers->execute([$project['id']]);
        foreach ($teamMembers->fetchAll() as $member) {
            $project['team'][] = [
                'full_name' => $member['full_name'],
                'profile_slug' => null,
                'role_title' => tr((string) ($member['role_ms'] ?? ''), (string) ($member['role_en'] ?? '')),
                'contribution' => null,
            ];
        }
    } catch (Throwable $exception) {
        // CP09C is optional; existing project_people entries remain available.
    }

    try {
        $collaborators = db()->prepare("SELECT name, collaborator_type, role_ms, role_en FROM project_collaborators WHERE project_id = ? AND verification_status = 'verified' ORDER BY display_order, id");
        $collaborators->execute([$project['id']]);
        $project['collaborators'] = array_map(static function (array $row): array {
            $row['role_description'] = tr((string) ($row['role_ms'] ?? ''), (string) ($row['role_en'] ?? ''));
            return $row;
        }, $collaborators->fetchAll());
    } catch (Throwable $exception) {
        $project['collaborators'] = [];
    }
    return $project;
}
