<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        $config = require BASE_PATH . '/config/app.php';
        return rtrim($config['url'], '/') . '/' . ltrim($path, '/');
    }
}
