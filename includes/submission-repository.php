<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

const SUBMISSION_FIELDS = [
    'submitter_name', 'submitter_email', 'project_name', 'institution', 'solution_area', 'project_development_status', 'tagline',
    'problem', 'solution', 'how_it_works', 'key_features', 'impact', 'evidence_status',
    'evidence_summary', 'technologies', 'team_details', 'project_journey', 'call_to_action',
];

function submission_payload(array $source): array
{
    $map = [
        'submitter_name' => 'name', 'submitter_email' => 'email', 'project_name' => 'project_name',
        'institution' => 'institution', 'solution_area' => 'category', 'project_development_status' => 'development_status', 'tagline' => 'tagline',
        'problem' => 'problem', 'solution' => 'solution', 'how_it_works' => 'how_it_works',
        'key_features' => 'features', 'impact' => 'impact', 'evidence_status' => 'evidence_status',
        'evidence_summary' => 'evidence', 'technologies' => 'technologies', 'team_details' => 'team',
        'project_journey' => 'journey', 'call_to_action' => 'call_to_action',
    ];
    $payload = [];
    foreach ($map as $column => $field) {
        $value = trim((string) ($source[$field] ?? ''));
        $payload[$column] = $value !== '' ? $value : null;
    }
    return $payload;
}

function find_submission_by_token(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    $stmt = db()->prepare('SELECT * FROM submissions WHERE public_token = ? LIMIT 1');
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

function save_submission(array $payload, ?string $token, string $status): array
{
    $allowed = ['draft', 'pending_review'];
    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException('Status submission tidak sah.');
    }

    $existing = $token ? find_submission_by_token($token) : null;
    if ($existing && !in_array($existing['status'], ['draft', 'needs_revision'], true)) {
        throw new RuntimeException('Submission ini tidak lagi boleh disunting.');
    }

    if (!$existing) {
        $token = bin2hex(random_bytes(32));
        $columns = implode(', ', SUBMISSION_FIELDS);
        $placeholders = implode(', ', array_fill(0, count(SUBMISSION_FIELDS), '?'));
        $stmt = db()->prepare("INSERT INTO submissions (public_token, {$columns}, status, submitted_at) VALUES (?, {$placeholders}, ?, ?)");
        $values = [$token];
        foreach (SUBMISSION_FIELDS as $field) $values[] = $payload[$field] ?? null;
        $values[] = $status;
        $values[] = $status === 'pending_review' ? date('Y-m-d H:i:s') : null;
        $stmt->execute($values);
    } else {
        $assignments = implode(', ', array_map(static fn (string $field): string => "{$field} = ?", SUBMISSION_FIELDS));
        $stmt = db()->prepare("UPDATE submissions SET {$assignments}, status = ?, submitted_at = ? WHERE public_token = ?");
        $values = [];
        foreach (SUBMISSION_FIELDS as $field) $values[] = $payload[$field] ?? null;
        $values[] = $status;
        $values[] = $status === 'pending_review' ? date('Y-m-d H:i:s') : null;
        $values[] = $token;
        $stmt->execute($values);
    }
    return find_submission_by_token((string) $token) ?? [];
}

function submission_is_complete(array $payload): bool
{
    foreach (['submitter_name','submitter_email','project_name','solution_area','project_development_status','tagline','problem','solution','how_it_works','impact','call_to_action'] as $field) {
        if (empty($payload[$field])) return false;
    }
    return filter_var($payload['submitter_email'], FILTER_VALIDATE_EMAIL) !== false;
}

function get_all_submissions(): array
{
    return db()->query('SELECT * FROM submissions ORDER BY updated_at DESC')->fetchAll();
}

function slugify_project(string $name): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
    $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii), '-'));
    return $slug !== '' ? $slug : 'projek-' . bin2hex(random_bytes(4));
}

function text_lines(?string $value): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R+/', $value ?? '') ?: [])));
}

function publish_submission_as_project(PDO $pdo, array $submission): int
{
    if (empty($submission['project_development_status'])) {
        throw new RuntimeException('Tahap pembangunan diperlukan sebelum penerbitan.');
    }

    $projectId = $submission['linked_project_id'] ? (int) $submission['linked_project_id'] : null;
    $slug = slugify_project((string) $submission['project_name']);
    if (!$projectId) {
        $baseSlug = $slug;
        $suffix = 2;
        $check = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE slug = ?');
        do {
            $check->execute([$slug]);
            if (!(bool) $check->fetchColumn()) break;
            $slug = $baseSlug . '-' . $suffix++;
        } while (true);
    }

    $process = array_map(static fn (string $line, int $index): array => ['title' => 'Langkah ' . ($index + 1), 'text' => $line], text_lines($submission['how_it_works']), array_keys(text_lines($submission['how_it_works'])));
    $features = text_lines($submission['key_features']);
    $technologies = array_values(array_filter(array_map('trim', explode(',', (string) $submission['technologies']))));
    $journey = text_lines($submission['project_journey']);

    if ($projectId) {
        $stmt = $pdo->prepare("UPDATE projects SET name=?, full_title=?, category=?, solution_area=?, tagline=?, development_status=?, verification_status='verified', review_status='published', problem=?, solution=?, how_it_works=?, key_features=?, impact=?, technology_stack=?, technology_details=?, project_journey=?, published_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([$submission['project_name'],$submission['project_name'],$submission['solution_area'],$submission['solution_area'],$submission['tagline'],$submission['project_development_status'],$submission['problem'],$submission['solution'],json_encode($process),json_encode($features),$submission['impact'],json_encode($technologies),json_encode($technologies),json_encode($journey),$projectId]);
    } else {
        $order = (int) $pdo->query('SELECT COALESCE(MAX(display_order), 0) + 1 FROM projects')->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO projects (slug,name,full_title,category,solution_area,tagline,development_status,verification_status,review_status,display_order,problem,solution,how_it_works,key_features,impact,technology_stack,technology_details,project_journey,published_at) VALUES (?,?,?,?,?,?,?,'verified','published',?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)");
        $stmt->execute([$slug,$submission['project_name'],$submission['project_name'],$submission['solution_area'],$submission['solution_area'],$submission['tagline'],$submission['project_development_status'],$order,$submission['problem'],$submission['solution'],json_encode($process),json_encode($features),$submission['impact'],json_encode($technologies),json_encode($technologies),json_encode($journey)]);
        $projectId = (int) $pdo->lastInsertId();
    }
    return $projectId;
}

function admin_update_submission(int $id, string $status, ?string $notes): bool
{
    $allowed = ['draft','pending_review','needs_revision','published','archived'];
    if (!in_array($status, $allowed, true)) return false;
    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) $pdo->beginTransaction();
    try {
        $select = $pdo->prepare('SELECT * FROM submissions WHERE id = ? FOR UPDATE');
        $select->execute([$id]);
        $submission = $select->fetch();
        if (!$submission) throw new RuntimeException('Submission tidak dijumpai.');

        $projectId = $submission['linked_project_id'];
        if ($status === 'published') {
            $projectId = publish_submission_as_project($pdo, $submission);
        } elseif ($status === 'archived' && $projectId) {
            $archive = $pdo->prepare("UPDATE projects SET review_status = 'archived', is_featured = 0 WHERE id = ?");
            $archive->execute([$projectId]);
        }

        $stmt = $pdo->prepare('UPDATE submissions SET status = ?, admin_notes = ?, linked_project_id = ?, reviewed_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$status, $notes ?: null, $projectId, $id]);
        if ($ownsTransaction) $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        error_log('Admin submission update failed: ' . $exception->getMessage());
        return false;
    }
}
