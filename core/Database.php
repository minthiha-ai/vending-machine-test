<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require BASE_PATH . '/config/app.php';
            $db     = $config['db'];

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public static function setInstance(PDO $pdo): void
    {
        self::$instance = $pdo;
    }
}
