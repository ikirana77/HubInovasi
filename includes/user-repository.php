<?php
declare(strict_types=1);
require_once __DIR__ . '/user-auth.php';

function user_role_label(string $role): string
{
    return ['student' => tr('Pelajar', 'Student'), 'lecturer' => tr('Pensyarah', 'Lecturer')][$role] ?? $role;
}

function user_status_label(string $status): string
{
    return ['pending' => tr('Menunggu Kelulusan', 'Awaiting Approval'), 'active' => tr('Aktif', 'Active'), 'suspended' => tr('Digantung', 'Suspended')][$status] ?? $status;
}

function user_summary_counts(): array
{
    $counts = ['all' => 0, 'pending' => 0, 'active' => 0, 'suspended' => 0];
    $stmt = db()->query('SELECT account_status, COUNT(*) total FROM users GROUP BY account_status');
    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['account_status']] = (int) $row['total'];
        $counts['all'] += (int) $row['total'];
    }
    return $counts;
}

function search_users(string $status = '', string $query = '', int $page = 1, int $perPage = 15): array
{
    $conditions = [];
    $params = [];
    if (in_array($status, ['pending','active','suspended'], true)) {
        $conditions[] = 'account_status = ?';
        $params[] = $status;
    }
    if ($query !== '') {
        $conditions[] = '(full_name LIKE ? OR email LIKE ? OR institution LIKE ? OR programme_or_position LIKE ?)';
        $term = '%' . $query . '%';
        array_push($params, $term, $term, $term, $term);
    }
    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    $count = db()->prepare('SELECT COUNT(*) FROM users' . $where);
    $count->execute($params);
    $total = (int) $count->fetchColumn();
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    $offset = ($page - 1) * $perPage;
    $stmt = db()->prepare('SELECT id,full_name,email,role,account_status,institution,programme_or_position,last_login_at,created_at,approved_at,auth_provider,avatar_url,google_email FROM users' . $where . ' ORDER BY created_at DESC LIMIT ? OFFSET ?');
    $position = 1;
    foreach ($params as $param) $stmt->bindValue($position++, $param, PDO::PARAM_STR);
    $stmt->bindValue($position++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($position, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
}

/** @return array{success:bool,message:string} */
function transition_user_status(int $userId, string $targetStatus, int $adminId, ?string $notes = null): array
{
    if (!in_array($targetStatus, ['active','suspended'], true) || $userId < 1 || $adminId < 1) {
        return ['success' => false, 'message' => 'Tindakan akaun tidak sah.'];
    }
    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? FOR UPDATE');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) throw new RuntimeException('Pengguna tidak dijumpai.');
        $from = (string) $user['account_status'];
        $allowed = [
            'pending' => ['active','suspended'],
            'active' => ['suspended'],
            'suspended' => ['active'],
        ];
        if (!in_array($targetStatus, $allowed[$from] ?? [], true)) {
            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Perubahan status akaun tidak dibenarkan.'];
        }
        $approvedAt = $targetStatus === 'active' ? 'CURRENT_TIMESTAMP' : 'approved_at';
        $approvedBy = $targetStatus === 'active' ? $adminId : ($user['approved_by_admin_id'] ?? null);
        $update = $pdo->prepare("UPDATE users SET account_status = ?, approved_at = {$approvedAt}, approved_by_admin_id = ? WHERE id = ?");
        $update->execute([$targetStatus, $approvedBy, $userId]);
        $history = $pdo->prepare('INSERT INTO user_account_history (user_id,from_status,to_status,admin_user_id,admin_notes) VALUES (?,?,?,?,?)');
        $history->execute([$userId, $from, $targetStatus, $adminId, trim((string) $notes) ?: null]);
        if ($ownsTransaction) {
            $pdo->commit();
        }
        return ['success' => true, 'message' => $targetStatus === 'active' ? 'Akaun pengguna telah diaktifkan.' : 'Akaun pengguna telah digantung.'];
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('User status transition failed: ' . $exception->getMessage());
        return ['success' => false, 'message' => 'Status akaun tidak dapat dikemas kini.'];
    }
}

function update_user_profile(int $userId, array $source): array
{
    $institution = trim((string) ($source['institution'] ?? ''));
    $programme = trim((string) ($source['programme_or_position'] ?? ''));
    if (mb_strlen($institution) > 255 || mb_strlen($programme) > 180) {
        return ['success' => false, 'message' => tr('Maklumat profil terlalu panjang.', 'The profile information is too long.')];
    }
    $stmt = db()->prepare('UPDATE users SET institution = ?, programme_or_position = ? WHERE id = ?');
    $stmt->execute([$institution ?: null, $programme ?: null, $userId]);
    return ['success' => true, 'message' => tr('Profil berjaya dikemas kini.', 'Profile updated successfully.')];
}
