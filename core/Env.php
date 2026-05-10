<?php

declare(strict_types=1);

namespace Core;


class Env
{
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, '"\'');
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}
