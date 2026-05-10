<?php

declare(strict_types=1);

return [
    'env'   => $_ENV['APP_ENV']   ?? 'local',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'url'   => $_ENV['APP_URL']   ?? 'http://localhost',

    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'name' => $_ENV['DB_NAME'] ?? 'vending_machine',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
    ],

    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'change-me',
        'expiry' => (int) ($_ENV['JWT_EXPIRY'] ?? 3600),
    ],
];
