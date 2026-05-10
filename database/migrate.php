<?php

/**
 * Simple migration runner — applies schema.sql then seeds.sql.
 * Safe to run multiple times (all statements use IF NOT EXISTS / ON DUPLICATE KEY).
 *
 * Usage:  php database/migrate.php
 *         docker compose exec app php database/migrate.php
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

Core\Env::load(BASE_PATH . '/.env');

$config = require BASE_PATH . '/config/app.php';
$db     = $config['db'];

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $db['host'], $db['port'], $db['name']
);

try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    // Try without dbname to create it first
    $dsn2 = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $db['host'], $db['port']);
    $pdo  = new PDO($dsn2, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $db['name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . $db['name'] . '`');
    echo "  Created database '{$db['name']}'.\n";
}

$files = [
    BASE_PATH . '/database/schema.sql',
    BASE_PATH . '/database/seeds.sql',
];

foreach ($files as $file) {
    echo "  Running " . basename($file) . "... ";
    $sql = file_get_contents($file);

    // Split on semicolons, skip empty lines and USE statements (already connected)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => $s !== '' && !preg_match('/^\s*--/m', $s) || strlen(trim($s)) > 5
    );

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement === '' || str_starts_with($statement, '--')) continue;
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            // Ignore "table already exists" errors for idempotency
            if (!str_contains($e->getMessage(), 'already exists')) {
                echo "\n  ERROR: " . $e->getMessage() . "\n";
                echo "  Statement: " . substr($statement, 0, 80) . "\n";
            }
        }
    }
    echo "done.\n";
}

echo "\nMigrations complete.\n";
