<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

$_ENV['APP_ENV']    = 'testing';
$_ENV['APP_DEBUG']  = 'false';
$_ENV['APP_URL']    = 'http://localhost';
$_ENV['DB_HOST']    = '127.0.0.1';
$_ENV['DB_PORT']    = '3306';
$_ENV['DB_NAME']    = 'vending_machine_test';
$_ENV['DB_USER']    = 'root';
$_ENV['DB_PASS']    = '';
$_ENV['JWT_SECRET'] = 'test-secret-key';
$_ENV['JWT_EXPIRY'] = '3600';

if (!isset($_SESSION)) {
    $_SESSION = [];
}
