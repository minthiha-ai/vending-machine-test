<?php

declare(strict_types=1);

namespace Core;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class JWT
{
    private static function secret(): string
    {
        $config = require BASE_PATH . '/config/app.php';
        return $config['jwt']['secret'];
    }

    private static function expiry(): int
    {
        $config = require BASE_PATH . '/config/app.php';
        return $config['jwt']['expiry'];
    }

    /**
     * Generate a signed JWT for the given payload.
     *
     * @param array<string, mixed> $payload
     */
    public static function generate(array $payload): string
    {
        $now = time();
        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + self::expiry(),
        ]);

        return FirebaseJWT::encode($payload, self::secret(), 'HS256');
    }

    /**
     * Verify and decode a JWT string.
     *
     * @throws \Exception on invalid/expired token
     */
    public static function verify(string $token): object
    {
        return FirebaseJWT::decode($token, new Key(self::secret(), 'HS256'));
    }
}
