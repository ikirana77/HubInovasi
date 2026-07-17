<?php
$local = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
return ['password' => getenv('HUBINOVASI_ADMIN_PASSWORD') ?: ($local['admin_password'] ?? '')];
