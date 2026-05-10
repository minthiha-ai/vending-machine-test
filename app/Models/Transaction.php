<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;

class Transaction
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function log(int $productId, int $userId, float $totalPrice): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO transactions (product_id, user_id, total_price) VALUES (:product_id, :user_id, :total_price)'
        );
        $stmt->execute([
            ':product_id'  => $productId,
            ':user_id'     => $userId,
            ':total_price' => $totalPrice,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT t.id, t.total_price, t.created_at,
                    p.name AS product_name, u.username
             FROM transactions t
             JOIN products p ON p.id = t.product_id
             JOIN users    u ON u.id = t.user_id
             ORDER BY t.created_at DESC'
        )->fetchAll();
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.id, t.total_price, t.created_at, p.name AS product_name
             FROM transactions t
             JOIN products p ON p.id = t.product_id
             WHERE t.user_id = :user_id
             ORDER BY t.created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
