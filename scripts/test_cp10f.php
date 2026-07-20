<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/submission-repository.php';
require_once __DIR__ . '/../includes/submission-media-details.php';
require_once __DIR__ . '/../includes/project-repository.php';

function cp10f_check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo "[PASS] {$message}\n";
}

function cp10f_submission_payload(string $suffix): array
{
    $payload = array_fill_keys(SUBMISSION_FIELDS, null);
    return array_merge($payload, [
        'submitter_name'=>'CP10F Tester','submitter_email'=>"cp10f-{$suffix}@example.test",'project_name'=>"CP10F Project {$suffix}",'institution'=>'KVKS',
        'solution_area'=>'smart-campus-safety-operations','innovation_type'=>'digital-solution','programme_codes'=>json_encode(['KPD']),'project_development_status'=>'Functional Prototype',
        'tagline'=>'Galeri dan perjalanan projek.','problem'=>'Projek memerlukan media ringkas untuk menerangkan hasil inovasi kepada umum.',
        'solution'=>'Galeri, satu video demo dan rekod perjalanan dipaparkan dalam halaman projek.','how_it_works'=>"Muat naik gambar\nTambah perjalanan\nSemak hasil",
        'impact'=>'Halaman projek menjadi lebih jelas dan menarik.','call_to_action'=>'Tonton demonstrasi projek',
    ]);
}

function cp10f_upload_group(array $paths): array
{
    $group = ['name'=>[], 'type'=>[], 'tmp_name'=>[], 'error'=>[], 'size'=>[]];
    foreach ($paths as $index => $path) {
        $group['name'][$index] = basename($path); $group['type'][$index] = '';
        $group['tmp_name'][$index] = $path; $group['error'][$index] = UPLOAD_ERR_OK; $group['size'][$index] = filesize($path);
    }
    return $group;
}

function cp10f_source_from_details(array $details): array
{
    $source = [];
    foreach (array_keys(submission_media_collection_definitions()) as $mediaType) {
        $prefix = submission_media_source_prefix($mediaType);
        foreach (($details[$mediaType] ?? []) as $image) {
            $reference = 'id:' . $image['id'];
            $source[$prefix . '_order'][] = $reference;
            $source[$prefix . '_captions'][$reference] = $image['caption'] ?? '';
            if ($mediaType === SUBMISSION_GALLERY_TYPE && $image['is_cover']) $source['gallery_cover'] = $reference;
        }
    }
    $source['video_demo_url'] = $details['video_url'] ?? '';
    foreach (($details['milestones'] ?? []) as $index => $row) $source['journey_records']['existing-' . $index] = ['title'=>$row['title'], 'description'=>$row['description']];
    return $source;
}

if (!extension_loaded('gd')) { fwrite(STDERR, "[FAIL] GD is required for CP10F upload tests.\n"); exit(1); }
$testRoot = __DIR__ . '/.cp10f-' . bin2hex(random_bytes(5));
$fixtureRoot = $testRoot . '/fixtures'; $uploadRoot = $testRoot . '/uploads';
mkdir($fixtureRoot, 0775, true);
$largePath = $fixtureRoot . '/large.jpg'; $smallPath = $fixtureRoot . '/small.png'; $replacementPath = $fixtureRoot . '/replacement.jpg'; $badPath = $fixtureRoot . '/not-image.jpg';
$large = imagecreatetruecolor(1900, 1000); imagefill($large, 0, 0, imagecolorallocate($large, 210, 32, 110)); imagejpeg($large, $largePath, 90); imagedestroy($large);
$small = imagecreatetruecolor(640, 480); imagefill($small, 0, 0, imagecolorallocate($small, 245, 120, 45)); imagepng($small, $smallPath); imagedestroy($small);
$replacement = imagecreatetruecolor(800, 800); imagefill($replacement, 0, 0, imagecolorallocate($replacement, 30, 80, 160)); imagejpeg($replacement, $replacementPath, 90); imagedestroy($replacement);
file_put_contents($badPath, 'not an image');
$options = ['root'=>$uploadRoot, 'public_prefix'=>'test-gallery', 'allow_local_test_files'=>true];
$pdo = db(); $createdPlans = []; $token = '';
$pdo->beginTransaction();
try {
    $column = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='submission_media' AND COLUMN_NAME='is_cover'")->fetchColumn();
    cp10f_check($column === 1, 'CP10F cover column is installed');
    cp10f_check(SUBMISSION_GALLERY_TYPE === 'gallery', 'Project gallery uses the canonical gallery media type');
    cp10f_check(submission_video_url_is_valid('https://youtu.be/example') && submission_video_url_is_valid('https://drive.google.com/file/d/example/view') && !submission_video_url_is_valid('https://example.com/video'), 'Video URL validation accepts supported providers only');
    cp10f_check(submission_video_embed_url('https://vimeo.com/123456') === 'https://player.vimeo.com/video/123456', 'YouTube and Vimeo URLs can produce a safe responsive embed URL');

    $submission = save_submission(cp10f_submission_payload(bin2hex(random_bytes(3))), null, 'draft', null);
    $submissionId = (int) $submission['id']; $token = $submission['public_token'];
    $draft = submission_media_payload(['video_demo_url'=>'https://', 'journey_records'=>['partial'=>['title'=>'Idea awal','description'=>'']]]);
    cp10f_check(submission_media_validation_errors($draft, [], false) === [], 'Partial Section 07 data is allowed for drafts');
    cp10f_check(count(submission_media_validation_errors($draft, [], true)) >= 3, 'Review rejects a missing project visual, incomplete journey and invalid video URL');
    save_submission_media_details($submissionId, $draft, [], $options);
    $draftReload = submission_media_details_for_submission($submissionId);
    cp10f_check($draftReload['video_url'] === 'https://' && $draftReload['milestones'][0]['title'] === 'Idea awal', 'Partial draft persists and reloads');

    $payload = submission_media_payload([
        'problem_visual_order'=>['new:0','new:1'], 'problem_visual_captions'=>['new:0'=>'Borang manual lama','new:1'=>'Rekod proses lama'],
        'solution_visual_order'=>['new:0'], 'solution_visual_captions'=>['new:0'=>'Dashboard penyelesaian'],
        'poster_order'=>['new:0'], 'poster_captions'=>['new:0'=>'Poster pertandingan'],
        'gallery_order'=>['new:0','new:1'], 'gallery_captions'=>['new:0'=>'Prototaip','new:1'=>'Ujian pengguna'], 'gallery_cover'=>'new:1', 'video_demo_url'=>'https://vimeo.com/123456',
        'journey_records'=>[
            'first'=>['title'=>'Idea awal','description'=>'Idea projek dibincangkan bersama mentor.'],
            'second'=>['title'=>'Prototaip pertama','description'=>'Prototaip berjaya dibina.'],
        ],
    ]);
    $uploadCounts = ['problem_visual'=>2,'solution_visual'=>1,'poster'=>1,'gallery'=>2];
    cp10f_check(submission_media_validation_errors($payload, $uploadCounts, true) === [], 'Complete Section 07 payload passes Review validation');

    $plan = save_submission_media_details($submissionId, $payload, [
        'problem_visual_images'=>cp10f_upload_group([$smallPath,$replacementPath]),
        'solution_visual_images'=>cp10f_upload_group([$largePath]),
        'poster_images'=>cp10f_upload_group([$largePath]),
        'gallery_images'=>cp10f_upload_group([$largePath,$smallPath]),
    ], $options);
    $createdPlans[] = $plan;
    $stored = submission_media_details_for_submission($submissionId);
    cp10f_check(count($stored['problem_visual']) === 2 && count($stored['solution_visual']) === 1 && count($stored['poster']) === 1 && count($stored['gallery']) === 2, 'Problem, solution, poster and gallery media types persist separately');
    cp10f_check(count($stored['milestones']) === 2 && $stored['video_url'] === 'https://vimeo.com/123456', 'Video and multiple journey records persist and reload');
    cp10f_check($stored['problem_visual'][0]['caption'] === 'Borang manual lama' && $stored['solution_visual'][0]['caption'] === 'Dashboard penyelesaian' && $stored['gallery'][1]['caption'] === 'Ujian pengguna', 'Captions persist for section visuals and gallery');
    $filenames = array_map(static fn (array $row): string => basename($row['file_path']), array_merge($stored['problem_visual'],$stored['solution_visual'],$stored['poster'],$stored['gallery']));
    cp10f_check(count($filenames) === count(array_unique($filenames)), 'Every stored image uses a unique filename');
    cp10f_check(!$stored['gallery'][0]['is_cover'] && $stored['gallery'][1]['is_cover'], 'Selected cover image persists');
    $firstFile = $uploadRoot . '/' . basename($stored['gallery'][0]['file_path']);
    $processed = getimagesize($firstFile);
    $expectedFormat = function_exists('imagewebp') ? $processed['mime'] === 'image/webp' : in_array($processed['mime'], ['image/jpeg','image/png'], true);
    cp10f_check($processed && max($processed[0], $processed[1]) === 1600 && $expectedFormat, 'Upload is resized to 1600px maximum and converted to WebP when supported');
    $posterFile = getimagesize($uploadRoot . '/' . basename($stored['poster'][0]['file_path']));
    cp10f_check($posterFile && max($posterFile[0], $posterFile[1]) === 1900, 'Poster retains detail up to its 2000px maximum');
    $mimeRejected = false; try { store_submission_gallery_image(['name'=>'fake.jpg','tmp_name'=>$badPath,'error'=>UPLOAD_ERR_OK,'size'=>filesize($badPath)], $options); } catch (SubmissionMediaException) { $mimeRejected = true; }
    cp10f_check($mimeRejected, 'MIME validation rejects a disguised non-image file');
    $sizeRejected = false; try { store_submission_gallery_image(['name'=>'large.jpg','tmp_name'=>$largePath,'error'=>UPLOAD_ERR_OK,'size'=>SUBMISSION_GALLERY_MAX_BYTES + 1], $options); } catch (SubmissionMediaException) { $sizeRejected = true; }
    cp10f_check($sizeRejected, 'File size validation rejects images over 5MB');

    $firstRef = 'id:' . $stored['gallery'][0]['id']; $secondRef = 'id:' . $stored['gallery'][1]['id'];
    $reorderSource = cp10f_source_from_details($stored);
    $reorderSource['gallery_order'] = [$secondRef,$firstRef]; $reorderSource['gallery_cover'] = $firstRef;
    $reorderSource['gallery_captions'] = [$secondRef=>'Ujian pengguna',$firstRef=>'Prototaip'];
    $reorderSource['journey_records'] = [
            'second'=>['title'=>'Prototaip pertama','description'=>'Prototaip berjaya dibina.'],
            'first'=>['title'=>'Idea awal','description'=>'Idea projek dibincangkan bersama mentor.'],
    ];
    $reordered = submission_media_payload($reorderSource);
    save_submission_media_details($submissionId, $reordered, [], $options);
    $reloaded = submission_media_details_for_submission($submissionId);
    cp10f_check((int) $reloaded['gallery'][0]['id'] === (int) $stored['gallery'][1]['id'] && $reloaded['milestones'][0]['title'] === 'Prototaip pertama', 'Gallery and journey reorder persists');
    cp10f_check((int) $reloaded['gallery'][1]['id'] === (int) $stored['gallery'][0]['id'] && $reloaded['gallery'][1]['is_cover'], 'Cover selection remains correct after reorder');

    $replaceFiles = ['gallery_replacements'=>cp10f_upload_group(['gallery-' . $reloaded['gallery'][0]['id'] => $replacementPath])];
    $replacePlan = save_submission_media_details($submissionId, $reordered, $replaceFiles, $options); $createdPlans[] = $replacePlan;
    cp10f_check(count($replacePlan['created']) === 1 && count($replacePlan['obsolete']) === 1, 'Replacing an image schedules the new file and old file cleanup');
    $replacedOldFile = $uploadRoot . '/' . basename($replacePlan['obsolete'][0]);
    finalize_submission_gallery_files($replacePlan, true, $options);
    cp10f_check(!is_file($replacedOldFile), 'Replacing an image deletes the old file after commit');

    $afterReplace = submission_media_details_for_submission($submissionId);
    $keep = $afterReplace['gallery'][0];
    $deleteSource = cp10f_source_from_details($afterReplace);
    $deleteSource['gallery_order'] = ['id:' . $keep['id']]; $deleteSource['gallery_cover'] = 'id:' . $keep['id'];
    $deleteSource['gallery_captions'] = ['id:' . $keep['id']=>$keep['caption']];
    $deleteSource['journey_records'] = [['title'=>'Ujian pengguna','description'=>'30 pelajar telah menguji sistem.']];
    $deletePayload = submission_media_payload($deleteSource);
    $deletePlan = save_submission_media_details($submissionId, $deletePayload, [], $options);
    cp10f_check(count($deletePlan['obsolete']) === 1 && count(submission_media_details_for_submission($submissionId)['gallery']) === 1, 'Deleting a gallery record schedules its file for deletion');
    $deletedFile = $uploadRoot . '/' . basename($deletePlan['obsolete'][0]);
    finalize_submission_gallery_files($deletePlan, true, $options);
    cp10f_check(!is_file($deletedFile), 'Deleting a gallery record removes its file after commit');

    $submissionRow = find_submission_by_id($submissionId);
    $projectId = publish_submission_as_project($pdo, $submissionRow);
    $pdo->prepare("UPDATE submissions SET linked_project_id=?,status='published' WHERE id=?")->execute([$projectId,$submissionId]);
    $slugQuery = $pdo->prepare('SELECT slug FROM projects WHERE id=?'); $slugQuery->execute([$projectId]);
    $publicProject = get_public_project_by_slug((string) $slugQuery->fetchColumn());
    cp10f_check(count($publicProject['submission_media']['problem_visual'] ?? []) === 2 && count($publicProject['submission_media']['solution_visual'] ?? []) === 1, 'Published project reloads problem_visual and solution_visual by their correct media types');
    $projectTemplate = file_get_contents(__DIR__ . '/../project.php');
    cp10f_check(str_contains($projectTemplate, 'story-row--text-only') && str_contains($projectTemplate, '$problemVisuals') && str_contains($projectTemplate, '$solutionVisuals'), 'Project template has responsive text-only fallback and section-specific visual mappings');

    $pdo->prepare('DELETE FROM submissions WHERE id=?')->execute([$submissionId]);
    foreach (['submission_media','submission_links','submission_milestones'] as $table) {
        $count = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE submission_id=?"); $count->execute([$submissionId]);
        cp10f_check((int) $count->fetchColumn() === 0, "Cascade delete clears {$table}");
    }
    $pdo->rollBack();
    $check = $pdo->prepare('SELECT COUNT(*) FROM submissions WHERE public_token=?'); $check->execute([$token]);
    cp10f_check((int) $check->fetchColumn() === 0, 'CP10F test transaction is rolled back');
    foreach ($createdPlans as $createdPlan) finalize_submission_gallery_files($createdPlan, false, $options);
    cp10f_check(!glob($uploadRoot . '/*'), 'CP10F generated upload files are removed');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    foreach ($createdPlans as $createdPlan) finalize_submission_gallery_files($createdPlan, false, $options);
    fwrite(STDERR, "[FAIL] {$exception->getMessage()}\n");
    exit(1);
} finally {
    $items = is_dir($testRoot) ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) : [];
    foreach ($items as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    if (is_dir($testRoot)) rmdir($testRoot);
}
