<?php
/** Salin sebagai local.php dan isi nilai pembangunan tempatan. Jangan commit local.php. */
return [
    'database' => [
        'host' => 'localhost',
        'port' => 8889,
        'socket' => '/path/to/mysql.sock',
        'database' => 'hubinovasi',
        'username' => 'your_database_user',
        'password' => 'your_database_password',
    ],
    'google' => [
        'client_id' => 'YOUR_WEB_CLIENT_ID.apps.googleusercontent.com',
        'login_uri' => '', // Blank = derive automatically from current host.
    ],
];
