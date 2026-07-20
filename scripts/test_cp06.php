<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/submission-repository.php';

function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
    echo "[PASS] {$message}\n";
}

$pdo = db();
$pdo->beginTransaction();
try {
    $password = 'CP06-Secure-Test-Password!';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $email = 'cp06-' . bin2hex(random_bytes(4)) . '@example.test';
    $stmt = $pdo->prepare("INSERT INTO admin_users(full_name,email,password_hash,role,is_active) VALUES(?,?,?,'admin',1)");
    $stmt->execute(['CP06 Admin', $email, $hash]);
    $adminId = (int) $pdo->lastInsertId();
    check($hash !== $password && str_starts_with($hash, '$'), 'Admin password stored as a hash');
    check(password_verify($password, $hash), 'password_verify accepts correct password');
    check(!password_verify('Wrong-Password', $hash), 'Wrong password is rejected');
    check(authenticate_admin($email, $password, login_identifier_hash('test-ip-active')) !== null, 'Active admin can authenticate');

    $inactiveEmail = 'inactive-' . bin2hex(random_bytes(4)) . '@example.test';
    $stmt->execute(['Inactive Admin', $inactiveEmail, $hash]);
    $inactiveId = (int) $pdo->lastInsertId();
    $pdo->prepare('UPDATE admin_users SET is_active=0 WHERE id=?')->execute([$inactiveId]);
    check(authenticate_admin($inactiveEmail, $password, login_identifier_hash('test-ip-inactive')) === null, 'Inactive admin cannot authenticate');

    $rateEmail = 'rate-' . bin2hex(random_bytes(4)) . '@example.test';
    $rateIp = login_identifier_hash('rate-test-ip');
    for ($i=0;$i<LOGIN_MAX_FAILURES;$i++) record_login_attempt($rateEmail, false, $rateIp);
    check(login_is_rate_limited($rateEmail, $rateIp), 'Login rate limit activates after five failures');

    $payload = array_fill_keys(SUBMISSION_FIELDS, null);
    $payload = array_merge($payload, [
        'submitter_name'=>'CP06 Submitter','submitter_email'=>'submitter@example.test','project_name'=>'CP06 Review Test',
        'institution'=>'KVKS','solution_area'=>'smart-campus-safety-operations','innovation_type'=>'digital-solution','programme_codes'=>json_encode(['KPD']),'project_development_status'=>'Functional Prototype',
        'tagline'=>'Submission lengkap untuk ujian terkawal.','problem'=>'Masalah sebenar diterangkan dengan konteks yang mencukupi.',
        'solution'=>'Penyelesaian menerangkan manfaat dan cara menangani masalah.','how_it_works'=>"Pengguna membuka aplikasi\nSistem memproses input",
        'key_features'=>"Ciri satu\nCiri dua",'impact'=>'Impak yang dijangka diterangkan tanpa statistik rekaan.',
        'evidence_status'=>'Belum diuji','technologies'=>'PHP, MySQL','project_journey'=>'Prototaip dibina','call_to_action'=>'Hubungi pasukan projek',
    ]);
    $draft = save_submission($payload, null, 'draft');
    $pending = save_submission($payload, $draft['public_token'], 'pending_review');

    check(can_transition_submission('pending_review','needs_revision'), 'Valid transition is accepted');
    check(!can_transition_submission('pending_review','draft'), 'Invalid transition is rejected');
    check(!transition_submission_status((int)$pending['id'],'needs_revision',$adminId,'')['success'], 'needs_revision without note is rejected');
    check(!transition_submission_status((int)$pending['id'],'archived',$adminId,'')['success'], 'archived without note is rejected');

    $published = transition_submission_status((int)$pending['id'],'published',$adminId,null);
    check($published['success'], 'Complete submission publication succeeds');
    $publishedRow = find_submission_by_id((int)$pending['id']);
    check(!empty($publishedRow['linked_project_id']), 'Published submission links to a project');
    $linkedId = (int)$publishedRow['linked_project_id'];
    $before = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    publish_submission_as_project($pdo, $publishedRow);
    $after = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    check($before === $after && $linkedId === (int)$publishedRow['linked_project_id'], 'Linked project is updated without duplication');
    check(count(submission_status_history((int)$pending['id'])) === 1, 'Audit trail is recorded');

    $incomplete = $payload;
    $incomplete['problem'] = null;
    $incompleteDraft = save_submission($incomplete, null, 'draft');
    $pdo->prepare("UPDATE submissions SET status='pending_review' WHERE id=?")->execute([$incompleteDraft['id']]);
    check(!transition_submission_status((int)$incompleteDraft['id'],'published',$adminId,null)['success'], 'Incomplete publication is rejected');

    for ($i=1;$i<=12;$i++) {
        $row = $payload; $row['project_name'] = 'Searchable CP06 Project ' . $i;
        save_submission($row, null, 'draft');
    }
    $filtered = search_submissions('published','CP06 Review Test',1,10);
    check($filtered['total'] === 1, 'Dashboard status query works');
    $searched = search_submissions('','Searchable CP06 Project',1,5);
    check($searched['total'] === 12, 'Dashboard search works');
    check($searched['pages'] === 3 && count($searched['items']) === 5, 'Dashboard pagination works');

    $pdo->rollBack();
    echo "[PASS] CP06 test transaction rolled back\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "[FAIL] {$exception->getMessage()}\n");
    exit(1);
}
