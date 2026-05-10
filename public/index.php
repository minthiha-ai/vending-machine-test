<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

Core\Env::load(BASE_PATH . '/.env');

$config = require BASE_PATH . '/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$router = new Core\Router();
$router->registerControllers([
    \App\Controllers\AuthController::class,
    \App\Controllers\ProductsController::class,
    \App\Controllers\Api\RestController::class,
]);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
