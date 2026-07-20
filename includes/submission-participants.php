<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/taxonomy.php';

const SUBMISSION_PROFILE_PHOTO_MAX_BYTES = 5 * 1024 * 1024;
const SUBMISSION_PROFILE_PHOTO_MAX_PIXELS = 40000000;
const SUBMISSION_PROFILE_PHOTO_SIZE = 512;
const SUBMISSION_PERSON_LIMIT = 30;

final class SubmissionPhotoException extends RuntimeException {}

function submission_student_programmes(): array
{
    return array_intersect_key(hub_programmes(), array_flip(['KPD', 'KMK', 'BAK', 'BPM', 'HSK', 'HBP', 'OPP']));
}

function submission_study_years(): array
{
    return ['1SVM' => '1 SVM', '2SVM' => '2 SVM', '1DVM' => '1 DVM', '2DVM' => '2 DVM'];
}

function submission_person_text(mixed $value, int $maxlength): string
{
    $value = trim((string) $value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxlength) : substr($value, 0, $maxlength);
}

function submission_person_row_key(mixed $value, string $prefix, int $index): string
{
    $value = (string) $value;
    return preg_match('/^[A-Za-z0-9_-]{1,80}$/', $value) ? $value : $prefix . '-' . $index;
}

function submission_participants_payload(array $source): array
{
    $students = [];
    $leaderAssigned = false;
    $studentRows = is_array($source['participants'] ?? null) ? array_slice($source['participants'], 0, SUBMISSION_PERSON_LIMIT, true) : [];
    foreach ($studentRows as $rowKey => $row) {
        if (!is_array($row)) continue;
        $fullName = submission_person_text($row['full_name'] ?? '', 160);
        if ($fullName === '') continue;
        $isLeader = !$leaderAssigned && !empty($row['is_team_leader']);
        if ($isLeader) $leaderAssigned = true;
        $programme = strtoupper(submission_person_text($row['programme'] ?? '', 3));
        if (!isset(submission_student_programmes()[$programme])) $programme = '';
        $studyYear = strtoupper(submission_person_text($row['year_of_study'] ?? '', 80));
        if (!isset(submission_study_years()[$studyYear])) $studyYear = '';
        $students[] = [
            'row_key' => submission_person_row_key($rowKey, 'student', count($students)),
            'id' => max(0, (int) ($row['id'] ?? 0)),
            'full_name' => $fullName,
            'programme' => $programme,
            'class_name' => submission_person_text($row['class_name'] ?? '', 120),
            'role_title' => submission_person_text($row['role_title'] ?? '', 160),
            'year_of_study' => $studyYear,
            'is_team_leader' => $isLeader,
            'remove_photo' => !empty($row['remove_photo']),
        ];
    }
    usort($students, static fn (array $a, array $b): int => (int) $b['is_team_leader'] <=> (int) $a['is_team_leader']);

    $mentors = [];
    $mentorRows = is_array($source['mentors'] ?? null) ? array_slice($source['mentors'], 0, SUBMISSION_PERSON_LIMIT, true) : [];
    foreach ($mentorRows as $rowKey => $row) {
        if (!is_array($row)) continue;
        $fullName = submission_person_text($row['full_name'] ?? '', 160);
        if ($fullName === '') continue;
        $mentors[] = [
            'row_key' => submission_person_row_key($rowKey, 'mentor', count($mentors)),
            'id' => max(0, (int) ($row['id'] ?? 0)),
            'full_name' => $fullName,
            'position_title' => submission_person_text($row['position_title'] ?? '', 180),
            'institution' => submission_person_text($row['institution'] ?? '', 255),
            'role_title' => submission_person_text($row['role_title'] ?? '', 2000),
            'remove_photo' => !empty($row['remove_photo']),
        ];
    }
    return ['students' => $students, 'mentors' => $mentors];
}

function submission_participants_validation_errors(array $payload, bool $requiredForReview = false): array
{
    $errors = [];
    $programmes = submission_student_programmes();
    $years = submission_study_years();
    if ($requiredForReview && empty($payload['students'])) $errors[] = 'Sekurang-kurangnya seorang pelajar diperlukan.';
    foreach (($payload['students'] ?? []) as $student) {
        if (!isset($programmes[$student['programme']])) $errors[] = 'Program pelajar tidak sah.';
        if ($student['class_name'] === '') $errors[] = 'Nama kelas pelajar diperlukan.';
        if ($student['role_title'] === '') $errors[] = 'Peranan pelajar diperlukan.';
        if (!isset($years[$student['year_of_study']])) $errors[] = 'Tahun pengajian pelajar tidak sah.';
    }
    foreach (($payload['mentors'] ?? []) as $mentor) {
        if ($mentor['position_title'] === '') $errors[] = 'Jawatan mentor diperlukan.';
        if ($mentor['institution'] === '') $errors[] = 'Organisasi mentor diperlukan.';
        if ($mentor['role_title'] === '') $errors[] = 'Peranan mentor diperlukan.';
    }
    return array_values(array_unique($errors));
}

function submission_profile_storage_options(array $options = []): array
{
    return [
        'root' => $options['root'] ?? dirname(__DIR__) . '/uploads/submissions/people',
        'public_prefix' => trim((string) ($options['public_prefix'] ?? 'uploads/submissions/people'), '/'),
        'allow_local_test_files' => !empty($options['allow_local_test_files']),
    ];
}

function submission_profile_photo_public_path(?string $path): ?string
{
    return $path && preg_match('#^uploads/submissions/people/[a-f0-9]{32}\.webp$#', $path) ? $path : null;
}

function submission_profile_upload(array $files, string $group, string $rowKey): ?array
{
    if (!isset($files[$group]) || !is_array($files[$group])) return null;
    $file = $files[$group];
    $error = $file['error'][$rowKey] ?? UPLOAD_ERR_NO_FILE;
    if ((int) $error === UPLOAD_ERR_NO_FILE) return null;
    return [
        'name' => $file['name'][$rowKey] ?? '',
        'type' => $file['type'][$rowKey] ?? '',
        'tmp_name' => $file['tmp_name'][$rowKey] ?? '',
        'error' => (int) $error,
        'size' => (int) ($file['size'][$rowKey] ?? 0),
    ];
}

function store_submission_profile_photo(array $file, array $options = []): string
{
    $storage = submission_profile_storage_options($options);
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new SubmissionPhotoException('Upload foto tidak berjaya.');
    if (($file['size'] ?? 0) < 1 || (int) $file['size'] > SUBMISSION_PROFILE_PHOTO_MAX_BYTES) throw new SubmissionPhotoException('Foto profil mesti tidak melebihi 5MB.');
    $tmpName = (string) ($file['tmp_name'] ?? '');
    if (!is_file($tmpName) || (!$storage['allow_local_test_files'] && !is_uploaded_file($tmpName))) throw new SubmissionPhotoException('Fail upload foto tidak sah.');
    if (!class_exists('finfo')) throw new SubmissionPhotoException('Extension fileinfo diperlukan untuk upload foto.');
    if (!extension_loaded('gd') || !function_exists('imagewebp')) throw new SubmissionPhotoException('Extension GD dengan sokongan WebP diperlukan untuk memproses foto profil.');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $loaders = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    ];
    if (!isset($loaders[$mime]) || !function_exists($loaders[$mime])) throw new SubmissionPhotoException('Foto profil mesti berformat JPG, PNG atau WebP.');
    $dimensions = @getimagesize($tmpName);
    if (!$dimensions || $dimensions[0] < 1 || $dimensions[1] < 1 || ($dimensions[0] * $dimensions[1]) > SUBMISSION_PROFILE_PHOTO_MAX_PIXELS) throw new SubmissionPhotoException('Dimensi foto profil tidak sah atau terlalu besar.');

    $source = @$loaders[$mime]($tmpName);
    if (!$source) throw new SubmissionPhotoException('Foto profil tidak dapat dibaca.');
    $cropSize = min((int) $dimensions[0], (int) $dimensions[1]);
    $sourceX = (int) floor(((int) $dimensions[0] - $cropSize) / 2);
    $sourceY = (int) floor(((int) $dimensions[1] - $cropSize) / 2);
    $targetSize = min(SUBMISSION_PROFILE_PHOTO_SIZE, $cropSize);
    $target = imagecreatetruecolor($targetSize, $targetSize);
    if (!$target || !imagecopyresampled($target, $source, 0, 0, $sourceX, $sourceY, $targetSize, $targetSize, $cropSize, $cropSize)) {
        imagedestroy($source);
        if ($target) imagedestroy($target);
        throw new SubmissionPhotoException('Foto profil tidak dapat diproses.');
    }
    imagedestroy($source);

    if (!is_dir($storage['root']) && !mkdir($storage['root'], 0775, true) && !is_dir($storage['root'])) {
        imagedestroy($target);
        throw new SubmissionPhotoException('Direktori foto profil tidak dapat disediakan.');
    }
    $filename = bin2hex(random_bytes(16)) . '.webp';
    $destination = rtrim($storage['root'], '/\\') . DIRECTORY_SEPARATOR . $filename;
    $stored = imagewebp($target, $destination, 84);
    imagedestroy($target);
    if (!$stored || !is_file($destination)) throw new SubmissionPhotoException('Foto profil tidak dapat disimpan.');
    @chmod($destination, 0644);
    return $storage['public_prefix'] . '/' . $filename;
}

function delete_submission_profile_photo(?string $path, array $options = []): bool
{
    if (!$path) return false;
    $storage = submission_profile_storage_options($options);
    $prefix = $storage['public_prefix'] . '/';
    if (!str_starts_with($path, $prefix)) return false;
    $filename = substr($path, strlen($prefix));
    if (!preg_match('/^[a-f0-9]{32}\.webp$/', $filename)) return false;
    $target = rtrim($storage['root'], '/\\') . DIRECTORY_SEPARATOR . $filename;
    return is_file($target) ? unlink($target) : false;
}

function finalize_submission_profile_files(array $plan, bool $committed, array $options = []): void
{
    foreach (($committed ? ($plan['obsolete'] ?? []) : ($plan['created'] ?? [])) as $path) {
        delete_submission_profile_photo($path, $options);
    }
}

function submission_participants_for_submission(int $submissionId): array
{
    if ($submissionId < 1) return ['students' => [], 'mentors' => []];
    $students = db()->prepare('SELECT id, full_name, programme, class_name, role_title, year_of_study, is_team_leader, profile_image_path FROM submission_people WHERE submission_id = ? ORDER BY is_team_leader DESC, sort_order, id');
    $students->execute([$submissionId]);
    $studentRows = $students->fetchAll();
    foreach ($studentRows as &$student) {
        $student['row_key'] = 'student-' . $student['id'];
        $student['is_team_leader'] = (bool) $student['is_team_leader'];
    }
    unset($student);

    $mentors = db()->prepare('SELECT id, full_name, position_title, institution, mentorship_contribution role_title, profile_image_path FROM submission_mentors WHERE submission_id = ? ORDER BY sort_order, id');
    $mentors->execute([$submissionId]);
    $mentorRows = $mentors->fetchAll();
    foreach ($mentorRows as &$mentor) $mentor['row_key'] = 'mentor-' . $mentor['id'];
    unset($mentor);
    return ['students' => $studentRows, 'mentors' => $mentorRows];
}

function save_submission_participants(int $submissionId, array $payload, array $files = [], array $options = []): array
{
    if ($submissionId < 1) throw new InvalidArgumentException('Submission tidak sah.');
    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    $plan = ['created' => [], 'obsolete' => []];
    if ($ownsTransaction) $pdo->beginTransaction();
    try {
        $studentStatement = $pdo->prepare('SELECT id, profile_image_path FROM submission_people WHERE submission_id = ?');
        $studentStatement->execute([$submissionId]);
        $existingStudents = [];
        foreach ($studentStatement->fetchAll() as $row) $existingStudents[(int) $row['id']] = $row;
        $keptStudents = [];
        $studentInsert = $pdo->prepare('INSERT INTO submission_people (submission_id, full_name, programme, class_name, role_title, year_of_study, is_team_leader, profile_image_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $studentUpdate = $pdo->prepare('UPDATE submission_people SET full_name=?, programme=?, class_name=?, role_title=?, year_of_study=?, is_team_leader=?, profile_image_path=?, sort_order=? WHERE id=? AND submission_id=?');
        foreach (($payload['students'] ?? []) as $sortOrder => $student) {
            $id = (int) $student['id'];
            if ($id && (!isset($existingStudents[$id]) || isset($keptStudents[$id]))) throw new RuntimeException('Rekod pelajar tidak sah.');
            $oldPath = $id ? ($existingStudents[$id]['profile_image_path'] ?? null) : null;
            $newPath = $oldPath;
            $upload = submission_profile_upload($files, 'participant_photos', $student['row_key']);
            if ($upload) {
                $newPath = store_submission_profile_photo($upload, $options);
                $plan['created'][] = $newPath;
                if ($oldPath) $plan['obsolete'][] = $oldPath;
            } elseif (!empty($student['remove_photo'])) {
                $newPath = null;
                if ($oldPath) $plan['obsolete'][] = $oldPath;
            }
            $values = [$student['full_name'], $student['programme'], $student['class_name'], $student['role_title'], $student['year_of_study'], (int) $student['is_team_leader'], $newPath, $sortOrder];
            if ($id) {
                $studentUpdate->execute([...$values, $id, $submissionId]);
                $keptStudents[$id] = true;
            } else {
                $studentInsert->execute([$submissionId, ...$values]);
                $keptStudents[(int) $pdo->lastInsertId()] = true;
            }
        }
        $deleteStudent = $pdo->prepare('DELETE FROM submission_people WHERE id = ? AND submission_id = ?');
        foreach ($existingStudents as $id => $row) {
            if (isset($keptStudents[$id])) continue;
            $deleteStudent->execute([$id, $submissionId]);
            if ($row['profile_image_path']) $plan['obsolete'][] = $row['profile_image_path'];
        }

        $mentorStatement = $pdo->prepare('SELECT id, profile_image_path FROM submission_mentors WHERE submission_id = ?');
        $mentorStatement->execute([$submissionId]);
        $existingMentors = [];
        foreach ($mentorStatement->fetchAll() as $row) $existingMentors[(int) $row['id']] = $row;
        $keptMentors = [];
        $mentorInsert = $pdo->prepare('INSERT INTO submission_mentors (submission_id, full_name, position_title, institution, mentorship_contribution, profile_image_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $mentorUpdate = $pdo->prepare('UPDATE submission_mentors SET full_name=?, position_title=?, institution=?, mentorship_contribution=?, profile_image_path=?, sort_order=? WHERE id=? AND submission_id=?');
        foreach (($payload['mentors'] ?? []) as $sortOrder => $mentor) {
            $id = (int) $mentor['id'];
            if ($id && (!isset($existingMentors[$id]) || isset($keptMentors[$id]))) throw new RuntimeException('Rekod mentor tidak sah.');
            $oldPath = $id ? ($existingMentors[$id]['profile_image_path'] ?? null) : null;
            $newPath = $oldPath;
            $upload = submission_profile_upload($files, 'mentor_photos', $mentor['row_key']);
            if ($upload) {
                $newPath = store_submission_profile_photo($upload, $options);
                $plan['created'][] = $newPath;
                if ($oldPath) $plan['obsolete'][] = $oldPath;
            } elseif (!empty($mentor['remove_photo'])) {
                $newPath = null;
                if ($oldPath) $plan['obsolete'][] = $oldPath;
            }
            $values = [$mentor['full_name'], $mentor['position_title'], $mentor['institution'], $mentor['role_title'], $newPath, $sortOrder];
            if ($id) {
                $mentorUpdate->execute([...$values, $id, $submissionId]);
                $keptMentors[$id] = true;
            } else {
                $mentorInsert->execute([$submissionId, ...$values]);
                $keptMentors[(int) $pdo->lastInsertId()] = true;
            }
        }
        $deleteMentor = $pdo->prepare('DELETE FROM submission_mentors WHERE id = ? AND submission_id = ?');
        foreach ($existingMentors as $id => $row) {
            if (isset($keptMentors[$id])) continue;
            $deleteMentor->execute([$id, $submissionId]);
            if ($row['profile_image_path']) $plan['obsolete'][] = $row['profile_image_path'];
        }

        $plan['created'] = array_values(array_unique($plan['created']));
        $plan['obsolete'] = array_values(array_unique($plan['obsolete']));
        if ($ownsTransaction) {
            $pdo->commit();
            finalize_submission_profile_files($plan, true, $options);
            return ['created' => [], 'obsolete' => []];
        }
        return $plan;
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        finalize_submission_profile_files($plan, false, $options);
        throw $exception;
    }
}
