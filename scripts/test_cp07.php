<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/user-repository.php';
require_once __DIR__ . '/../includes/submission-repository.php';

function cp07_check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
    echo "[PASS] {$message}\n";
}

$pdo = db();
$pdo->beginTransaction();
try {
    $adminEmail = 'cp07-admin-' . bin2hex(random_bytes(4)) . '@example.test';
    $pdo->prepare("INSERT INTO admin_users(full_name,email,password_hash,role,is_active) VALUES(?,?,?,'admin',1)")
        ->execute(['CP07 Admin',$adminEmail,password_hash('CP07-Admin-Password!',PASSWORD_DEFAULT)]);
    $adminId = (int) $pdo->lastInsertId();

    $email = 'cp07-user-' . bin2hex(random_bytes(4)) . '@example.test';
    $registration = register_user_account([
        'full_name'=>'CP07 Student','email'=>$email,'role'=>'student','institution'=>'KVKS',
        'programme_or_position'=>'2 DVM KPD','password'=>'CP07-User-Password!','password_confirmation'=>'CP07-User-Password!',
    ]);
    cp07_check($registration['success'], 'User registration succeeds');
    $user = find_user_by_email($email);
    cp07_check($user && $user['account_status']==='pending', 'New account starts as pending');
    cp07_check(!authenticate_user($email,'CP07-User-Password!')['success'], 'Pending account cannot login');

    $approval = transition_user_status((int)$user['id'],'active',$adminId,'Automated CP07 approval');
    cp07_check($approval['success'], 'Admin can activate pending user');
    $login = authenticate_user($email,'CP07-User-Password!');
    cp07_check($login['success'], 'Active user can login');

    $payload = array_fill_keys(SUBMISSION_FIELDS, null);
    $payload = array_merge($payload, [
        'submitter_name'=>'CP07 Student','submitter_email'=>$email,'project_name'=>'CP07 Owned Project',
        'institution'=>'KVKS','solution_area'=>'smart-campus-safety-operations','innovation_type'=>'digital-solution','programme_codes'=>json_encode(['KPD']),'project_development_status'=>'Functional Prototype',
        'tagline'=>'Pitch berasaskan akaun dan pemilikan projek.','problem'=>'Masalah memerlukan workflow submission yang mempunyai pemilik sah.',
        'solution'=>'Submission dikaitkan dengan akaun pengguna yang aktif.','how_it_works'=>'Login\nCipta draf\nHantar semakan',
        'impact'=>'Mengelakkan pengguna menyunting submission milik orang lain.','call_to_action'=>'Hubungi pasukan projek',
    ]);
    $draft = save_submission($payload,null,'draft',(int)$user['id']);
    cp07_check((int)$draft['owner_user_id']===(int)$user['id'], 'Submission is linked to owner user');
    cp07_check(find_submission_for_owner($draft['public_token'],(int)$user['id']) !== null, 'Owner can retrieve own submission');

    $otherEmail = 'cp07-other-' . bin2hex(random_bytes(4)) . '@example.test';
    $pdo->prepare("INSERT INTO users(full_name,email,password_hash,role,account_status) VALUES(?,?,?,'student','active')")
        ->execute(['Other User',$otherEmail,password_hash('Other-Password-123!',PASSWORD_DEFAULT)]);
    $otherId = (int)$pdo->lastInsertId();
    cp07_check(find_submission_for_owner($draft['public_token'],$otherId) === null, 'Other user cannot retrieve owned submission');
    $blocked = false;
    try { save_submission($payload,$draft['public_token'],'draft',$otherId); } catch (RuntimeException) { $blocked = true; }
    cp07_check($blocked, 'Other user cannot update owned submission');
    cp07_check(user_submission_summary_counts((int)$user['id'])['draft']===1, 'User dashboard summary counts own draft');
    cp07_check(count(user_submissions((int)$user['id']))===1, 'User dashboard lists only own submission');

    $suspension = transition_user_status((int)$user['id'],'suspended',$adminId,'Automated CP07 suspension');
    cp07_check($suspension['success'], 'Admin can suspend active user');
    cp07_check(!authenticate_user($email,'CP07-User-Password!')['success'], 'Suspended user cannot login');

    $pdo->rollBack();
    echo "[PASS] CP07 test transaction rolled back\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR,"[FAIL] {$exception->getMessage()}\n");
    exit(1);
}
