<?php
require_once __DIR__ . '/includes/user-auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !user_is_authenticated() || !verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    exit('Kaedah tidak dibenarkan.');
}
clear_user_session();
header('Location: login.php');
exit;
