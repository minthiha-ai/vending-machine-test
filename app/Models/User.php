<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;

class User
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function findByUsername(string $username): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT id, username, role, created_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(string $username, string $password, string $role = 'user'): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password, role) VALUES (:username, :password, :role)'
        );
        $stmt->execute([
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            ':role'     => $role,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
