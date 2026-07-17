<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-auth.php';

if (PHP_SAPI !== 'cli') { fwrite(STDERR, "CLI sahaja.\n"); exit(1); }

function prompt_text(string $label): string
{
    fwrite(STDOUT, $label . ': ');
    return trim((string) fgets(STDIN));
}

function prompt_secret(string $label): string
{
    fwrite(STDOUT, $label . ': ');
    $hidden = DIRECTORY_SEPARATOR === '/' && function_exists('shell_exec');
    if ($hidden) shell_exec('stty -echo 2>/dev/null');
    $value = rtrim((string) fgets(STDIN), "\r\n");
    if ($hidden) { shell_exec('stty echo 2>/dev/null'); fwrite(STDOUT, "\n"); }
    return $value;
}

$name = prompt_text('Nama penuh');
$email = normalize_admin_email(prompt_text('Email'));
$password = prompt_secret('Kata laluan');
$confirmation = prompt_secret('Sahkan kata laluan');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Maklumat tidak sah. Gunakan email sah, kata laluan minimum 12 aksara dan pengesahan sepadan.\n");
    exit(1);
}

try {
    $stmt = db()->prepare("INSERT INTO admin_users (full_name,email,password_hash,role,is_active) VALUES (?,?,?,'admin',1)");
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, "Akaun admin berjaya dicipta.\n");
} catch (PDOException $exception) {
    error_log('Create admin failed: ' . $exception->getMessage());
    fwrite(STDERR, "Akaun admin tidak dapat dicipta. Pastikan email belum digunakan dan migration CP06 telah dijalankan.\n");
    exit(1);
}
