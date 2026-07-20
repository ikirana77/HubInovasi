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

function save_submission(array $payload, ?string $token, string $status, ?int $ownerUserId = null): array
{
    $allowed = ['draft', 'pending_review'];
    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException('Status submission tidak sah.');
    }
    if ($status === 'pending_review' && !submission_is_complete($payload)) {
        throw new RuntimeException('Submission mesti lengkap sebelum dihantar untuk semakan.');
    }

    $existing = $token ? find_submission_by_token($token) : null;
    if ($token && !$existing) throw new RuntimeException('Submission tidak dijumpai.');
    if ($existing && !in_array($existing['status'], ['draft', 'needs_revision'], true)) {
        throw new RuntimeException('Submission ini tidak lagi boleh disunting.');
    }
    if ($existing && $existing['status'] === 'needs_revision' && $status !== 'pending_review') {
        throw new RuntimeException('Submission pembetulan mesti dihantar semula untuk semakan.');
    }
    if ($existing && !empty($existing['owner_user_id']) && (int) $existing['owner_user_id'] !== (int) $ownerUserId) {
        throw new RuntimeException('Anda tidak mempunyai akses kepada submission ini.');
    }

    if (!$existing) {
        $token = bin2hex(random_bytes(32));
        $columns = implode(', ', SUBMISSION_FIELDS);
        $placeholders = implode(', ', array_fill(0, count(SUBMISSION_FIELDS), '?'));
        $stmt = db()->prepare("INSERT INTO submissions (public_token, owner_user_id, {$columns}, status, submitted_at) VALUES (?, ?, {$placeholders}, ?, ?)");
        $values = [$token, $ownerUserId];
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


function find_submission_for_owner(string $token, int $ownerUserId): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token) || $ownerUserId < 1) return null;
    $stmt = db()->prepare('SELECT * FROM submissions WHERE public_token = ? AND owner_user_id = ? LIMIT 1');
    $stmt->execute([$token, $ownerUserId]);
    return $stmt->fetch() ?: null;
}

function user_submission_summary_counts(int $ownerUserId): array
{
    $counts = ['all' => 0, 'draft' => 0, 'pending_review' => 0, 'needs_revision' => 0, 'published' => 0, 'archived' => 0];
    $stmt = db()->prepare('SELECT status, COUNT(*) total FROM submissions WHERE owner_user_id = ? GROUP BY status');
    $stmt->execute([$ownerUserId]);
    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['status']] = (int) $row['total'];
        $counts['all'] += (int) $row['total'];
    }
    return $counts;
}

function user_submissions(int $ownerUserId): array
{
    $stmt = db()->prepare('SELECT s.id,s.public_token,s.project_name,s.tagline,s.status,s.admin_notes,s.submitted_at,s.updated_at,p.slug linked_project_slug FROM submissions s LEFT JOIN projects p ON p.id=s.linked_project_id WHERE s.owner_user_id=? ORDER BY s.updated_at DESC');
    $stmt->execute([$ownerUserId]);
    return $stmt->fetchAll();
}

function submission_is_complete(array $payload): bool
{
    foreach (['submitter_name','submitter_email','project_name','solution_area','project_development_status','tagline','problem','solution','how_it_works','impact','call_to_action'] as $field) {
        if (empty($payload[$field])) return false;
    }
    return filter_var($payload['submitter_email'], FILTER_VALIDATE_EMAIL) !== false;
}

function allowed_submission_transitions(string $actor = 'admin'): array
{
    if ($actor === 'submitter') return ['draft' => ['pending_review'], 'needs_revision' => ['pending_review']];
    return [
        'pending_review' => ['needs_revision','published','archived'],
        'published' => ['archived'],
    ];
}

function can_transition_submission(string $from, string $to, string $actor = 'admin'): bool
{
    return in_array($to, allowed_submission_transitions($actor)[$from] ?? [], true);
}

function submission_row_is_complete(array $submission): bool
{
    return submission_is_complete($submission);
}

function submission_status_label(string $status): string
{
    return [
        'draft' => 'Draf', 'pending_review' => 'Menunggu Semakan', 'needs_revision' => 'Perlu Pembetulan',
        'published' => 'Diterbitkan', 'archived' => 'Diarkibkan',
    ][$status] ?? $status;
}

function submission_summary_counts(): array
{
    $counts = ['all' => 0, 'pending_review' => 0, 'needs_revision' => 0, 'published' => 0, 'archived' => 0];
    $stmt = db()->prepare('SELECT status, COUNT(*) total FROM submissions GROUP BY status');
    $stmt->execute();
    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['status']] = (int) $row['total'];
        $counts['all'] += (int) $row['total'];
    }
    return $counts;
}

function search_submissions(string $status = '', string $query = '', int $page = 1, int $perPage = 10): array
{
    $allowedStatuses = ['draft','pending_review','needs_revision','published','archived'];
    $conditions = [];
    $params = [];
    if (in_array($status, $allowedStatuses, true)) { $conditions[] = 'status = ?'; $params[] = $status; }
    if ($query !== '') {
        $conditions[] = '(project_name LIKE ? OR submitter_name LIKE ? OR submitter_email LIKE ?)';
        $term = '%' . $query . '%';
        array_push($params, $term, $term, $term);
    }
    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    $count = db()->prepare('SELECT COUNT(*) FROM submissions' . $where);
    $count->execute($params);
    $total = (int) $count->fetchColumn();
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    $offset = ($page - 1) * $perPage;
    $stmt = db()->prepare('SELECT id,project_name,submitter_name,submitter_email,project_development_status,evidence_status,status,created_at,submitted_at,updated_at FROM submissions' . $where . ' ORDER BY updated_at DESC LIMIT ? OFFSET ?');
    $position = 1;
    foreach ($params as $param) $stmt->bindValue($position++, $param, PDO::PARAM_STR);
    $stmt->bindValue($position++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($position, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
}

function find_submission_by_id(int $id): ?array
{
    if ($id < 1) return null;
    $stmt = db()->prepare('SELECT s.*, p.slug linked_project_slug, p.name linked_project_name, u.role owner_role, u.account_status owner_account_status FROM submissions s LEFT JOIN projects p ON p.id=s.linked_project_id LEFT JOIN users u ON u.id=s.owner_user_id WHERE s.id=? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function submission_status_history(int $id): array
{
    $stmt = db()->prepare('SELECT h.*, a.full_name admin_name FROM submission_status_history h LEFT JOIN admin_users a ON a.id=h.admin_user_id WHERE h.submission_id=? ORDER BY h.created_at DESC, h.id DESC');
    $stmt->execute([$id]);
    return $stmt->fetchAll();
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

function transition_submission_status(int $id, string $status, ?int $adminId, ?string $notes): array
{
    $notes = trim((string) $notes);
    if ($id < 1 || !$adminId) return ['success' => false, 'message' => 'Permintaan tidak sah.'];
    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) $pdo->beginTransaction();
    try {
        $select = $pdo->prepare('SELECT * FROM submissions WHERE id = ? FOR UPDATE');
        $select->execute([$id]);
        $submission = $select->fetch();
        if (!$submission) throw new RuntimeException('Submission tidak dijumpai.');
        $from = $submission['status'];
        if (!can_transition_submission($from, $status, 'admin')) {
            if ($ownsTransaction) $pdo->rollBack();
            return ['success' => false, 'message' => 'Perubahan status tidak dibenarkan.'];
        }
        if (in_array($status, ['needs_revision','archived'], true) && $notes === '') {
            if ($ownsTransaction) $pdo->rollBack();
            return ['success' => false, 'message' => 'Nota admin diperlukan untuk tindakan ini.'];
        }
        if ($status === 'published' && !submission_row_is_complete($submission)) {
            if ($ownsTransaction) $pdo->rollBack();
            return ['success' => false, 'message' => 'Submission belum lengkap dan tidak boleh diterbitkan.'];
        }

        $projectId = $submission['linked_project_id'];
        if ($status === 'published') {
            $projectId = publish_submission_as_project($pdo, $submission);
        } elseif ($status === 'archived' && $projectId) {
            $archive = $pdo->prepare("UPDATE projects SET review_status = 'archived', is_featured = 0 WHERE id = ?");
            $archive->execute([$projectId]);
        }

        $stmt = $pdo->prepare('UPDATE submissions SET status=?, admin_notes=?, linked_project_id=?, reviewed_at=CURRENT_TIMESTAMP, reviewed_by_admin_id=? WHERE id=?');
        $stmt->execute([$status, $notes ?: null, $projectId, $adminId, $id]);
        $history = $pdo->prepare('INSERT INTO submission_status_history (submission_id,from_status,to_status,admin_user_id,admin_notes) VALUES (?,?,?,?,?)');
        $history->execute([$id, $from, $status, $adminId, $notes ?: null]);
        if ($ownsTransaction) $pdo->commit();
        return ['success' => true, 'message' => 'Status submission berjaya dikemas kini.'];
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        error_log('Admin submission update failed: ' . $exception->getMessage());
        return ['success' => false, 'message' => 'Status tidak dapat dikemas kini.'];
    }
}

/** Compatibility untuk ujian CP05; UI admin CP06 wajib menggunakan transition_submission_status(). */
function admin_update_submission(int $id, string $status, ?string $notes, ?int $adminId = null): bool
{
    if (!$adminId) return false;
    return transition_submission_status($id, $status, $adminId, $notes)['success'];
}
