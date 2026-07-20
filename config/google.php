<?php
declare(strict_types=1);

$local = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
$google = is_array($local['google'] ?? null) ? $local['google'] : [];

return [
    // Client ID is a public browser identifier, not a client secret.
    'client_id' => getenv('HUBINOVASI_GOOGLE_CLIENT_ID') ?: ($google['client_id'] ?? '426124791911-um3dverq6vdls80408vv2fao1qqci041.apps.googleusercontent.com'),
    // Leave blank to derive the localhost/production URL automatically.
    'login_uri' => getenv('HUBINOVASI_GOOGLE_LOGIN_URI') ?: ($google['login_uri'] ?? ''),
];
