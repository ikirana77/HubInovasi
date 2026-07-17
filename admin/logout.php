<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !admin_is_authenticated() || !verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    exit('Kaedah tidak dibenarkan.');
}
destroy_session();
header('Location: login.php');
exit;
