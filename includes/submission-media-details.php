<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const SUBMISSION_GALLERY_TYPE = 'gallery';
const SUBMISSION_PROBLEM_VISUAL_TYPE = 'problem_visual';
const SUBMISSION_SOLUTION_VISUAL_TYPE = 'solution_visual';
const SUBMISSION_POSTER_TYPE = 'poster';
const SUBMISSION_VIDEO_LINK_TYPE = 'demo_video';
const SUBMISSION_GALLERY_LIMIT = 10;
const SUBMISSION_GALLERY_MAX_BYTES = 5 * 1024 * 1024;
const SUBMISSION_GALLERY_MAX_PIXELS = 40000000;
const SUBMISSION_GALLERY_MAX_DIMENSION = 1600;
const SUBMISSION_POSTER_MAX_DIMENSION = 2000;
const SUBMISSION_JOURNEY_LIMIT = 20;

final class SubmissionMediaException extends RuntimeException {}

function submission_media_text(mixed $value, int $maxlength): string
{
    $value = trim((string) $value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxlength) : substr($value, 0, $maxlength);
}

function submission_media_collection_definitions(): array
{
    return [
        SUBMISSION_PROBLEM_VISUAL_TYPE => ['limit'=>3, 'max_dimension'=>SUBMISSION_GALLERY_MAX_DIMENSION],
        SUBMISSION_SOLUTION_VISUAL_TYPE => ['limit'=>3, 'max_dimension'=>SUBMISSION_GALLERY_MAX_DIMENSION],
        SUBMISSION_GALLERY_TYPE => ['limit'=>SUBMISSION_GALLERY_LIMIT, 'max_dimension'=>SUBMISSION_GALLERY_MAX_DIMENSION],
        SUBMISSION_POSTER_TYPE => ['limit'=>1, 'max_dimension'=>SUBMISSION_POSTER_MAX_DIMENSION],
    ];
}

function submission_media_source_prefix(string $mediaType): string
{
    return $mediaType === SUBMISSION_GALLERY_TYPE ? 'gallery' : $mediaType;
}

function submission_media_collection_payload(array $source, string $mediaType): array
{
    $definition = submission_media_collection_definitions()[$mediaType];
    $prefix = submission_media_source_prefix($mediaType);
    $order = [];
    foreach (array_slice((array) ($source[$prefix . '_order'] ?? []), 0, $definition['limit']) as $reference) {
        $reference = trim((string) $reference);
        if (preg_match('/^(id|new):\d+$/', $reference) && !in_array($reference, $order, true)) $order[] = $reference;
    }
    $rawCaptions = is_array($source[$prefix . '_captions'] ?? null) ? $source[$prefix . '_captions'] : [];
    $captions = [];
    foreach ($order as $reference) $captions[$reference] = submission_media_text($rawCaptions[$reference] ?? '', 1000);
    return ['order'=>$order, 'captions'=>$captions];
}

function submission_video_url(mixed $value): string
{
    return submission_media_text($value, 1000);
}

function submission_video_url_is_valid(?string $url): bool
{
    $url = trim((string) $url);
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) return false;
    $parts = parse_url($url);
    if (!in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) return false;
    $host = strtolower((string) ($parts['host'] ?? ''));
    $allowed = ['youtube.com', 'youtu.be', 'vimeo.com', 'drive.google.com', 'stream.microsoft.com', 'microsoftstream.com', 'sharepoint.com'];
    foreach ($allowed as $domain) {
        if ($host === $domain || str_ends_with($host, '.' . $domain)) return true;
    }
    return false;
}

function submission_video_embed_url(?string $url): ?string
{
    if (!submission_video_url_is_valid($url)) return null;
    $parts = parse_url((string) $url);
    $host = strtolower((string) ($parts['host'] ?? ''));
    $path = trim((string) ($parts['path'] ?? ''), '/');
    if ($host === 'youtu.be' || str_ends_with($host, '.youtu.be')) $videoId = explode('/', $path)[0] ?? '';
    elseif ($host === 'youtube.com' || str_ends_with($host, '.youtube.com')) {
        parse_str((string) ($parts['query'] ?? ''), $query);
        $videoId = (string) ($query['v'] ?? (str_starts_with($path, 'embed/') ? substr($path, 6) : ''));
    } elseif ($host === 'vimeo.com' || str_ends_with($host, '.vimeo.com')) {
        $videoId = explode('/', $path)[0] ?? '';
        return ctype_digit($videoId) ? 'https://player.vimeo.com/video/' . $videoId : null;
    } else return null;
    return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $videoId) ? 'https://www.youtube-nocookie.com/embed/' . $videoId : null;
}

function submission_media_payload(array $source): array
{
    $collections = [];
    foreach (array_keys(submission_media_collection_definitions()) as $mediaType) $collections[$mediaType] = submission_media_collection_payload($source, $mediaType);
    $cover = trim((string) ($source['gallery_cover'] ?? ''));
    if (!preg_match('/^(id|new):\d+$/', $cover)) $cover = '';

    $milestones = [];
    foreach (array_slice((array) ($source['journey_records'] ?? []), 0, SUBMISSION_JOURNEY_LIMIT, true) as $key => $row) {
        if (!is_array($row)) continue;
        $title = submission_media_text($row['title'] ?? '', 255);
        $description = submission_media_text($row['description'] ?? '', 2000);
        if ($title === '' && $description === '') continue;
        $milestones[] = [
            'row_key' => preg_match('/^[A-Za-z0-9_-]{1,80}$/', (string) $key) ? (string) $key : 'journey-' . count($milestones),
            'id' => max(0, (int) ($row['id'] ?? 0)),
            'title' => $title,
            'description' => $description,
        ];
    }
    return [
        'collections'=>$collections,
        'gallery_order'=>$collections[SUBMISSION_GALLERY_TYPE]['order'],
        'cover'=>$cover,
        'video_url'=>submission_video_url($source['video_demo_url'] ?? ''),
        'milestones'=>$milestones,
    ];
}

function submission_media_validation_errors(array $payload, array|int $newUploadCounts = [], bool $requiredForReview = false): array
{
    $errors = [];
    if (is_int($newUploadCounts)) $newUploadCounts = [SUBMISSION_GALLERY_TYPE=>$newUploadCounts];
    $counts = [];
    foreach (submission_media_collection_definitions() as $mediaType => $definition) {
        $count = count($payload['collections'][$mediaType]['order'] ?? []);
        if ($count === 0) $count = (int) ($newUploadCounts[$mediaType] ?? 0);
        $counts[$mediaType] = $count;
        if ($count > $definition['limit'] || (int) ($newUploadCounts[$mediaType] ?? 0) > $definition['limit']) {
            $errors[] = $mediaType === SUBMISSION_GALLERY_TYPE ? 'Galeri hanya menerima maksimum 10 gambar.' : 'Bilangan visual bagi seksyen ini melebihi had.';
        }
    }
    if ($requiredForReview && $counts[SUBMISSION_SOLUTION_VISUAL_TYPE] < 1 && $counts[SUBMISSION_GALLERY_TYPE] < 1) $errors[] = 'Sekurang-kurangnya satu Visual Penyelesaian atau gambar Galeri Projek diperlukan.';
    $video = $payload['video_url'] ?? '';
    if ($requiredForReview && $video !== '' && !submission_video_url_is_valid($video)) $errors[] = 'URL video mesti daripada YouTube, Vimeo, Google Drive atau Microsoft Stream.';
    if ($requiredForReview && empty($payload['milestones'])) $errors[] = 'Sekurang-kurangnya satu rekod perjalanan projek diperlukan.';
    if ($requiredForReview) {
        foreach (($payload['milestones'] ?? []) as $row) {
            if ($row['title'] === '' || $row['description'] === '') $errors[] = 'Setiap rekod perjalanan memerlukan tajuk dan penerangan ringkas.';
        }
    }
    return array_values(array_unique($errors));
}

function submission_gallery_storage_options(array $options = []): array
{
    return [
        'root' => $options['root'] ?? dirname(__DIR__) . '/uploads/submissions/gallery',
        'public_prefix' => trim((string) ($options['public_prefix'] ?? 'uploads/submissions/gallery'), '/'),
        'allow_local_test_files' => !empty($options['allow_local_test_files']),
    ];
}

function submission_gallery_public_path(?string $path): ?string
{
    return $path && preg_match('#^uploads/submissions/gallery/[a-f0-9]{32}\.(?:webp|jpg|png)$#', $path) ? $path : null;
}

function submission_gallery_uploads(array $files, string $group = 'gallery_images'): array
{
    $groupFiles = $files[$group] ?? null;
    if (!is_array($groupFiles)) return [];
    $names = is_array($groupFiles['name'] ?? null) ? $groupFiles['name'] : [$groupFiles['name'] ?? ''];
    $uploads = [];
    foreach ($names as $index => $name) {
        $error = (int) (is_array($groupFiles['error'] ?? null) ? ($groupFiles['error'][$index] ?? UPLOAD_ERR_NO_FILE) : ($groupFiles['error'] ?? UPLOAD_ERR_NO_FILE));
        if ($error === UPLOAD_ERR_NO_FILE) continue;
        $uploads[(int) $index] = [
            'name' => $name,
            'tmp_name' => is_array($groupFiles['tmp_name'] ?? null) ? ($groupFiles['tmp_name'][$index] ?? '') : ($groupFiles['tmp_name'] ?? ''),
            'error' => $error,
            'size' => (int) (is_array($groupFiles['size'] ?? null) ? ($groupFiles['size'][$index] ?? 0) : ($groupFiles['size'] ?? 0)),
        ];
    }
    return $uploads;
}

function submission_gallery_replacement(array $files, string $rowKey, string $group = 'gallery_replacements'): ?array
{
    $fileGroup = $files[$group] ?? null;
    if (!is_array($fileGroup)) return null;
    $error = (int) ($fileGroup['error'][$rowKey] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) return null;
    return ['name'=>$fileGroup['name'][$rowKey] ?? '', 'tmp_name'=>$fileGroup['tmp_name'][$rowKey] ?? '', 'error'=>$error, 'size'=>(int) ($fileGroup['size'][$rowKey] ?? 0)];
}

function store_submission_gallery_image(array $file, array $options = [], int $maxDimension = SUBMISSION_GALLERY_MAX_DIMENSION): array
{
    $storage = submission_gallery_storage_options($options);
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new SubmissionMediaException('Upload gambar galeri tidak berjaya.');
    if (($file['size'] ?? 0) < 1 || (int) $file['size'] > SUBMISSION_GALLERY_MAX_BYTES) throw new SubmissionMediaException('Setiap gambar galeri mesti tidak melebihi 5MB.');
    $tmp = (string) ($file['tmp_name'] ?? '');
    if (!is_file($tmp) || (!$storage['allow_local_test_files'] && !is_uploaded_file($tmp))) throw new SubmissionMediaException('Fail gambar galeri tidak sah.');
    if (!class_exists('finfo') || !extension_loaded('gd')) throw new SubmissionMediaException('Extension Fileinfo dan GD diperlukan untuk memproses galeri.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $loaders = ['image/jpeg'=>'imagecreatefromjpeg', 'image/png'=>'imagecreatefrompng', 'image/webp'=>'imagecreatefromwebp'];
    if (!isset($loaders[$mime]) || !function_exists($loaders[$mime])) throw new SubmissionMediaException('Gambar galeri mesti berformat JPG, PNG atau WebP.');
    $dimensions = @getimagesize($tmp);
    if (!$dimensions || $dimensions[0] < 1 || $dimensions[1] < 1 || $dimensions[0] * $dimensions[1] > SUBMISSION_GALLERY_MAX_PIXELS) throw new SubmissionMediaException('Dimensi gambar galeri tidak sah atau terlalu besar.');
    $source = @$loaders[$mime]($tmp);
    if (!$source) throw new SubmissionMediaException('Gambar galeri tidak dapat dibaca.');
    $scale = min(1, max(1, $maxDimension) / max($dimensions[0], $dimensions[1]));
    $width = max(1, (int) round($dimensions[0] * $scale));
    $height = max(1, (int) round($dimensions[1] * $scale));
    $target = imagecreatetruecolor($width, $height);
    imagealphablending($target, false); imagesavealpha($target, true);
    if (!imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $dimensions[0], $dimensions[1])) {
        imagedestroy($source); imagedestroy($target); throw new SubmissionMediaException('Gambar galeri tidak dapat diproses.');
    }
    imagedestroy($source);
    if (!is_dir($storage['root']) && !mkdir($storage['root'], 0775, true) && !is_dir($storage['root'])) {
        imagedestroy($target); throw new SubmissionMediaException('Direktori galeri tidak dapat disediakan.');
    }
    $base = bin2hex(random_bytes(16));
    if (function_exists('imagewebp')) { $extension = 'webp'; $outputMime = 'image/webp'; $stored = imagewebp($target, rtrim($storage['root'], '/\\') . DIRECTORY_SEPARATOR . $base . '.webp', 84); }
    elseif ($mime === 'image/png') { $extension = 'png'; $outputMime = 'image/png'; $stored = imagepng($target, rtrim($storage['root'], '/\\') . DIRECTORY_SEPARATOR . $base . '.png', 7); }
    else { $extension = 'jpg'; $outputMime = 'image/jpeg'; $stored = imagejpeg($target, rtrim($storage['root'], '/\\') . DIRECTORY_SEPARATOR . $base . '.jpg', 86); }
    imagedestroy($target);
    $destination = rtrim($storage['root'], '/\\') . DIRECTORY_SEPARATOR . $base . '.' . $extension;
    if (!$stored || !is_file($destination)) throw new SubmissionMediaException('Gambar galeri tidak dapat disimpan.');
    @chmod($destination, 0644);
    return ['path'=>$storage['public_prefix'] . '/' . $base . '.' . $extension, 'mime_type'=>$outputMime, 'original_filename'=>submission_media_text(basename((string) ($file['name'] ?? 'image')), 255)];
}

function delete_submission_gallery_image(?string $path, array $options = []): bool
{
    if (!$path) return false;
    $storage = submission_gallery_storage_options($options);
    $prefix = $storage['public_prefix'] . '/';
    if (!str_starts_with($path, $prefix)) return false;
    $filename = substr($path, strlen($prefix));
    if (!preg_match('/^[a-f0-9]{32}\.(?:webp|jpg|png)$/', $filename)) return false;
    $target = rtrim($storage['root'], '/\\') . DIRECTORY_SEPARATOR . $filename;
    return is_file($target) ? unlink($target) : false;
}

function finalize_submission_gallery_files(array $plan, bool $committed, array $options = []): void
{
    foreach (($committed ? ($plan['obsolete'] ?? []) : ($plan['created'] ?? [])) as $path) delete_submission_gallery_image($path, $options);
}

function submission_media_details_for_submission(int $submissionId): array
{
    $empty = ['problem_visual'=>[], 'solution_visual'=>[], 'gallery'=>[], 'poster'=>[], 'video_url'=>'', 'milestones'=>[]];
    if ($submissionId < 1) return $empty;
    $details = $empty;
    $media = db()->prepare('SELECT id,file_path,original_filename,mime_type,caption,is_cover,sort_order FROM submission_media WHERE submission_id=? AND media_type=? AND file_path IS NOT NULL ORDER BY sort_order,id');
    foreach (array_keys(submission_media_collection_definitions()) as $mediaType) {
        $media->execute([$submissionId, $mediaType]);
        $rows = $media->fetchAll();
        $prefix = submission_media_source_prefix($mediaType);
        foreach ($rows as &$row) { $row['is_cover'] = (bool) $row['is_cover']; $row['row_key'] = $prefix . '-' . $row['id']; }
        unset($row);
        $details[$mediaType] = $rows;
    }
    $video = db()->prepare('SELECT url FROM submission_links WHERE submission_id=? AND link_type=? ORDER BY id LIMIT 1');
    $video->execute([$submissionId, SUBMISSION_VIDEO_LINK_TYPE]);
    $journey = db()->prepare('SELECT id,milestone_title title,milestone_description description,sort_order FROM submission_milestones WHERE submission_id=? ORDER BY sort_order,id');
    $journey->execute([$submissionId]);
    $milestones = $journey->fetchAll();
    foreach ($milestones as &$row) $row['row_key'] = 'journey-' . $row['id'];
    unset($row);
    $details['video_url'] = (string) ($video->fetchColumn() ?: '');
    $details['milestones'] = $milestones;
    return $details;
}

function save_submission_media_details(int $submissionId, array $payload, array $files = [], array $options = []): array
{
    if ($submissionId < 1) throw new InvalidArgumentException('Submission tidak sah.');
    $pdo = db(); $ownsTransaction = !$pdo->inTransaction(); $plan = ['created'=>[], 'obsolete'=>[]];
    if ($ownsTransaction) $pdo->beginTransaction();
    try {
        $query = $pdo->prepare('SELECT id,file_path,original_filename,mime_type,caption FROM submission_media WHERE submission_id=? AND media_type=? AND file_path IS NOT NULL');
        $insert = $pdo->prepare('INSERT INTO submission_media (submission_id,media_type,file_path,original_filename,mime_type,caption,is_cover,sort_order) VALUES (?,?,?,?,?,?,?,?)');
        $update = $pdo->prepare('UPDATE submission_media SET file_path=?,original_filename=?,mime_type=?,caption=?,is_cover=?,sort_order=? WHERE id=? AND submission_id=? AND media_type=?');
        $delete = $pdo->prepare('DELETE FROM submission_media WHERE id=? AND submission_id=? AND media_type=?');
        foreach (submission_media_collection_definitions() as $mediaType => $definition) {
            $prefix = submission_media_source_prefix($mediaType);
            $query->execute([$submissionId, $mediaType]);
            $existing = []; foreach ($query->fetchAll() as $row) $existing[(int) $row['id']] = $row;
            $newUploads = submission_gallery_uploads($files, $prefix . '_images');
            $collection = $payload['collections'][$mediaType] ?? ['order'=>[], 'captions'=>[]];
            $order = $collection['order'];
            if (!$order && $newUploads) foreach (array_keys($newUploads) as $index) $order[] = 'new:' . $index;
            if (count($order) > $definition['limit']) throw new SubmissionMediaException('Bilangan gambar melebihi had seksyen.');
            $kept = []; $coverRef = $mediaType === SUBMISSION_GALLERY_TYPE ? ($payload['cover'] ?? '') : '';
            foreach ($order as $sort => $reference) {
                [$kind, $number] = explode(':', $reference, 2); $number = (int) $number;
                $caption = submission_media_text($collection['captions'][$reference] ?? '', 1000);
                if ($kind === 'id') {
                    if (!isset($existing[$number]) || isset($kept[$number])) throw new SubmissionMediaException('Rekod media tidak sah.');
                    $row = $existing[$number];
                    $replacement = submission_gallery_replacement($files, $prefix . '-' . $number, $prefix . '_replacements');
                    if ($replacement) {
                        $stored = store_submission_gallery_image($replacement, $options, $definition['max_dimension']);
                        $plan['created'][] = $stored['path']; $plan['obsolete'][] = $row['file_path']; $row = array_merge($row, $stored);
                    }
                    $update->execute([$row['file_path'],$row['original_filename'],$row['mime_type'],$caption ?: null,(int) ($coverRef === $reference),$sort,$number,$submissionId,$mediaType]);
                    $kept[$number] = true;
                } else {
                    if (!isset($newUploads[$number])) throw new SubmissionMediaException('Upload media tidak sepadan dengan susunan.');
                    $stored = store_submission_gallery_image($newUploads[$number], $options, $definition['max_dimension']);
                    $plan['created'][] = $stored['path'];
                    $insert->execute([$submissionId,$mediaType,$stored['path'],$stored['original_filename'],$stored['mime_type'],$caption ?: null,(int) ($coverRef === $reference),$sort]);
                }
            }
            foreach ($existing as $id => $row) if (!isset($kept[$id])) { $delete->execute([$id,$submissionId,$mediaType]); $plan['obsolete'][] = $row['file_path']; }
            if ($mediaType === SUBMISSION_GALLERY_TYPE) {
                $pdo->prepare('UPDATE submission_media SET is_cover=0 WHERE submission_id=? AND media_type=? AND id NOT IN (SELECT chosen.id FROM (SELECT id FROM submission_media WHERE submission_id=? AND media_type=? AND is_cover=1 ORDER BY sort_order,id LIMIT 1) chosen)')->execute([$submissionId,$mediaType,$submissionId,$mediaType]);
                $hasCover = $pdo->prepare('SELECT COUNT(*) FROM submission_media WHERE submission_id=? AND media_type=? AND is_cover=1'); $hasCover->execute([$submissionId,$mediaType]);
                if (!(int) $hasCover->fetchColumn()) $pdo->prepare('UPDATE submission_media SET is_cover=1 WHERE submission_id=? AND media_type=? AND file_path IS NOT NULL ORDER BY sort_order,id LIMIT 1')->execute([$submissionId,$mediaType]);
            }
        }

        $pdo->prepare('DELETE FROM submission_links WHERE submission_id=? AND link_type=?')->execute([$submissionId,SUBMISSION_VIDEO_LINK_TYPE]);
        if (($payload['video_url'] ?? '') !== '') {
            $pdo->prepare('INSERT INTO submission_links (submission_id,link_type,label,url,sort_order) VALUES (?,?,?,?,0)')->execute([$submissionId,SUBMISSION_VIDEO_LINK_TYPE,'Video Demo',$payload['video_url']]);
        }
        $pdo->prepare('DELETE FROM submission_milestones WHERE submission_id=?')->execute([$submissionId]);
        $milestoneInsert = $pdo->prepare('INSERT INTO submission_milestones (submission_id,milestone_title,milestone_description,sort_order) VALUES (?,?,?,?)');
        foreach (($payload['milestones'] ?? []) as $sort => $row) $milestoneInsert->execute([$submissionId,$row['title'],$row['description'] ?: null,$sort]);
        $plan['created'] = array_values(array_unique($plan['created'])); $plan['obsolete'] = array_values(array_unique($plan['obsolete']));
        if ($ownsTransaction) { $pdo->commit(); finalize_submission_gallery_files($plan, true, $options); return ['created'=>[], 'obsolete'=>[]]; }
        return $plan;
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        finalize_submission_gallery_files($plan, false, $options);
        throw $exception;
    }
}
