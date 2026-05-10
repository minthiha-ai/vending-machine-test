<?php

declare(strict_types=1);

namespace Core;

class Middleware
{
    public static function requireAuth(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireAuth();

        if (($_SESSION['user']['role'] ?? '') !== $role) {
            http_response_code(403);
            require BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Return the currently authenticated user array or null.
     *
     * @return array{id: int, username: string, role: string}|null
     */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Validate a Bearer JWT from the Authorization header.
     * Returns the decoded payload or sends a 401 JSON response.
     *
     * @return object
     */
    public static function requireJwt(): object
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($header, 'Bearer ')) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing or invalid Authorization header']);
            exit;
        }

        $token = substr($header, 7);
        try {
            return JWT::verify($token);
        } catch (\Exception $e) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid or expired token: ' . $e->getMessage()]);
            exit;
        }
    }
}
