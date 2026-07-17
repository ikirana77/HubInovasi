<?php
/**
 * Konfigurasi MySQL. Semua nilai boleh dioverride melalui environment MAMP/Apache.
 */
$local = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
$database = $local['database'] ?? [];

return [
    'host' => getenv('HUBINOVASI_DB_HOST') ?: ($database['host'] ?? 'localhost'),
    'port' => (int) (getenv('HUBINOVASI_DB_PORT') ?: ($database['port'] ?? 8889)),
    'socket' => getenv('HUBINOVASI_DB_SOCKET') ?: ($database['socket'] ?? ''),
    'database' => getenv('HUBINOVASI_DB_NAME') ?: ($database['database'] ?? 'hubinovasi'),
    'username' => getenv('HUBINOVASI_DB_USER') ?: ($database['username'] ?? ''),
    'password' => getenv('HUBINOVASI_DB_PASS') ?: ($database['password'] ?? ''),
    'charset' => 'utf8mb4',
];
