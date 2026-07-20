<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/submission-repository.php';
require_once __DIR__ . '/../includes/submission-participants.php';
require_once __DIR__ . '/../includes/project-repository.php';

function cp10d_check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo "[PASS] {$message}\n";
}

function cp10d_upload_group(array $files): array
{
    $group = ['name' => [], 'type' => [], 'tmp_name' => [], 'error' => [], 'size' => []];
    foreach ($files as $key => $path) {
        $group['name'][$key] = basename($path);
        $group['type'][$key] = 'application/octet-stream';
        $group['tmp_name'][$key] = $path;
        $group['error'][$key] = UPLOAD_ERR_OK;
        $group['size'][$key] = filesize($path);
    }
    return $group;
}

function cp10d_remove_test_directory(string $directory): void
{
    $name = basename($directory);
    if (!str_starts_with($name, '.cp10d-test-') || !is_dir($directory)) return;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    rmdir($directory);
}

if (!extension_loaded('gd') || !function_exists('imagewebp')) {
    fwrite(STDERR, "[FAIL] GD with WebP support is required. Run this test with: php -d extension=gd scripts/test_cp10d.php\n");
    exit(1);
}

$testDirectory = dirname(__DIR__) . '/.cp10d-test-' . bin2hex(random_bytes(6));
$uploadRoot = $testDirectory . '/uploads';
mkdir($testDirectory, 0775, true);
$options = ['root' => $uploadRoot, 'public_prefix' => 'test-uploads', 'allow_local_test_files' => true];
$pdo = db();
$token = null;
$pdo->beginTransaction();
try {
    $columns = $pdo->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='submission_people' AND COLUMN_NAME IN ('class_name','year_of_study','is_team_leader')")->fetchAll(PDO::FETCH_COLUMN);
    cp10d_check(count($columns) === 3, 'CP10D student columns are installed');
    cp10d_check(array_keys(submission_student_programmes()) === ['KPD','KMK','BAK','BPM','HSK','HBP','OPP'], 'Only the seven required programmes are accepted');

    $payload = array_fill_keys(SUBMISSION_FIELDS, null);
    $payload = array_merge($payload, [
        'submitter_name' => 'CP10D Tester', 'submitter_email' => 'cp10d-' . bin2hex(random_bytes(4)) . '@example.test',
        'project_name' => 'CP10D People Project', 'institution' => 'KVKS',
        'solution_area' => 'smart-campus-safety-operations', 'innovation_type' => 'digital-solution',
        'programme_codes' => json_encode(['KPD']), 'project_development_status' => 'Functional Prototype',
        'tagline' => 'Pasukan inovasi tersusun.',
        'problem' => 'Maklumat ahli projek perlu disimpan secara tersusun untuk penerbitan dan semakan.',
        'solution' => 'Pelajar dan mentor direkodkan dengan peranan, kelas, susunan dan foto profil.',
        'how_it_works' => "Tambah individu\nSusun pasukan\nSimpan draf",
        'impact' => 'Maklumat pasukan menjadi lengkap dan boleh digunakan semula pada halaman projek.',
        'call_to_action' => 'Hubungi pasukan projek',
    ]);
    $submission = save_submission($payload, null, 'draft', null);
    $token = $submission['public_token'];

    $jpegPath = $testDirectory . '/student.jpg';
    $jpeg = imagecreatetruecolor(900, 600);
    imagefill($jpeg, 0, 0, imagecolorallocate($jpeg, 225, 0, 126));
    imagejpeg($jpeg, $jpegPath, 90);
    imagedestroy($jpeg);
    $pngPath = $testDirectory . '/replacement.png';
    $png = imagecreatetruecolor(360, 720);
    imagefill($png, 0, 0, imagecolorallocate($png, 248, 144, 36));
    imagepng($png, $pngPath);
    imagedestroy($png);

    $source = [
        'participants' => [
            'student-a' => ['full_name'=>'Pelajar Kedua','programme'=>'KMK','class_name'=>'1DVM KMK','role_title'=>'Penguji','year_of_study'=>'1DVM'],
            'student-b' => ['full_name'=>'Pelajar Ketua','programme'=>'KPD','class_name'=>'2DVM KPD','role_title'=>'Pembangun Utama','year_of_study'=>'2DVM','is_team_leader'=>'1'],
        ],
        'mentors' => [
            'mentor-a' => ['full_name'=>'Mentor Satu','position_title'=>'Pensyarah','institution'=>'KVKS','role_title'=>'Penasihat teknikal'],
            'mentor-b' => ['full_name'=>'Mentor Dua','position_title'=>'Ketua Program','institution'=>'KVKS','role_title'=>'Penasihat industri'],
        ],
    ];
    $people = submission_participants_payload($source);
    cp10d_check($people['students'][0]['full_name'] === 'Pelajar Ketua' && $people['students'][0]['is_team_leader'], 'Team leader is normalised to the first position');
    cp10d_check(submission_participants_validation_errors($people, true) === [], 'Complete student and mentor payload passes validation');
    $invalid = submission_participants_payload(['participants'=>['bad'=>['full_name'=>'Tidak Lengkap','programme'=>'XYZ']]]);
    cp10d_check(count(submission_participants_validation_errors($invalid, true)) >= 3, 'Invalid programme, class, role and study year are rejected');

    $files = [
        'participant_photos' => cp10d_upload_group(['student-b' => $jpegPath]),
        'mentor_photos' => cp10d_upload_group(['mentor-a' => $jpegPath]),
    ];
    $filePlan = save_submission_participants((int) $submission['id'], $people, $files, $options);
    $stored = submission_participants_for_submission((int) $submission['id']);
    cp10d_check(count($stored['students']) === 2 && count($stored['mentors']) === 2, 'Multiple students and mentors are persisted');
    cp10d_check($stored['students'][0]['class_name'] === '2DVM KPD', 'Class name persists and reloads from the draft');
    cp10d_check($stored['students'][0]['is_team_leader'], 'Team leader reloads first');
    $studentPhoto = $stored['students'][0]['profile_image_path'];
    $mentorPhoto = $stored['mentors'][0]['profile_image_path'];
    cp10d_check($studentPhoto !== $mentorPhoto, 'Uploaded photos use unique filenames');
    $studentPhotoFile = $uploadRoot . '/' . basename($studentPhoto);
    $processed = getimagesize($studentPhotoFile);
    cp10d_check($processed && $processed[0] === 512 && $processed[1] === 512 && $processed['mime'] === 'image/webp', 'JPG upload is center-cropped, resized to 512×512 and converted to WebP');

    $stored['students'][0]['class_name'] = '2DVM KPD A';
    $stored['students'][0]['row_key'] = 'replace-student';
    $replaceFiles = ['participant_photos' => cp10d_upload_group(['replace-student' => $pngPath])];
    $replacePlan = save_submission_participants((int) $submission['id'], $stored, $replaceFiles, $options);
    finalize_submission_profile_files($replacePlan, true, $options);
    $reloaded = submission_participants_for_submission((int) $submission['id']);
    $replacementPhoto = $reloaded['students'][0]['profile_image_path'];
    cp10d_check($reloaded['students'][0]['class_name'] === '2DVM KPD A', 'Existing student record and class name can be edited');
    cp10d_check(!is_file($studentPhotoFile) && is_file($uploadRoot . '/' . basename($replacementPhoto)), 'Replacing a photo deletes the old file');

    $remainingStudent = $reloaded['students'][1];
    $remainingStudent['row_key'] = 'remaining-student';
    $remainingMentor = $reloaded['mentors'][1];
    $remainingMentor['row_key'] = 'remaining-mentor';
    $deletePlan = save_submission_participants((int) $submission['id'], ['students'=>[$remainingStudent], 'mentors'=>[$remainingMentor]], [], $options);
    finalize_submission_profile_files($deletePlan, true, $options);
    cp10d_check(!is_file($uploadRoot . '/' . basename($replacementPhoto)), 'Removing a student record deletes its profile photo');
    cp10d_check(!is_file($uploadRoot . '/' . basename($mentorPhoto)), 'Removing a mentor record deletes its profile photo');

    $badMime = $testDirectory . '/not-image.jpg';
    file_put_contents($badMime, 'not an image');
    $mimeRejected = false;
    try { store_submission_profile_photo(['error'=>UPLOAD_ERR_OK,'size'=>filesize($badMime),'tmp_name'=>$badMime], $options); } catch (SubmissionPhotoException) { $mimeRejected = true; }
    cp10d_check($mimeRejected, 'MIME validation rejects a disguised non-image file');
    $sizeRejected = false;
    try { store_submission_profile_photo(['error'=>UPLOAD_ERR_OK,'size'=>SUBMISSION_PROFILE_PHOTO_MAX_BYTES + 1,'tmp_name'=>$jpegPath], $options); } catch (SubmissionPhotoException) { $sizeRejected = true; }
    cp10d_check($sizeRejected, 'Photo size validation rejects files over 5MB');

    $submissionForPublish = find_submission_by_id((int) $submission['id']);
    $projectId = publish_submission_as_project($pdo, $submissionForPublish);
    $pdo->prepare("UPDATE submissions SET linked_project_id=?, status='published' WHERE id=?")->execute([$projectId, (int) $submission['id']]);
    $projectSlug = $pdo->prepare('SELECT slug FROM projects WHERE id = ?');
    $projectSlug->execute([$projectId]);
    $publicProject = get_public_project_by_slug((string) $projectSlug->fetchColumn());
    cp10d_check(($publicProject['submission_people']['students'][0]['class_name'] ?? '') === '1DVM KMK', 'Published project reloads the student class name');

    $pdo->prepare('DELETE FROM submissions WHERE id = ?')->execute([(int) $submission['id']]);
    foreach (['submission_people','submission_mentors'] as $table) {
        $count = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE submission_id = ?");
        $count->execute([(int) $submission['id']]);
        cp10d_check((int) $count->fetchColumn() === 0, "Cascade delete clears {$table}");
    }
    $pdo->rollBack();
    $tokenCheck = $pdo->prepare('SELECT COUNT(*) FROM submissions WHERE public_token = ?');
    $tokenCheck->execute([$token]);
    cp10d_check((int) $tokenCheck->fetchColumn() === 0, 'CP10D database test transaction is rolled back');
    cp10d_remove_test_directory($testDirectory);
    echo "[PASS] CP10D upload fixtures and generated photos were removed\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    cp10d_remove_test_directory($testDirectory);
    fwrite(STDERR, "[FAIL] {$exception->getMessage()}\n");
    exit(1);
}
