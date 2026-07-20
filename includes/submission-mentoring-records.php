<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function submission_guidance_types(): array
{
    return [
        'face_to_face' => tr('Bersemuka', 'Face-to-face'),
        'online' => tr('Dalam talian', 'Online'),
        'project_review' => tr('Semakan projek', 'Project review'),
        'workshop' => tr('Bengkel', 'Workshop'),
        'other' => tr('Lain-lain', 'Other'),
    ];
}

function submission_mentoring_text(mixed $value, int $maxlength): string
{
    $value = trim((string) $value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxlength) : substr($value, 0, $maxlength);
}

function submission_mentor_reference(mixed $value): string
{
    $value = trim((string) $value);
    return preg_match('/^(?:id:\d+|ref:[A-Za-z0-9_-]{1,80})$/', $value) ? $value : '';
}

function submission_mentoring_payload(array $source): array
{
    $sourceRows = is_array($source['mentoring_records'] ?? null) ? array_slice($source['mentoring_records'], 0, 30, true) : [];
    $records = [];
    foreach ($sourceRows as $rowKey => $row) {
        if (!is_array($row)) continue;
        $type = submission_mentoring_text($row['guidance_type'] ?? '', 40);
        if (!isset(submission_guidance_types()[$type])) $type = '';
        $record = [
            'row_key' => preg_match('/^[A-Za-z0-9_-]{1,80}$/', (string) $rowKey) ? (string) $rowKey : 'guidance-' . count($records),
            'id' => max(0, (int) ($row['id'] ?? 0)),
            'mentor_reference' => submission_mentor_reference($row['mentor_reference'] ?? ''),
            'guidance_type' => $type,
            'guidance_focus' => submission_mentoring_text($row['guidance_focus'] ?? '', 2000),
            'guidance_outcome' => submission_mentoring_text($row['guidance_outcome'] ?? '', 2000),
        ];
        if ($record['id'] || $record['mentor_reference'] !== '' || $record['guidance_type'] !== '' || $record['guidance_focus'] !== '' || $record['guidance_outcome'] !== '') {
            $records[] = $record;
        }
    }
    return $records;
}

function submission_mentoring_validation_errors(array $records, bool $requiredForReview = false): array
{
    if (!$requiredForReview) return [];
    $errors = [];
    foreach ($records as $record) {
        if ($record['mentor_reference'] === '') $errors[] = 'Mentor perlu dipilih bagi setiap rekod bimbingan.';
        if ($record['guidance_focus'] === '') $errors[] = 'Maklumat perkara yang dibimbing diperlukan.';
        if ($record['guidance_outcome'] === '') $errors[] = 'Hasil bimbingan diperlukan.';
    }
    return array_values(array_unique($errors));
}

function submission_mentoring_records_for_submission(int $submissionId): array
{
    if ($submissionId < 1) return [];
    $stmt = db()->prepare('SELECT r.id, r.mentor_id, r.guidance_type, r.guidance_focus, r.guidance_outcome, m.full_name mentor_name FROM submission_mentoring_records r LEFT JOIN submission_mentors m ON m.id=r.mentor_id AND m.submission_id=r.submission_id WHERE r.submission_id=? ORDER BY r.sort_order,r.id');
    $stmt->execute([$submissionId]);
    $records = $stmt->fetchAll();
    foreach ($records as &$record) {
        $record['row_key'] = 'guidance-' . $record['id'];
        $record['mentor_reference'] = $record['mentor_id'] ? 'id:' . $record['mentor_id'] : '';
    }
    unset($record);
    return $records;
}

function resolve_submission_mentor_reference(string $reference, int $submissionId, array $mentorIdsByReference = []): ?int
{
    if ($reference === '') return null;
    if (str_starts_with($reference, 'ref:')) {
        $rowKey = substr($reference, 4);
        if (!isset($mentorIdsByReference[$rowKey])) throw new RuntimeException('Rujukan mentor baharu tidak sah.');
        $mentorId = (int) $mentorIdsByReference[$rowKey];
    } else {
        $mentorId = (int) substr($reference, 3);
    }
    $check = db()->prepare('SELECT COUNT(*) FROM submission_mentors WHERE id=? AND submission_id=?');
    $check->execute([$mentorId, $submissionId]);
    if (!(bool) $check->fetchColumn()) throw new RuntimeException('Mentor bukan milik submission ini.');
    return $mentorId;
}

function save_submission_mentoring_records(int $submissionId, array $records, array $mentorIdsByReference = []): void
{
    if ($submissionId < 1) throw new InvalidArgumentException('Submission tidak sah.');
    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) $pdo->beginTransaction();
    try {
        $existingStatement = $pdo->prepare('SELECT id FROM submission_mentoring_records WHERE submission_id=?');
        $existingStatement->execute([$submissionId]);
        $existing = array_fill_keys(array_map('intval', $existingStatement->fetchAll(PDO::FETCH_COLUMN)), true);
        $kept = [];
        $insert = $pdo->prepare('INSERT INTO submission_mentoring_records (submission_id,mentor_id,guidance_type,guidance_focus,guidance_outcome,sort_order) VALUES (?,?,?,?,?,?)');
        $update = $pdo->prepare('UPDATE submission_mentoring_records SET mentor_id=?,guidance_type=?,guidance_focus=?,guidance_outcome=?,sort_order=? WHERE id=? AND submission_id=?');
        foreach ($records as $sortOrder => $record) {
            $id = (int) $record['id'];
            if ($id && (!isset($existing[$id]) || isset($kept[$id]))) throw new RuntimeException('Rekod bimbingan tidak sah.');
            $mentorId = resolve_submission_mentor_reference($record['mentor_reference'], $submissionId, $mentorIdsByReference);
            $values = [$mentorId, $record['guidance_type'] ?: null, $record['guidance_focus'] ?: null, $record['guidance_outcome'] ?: null, $sortOrder];
            if ($id) {
                $update->execute([...$values, $id, $submissionId]);
                $kept[$id] = true;
            } else {
                $insert->execute([$submissionId, ...$values]);
                $kept[(int) $pdo->lastInsertId()] = true;
            }
        }
        $delete = $pdo->prepare('DELETE FROM submission_mentoring_records WHERE id=? AND submission_id=?');
        foreach ($existing as $id => $_) if (!isset($kept[$id])) $delete->execute([$id, $submissionId]);
        if ($ownsTransaction) $pdo->commit();
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}
