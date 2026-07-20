<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/submission-repository.php';
require_once __DIR__ . '/../includes/submission-participants.php';
require_once __DIR__ . '/../includes/submission-mentoring-records.php';

function cp10e_check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo "[PASS] {$message}\n";
}

function cp10e_submission_payload(string $suffix): array
{
    $payload = array_fill_keys(SUBMISSION_FIELDS, null);
    return array_merge($payload, [
        'submitter_name'=>'CP10E Tester','submitter_email'=>"cp10e-{$suffix}@example.test",'project_name'=>"CP10E Project {$suffix}",'institution'=>'KVKS',
        'solution_area'=>'smart-campus-safety-operations','innovation_type'=>'digital-solution','programme_codes'=>json_encode(['KPD']),'project_development_status'=>'Functional Prototype',
        'tagline'=>'Rekod bimbingan yang ringkas.','problem'=>'Pasukan perlu merekod bimbingan tanpa borang yang panjang atau istilah teknikal.',
        'solution'=>'Setiap rekod menghubungkan mentor sedia ada dengan perkara yang dibimbing dan hasilnya.','how_it_works'=>"Pilih mentor\nCatat bimbingan\nSimpan hasil",
        'impact'=>'Bimbingan projek dapat dirujuk semula dengan mudah.','call_to_action'=>'Hubungi pasukan projek',
    ]);
}

$pdo = db();
$tokens = [];
$pdo->beginTransaction();
try {
    $tableExists = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='submission_mentoring_records'")->fetchColumn();
    cp10e_check($tableExists === 1, 'CP10E mentoring table is installed');
    cp10e_check(count(submission_guidance_types()) === 5, 'Exactly five simple guidance formats are available');

    $submissionA = save_submission(cp10e_submission_payload('a-' . bin2hex(random_bytes(3))), null, 'draft', null);
    $submissionB = save_submission(cp10e_submission_payload('b-' . bin2hex(random_bytes(3))), null, 'draft', null);
    $tokens = [$submissionA['public_token'], $submissionB['public_token']];

    $peopleA = submission_participants_payload(['mentors'=>[
        'mentor-a1'=>['full_name'=>'Mentor A1','position_title'=>'Pensyarah','institution'=>'KVKS','role_title'=>'Semakan teknikal'],
        'mentor-a2'=>['full_name'=>'Mentor A2','position_title'=>'Ketua Program','institution'=>'KVKS','role_title'=>'Semakan dokumentasi'],
    ]]);
    $peopleB = submission_participants_payload(['mentors'=>[
        'mentor-b1'=>['full_name'=>'Mentor B1','position_title'=>'Pensyarah','institution'=>'KVKS','role_title'=>'Mentor submission lain'],
    ]]);
    $planA = save_submission_participants((int) $submissionA['id'], $peopleA);
    $planB = save_submission_participants((int) $submissionB['id'], $peopleB);
    cp10e_check(count($planA['mentor_ids']) === 2, 'Multiple Section 05 mentors are available for linking');

    $partial = submission_mentoring_payload(['mentoring_records'=>['partial'=>[
        'guidance_type'=>'project_review','guidance_focus'=>'Semakan idea awal','guidance_outcome'=>'',
    ]]]);
    cp10e_check(submission_mentoring_validation_errors($partial, false) === [], 'Partial mentoring record is allowed for drafts');
    cp10e_check(count(submission_mentoring_validation_errors($partial, true)) === 2, 'Review requires only mentor, guidance focus, and outcome');
    save_submission_mentoring_records((int) $submissionA['id'], $partial, $planA['mentor_ids']);
    $partialReload = submission_mentoring_records_for_submission((int) $submissionA['id']);
    cp10e_check(count($partialReload) === 1 && $partialReload[0]['guidance_focus'] === 'Semakan idea awal', 'Partial draft persists and reloads');

    $multipleSource = ['mentoring_records'=>[
        'record-1'=>['mentor_reference'=>'ref:mentor-a1','guidance_type'=>'face_to_face','guidance_focus'=>'Semakan idea dan prototaip','guidance_outcome'=>'Aliran prototaip dipermudah'],
        'record-2'=>['mentor_reference'=>'ref:mentor-a2','guidance_type'=>'online','guidance_focus'=>'Semakan dokumentasi','guidance_outcome'=>'Laporan diperbetulkan'],
    ]];
    $multiple = submission_mentoring_payload($multipleSource);
    cp10e_check(submission_mentoring_validation_errors($multiple, true) === [], 'Complete mentoring records pass Review validation');
    save_submission_mentoring_records((int) $submissionA['id'], $multiple, $planA['mentor_ids']);
    $stored = submission_mentoring_records_for_submission((int) $submissionA['id']);
    cp10e_check(count($stored) === 2 && $stored[0]['mentor_name'] === 'Mentor A1' && $stored[1]['mentor_name'] === 'Mentor A2', 'Multiple mentoring records persist with their selected mentors');

    $stored[0]['guidance_outcome'] = 'Prototaip dan pembentangan ditambah baik';
    save_submission_mentoring_records((int) $submissionA['id'], [$stored[0]]);
    $edited = submission_mentoring_records_for_submission((int) $submissionA['id']);
    cp10e_check(count($edited) === 1 && $edited[0]['guidance_outcome'] === 'Prototaip dan pembentangan ditambah baik', 'Mentoring records can be edited and removed');

    $foreignMentorId = (int) reset($planB['mentor_ids']);
    $crossRejected = false;
    try {
        save_submission_mentoring_records((int) $submissionA['id'], submission_mentoring_payload(['mentoring_records'=>['cross'=>[
            'mentor_reference'=>'id:' . $foreignMentorId,'guidance_focus'=>'Tidak sah','guidance_outcome'=>'Tidak sah',
        ]]]));
    } catch (RuntimeException $exception) {
        $crossRejected = str_contains($exception->getMessage(), 'bukan milik submission');
    }
    cp10e_check($crossRejected, 'Application rejects a mentor belonging to another submission');

    $databaseRejected = false;
    try {
        $pdo->prepare('INSERT INTO submission_mentoring_records (submission_id,mentor_id,guidance_focus,guidance_outcome) VALUES (?,?,?,?)')
            ->execute([(int) $submissionA['id'], $foreignMentorId, 'Silang submission', 'Mesti ditolak']);
    } catch (PDOException) {
        $databaseRejected = true;
    }
    cp10e_check($databaseRejected, 'Composite foreign key rejects a cross-submission mentor');

    save_submission_mentoring_records((int) $submissionA['id'], $multiple, $planA['mentor_ids']);
    $mentorA1 = (int) $planA['mentor_ids']['mentor-a1'];
    $pdo->prepare('DELETE FROM submission_mentors WHERE id=? AND submission_id=?')->execute([$mentorA1, (int) $submissionA['id']]);
    $remaining = submission_mentoring_records_for_submission((int) $submissionA['id']);
    cp10e_check(count($remaining) === 1 && $remaining[0]['mentor_name'] === 'Mentor A2', 'Deleting a mentor cascades only its mentoring records');

    $pdo->prepare('DELETE FROM submissions WHERE id=?')->execute([(int) $submissionA['id']]);
    $recordCount = $pdo->prepare('SELECT COUNT(*) FROM submission_mentoring_records WHERE submission_id=?');
    $recordCount->execute([(int) $submissionA['id']]);
    cp10e_check((int) $recordCount->fetchColumn() === 0, 'Deleting a submission cascades all mentoring records');

    $pdo->rollBack();
    $tokenCheck = $pdo->prepare('SELECT COUNT(*) FROM submissions WHERE public_token IN (?,?)');
    $tokenCheck->execute($tokens);
    cp10e_check((int) $tokenCheck->fetchColumn() === 0, 'CP10E test transaction is rolled back');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "[FAIL] {$exception->getMessage()}\n");
    exit(1);
}
