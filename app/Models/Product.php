<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;

class Product
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Paginated, sorted list of all products.
     *
     * @return array{items: list<array>, total: int, pages: int}
     */
    public function paginate(int $page = 1, int $perPage = 10, string $sort = 'id', string $dir = 'asc'): array
    {
        $allowed = ['id', 'name', 'price', 'quantity_available'];
        $sort    = in_array($sort, $allowed, true) ? $sort : 'id';
        $dir     = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        $offset  = ($page - 1) * $perPage;

        $total = (int) $this->db
            ->query('SELECT COUNT(*) FROM products')
            ->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT * FROM products ORDER BY $sort $dir LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
        ];
    }
    public function all(string $sort = 'id', string $dir = 'asc'): array
    {
        $allowed = ['id', 'name', 'price', 'quantity_available'];
        $sort    = in_array($sort, $allowed, true) ? $sort : 'id';
        $dir     = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';

        return $this->db
            ->query("SELECT * FROM products ORDER BY $sort $dir")
            ->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(string $name, float $price, int $quantity): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (name, price, quantity_available) VALUES (:name, :price, :qty)'
        );
        $stmt->execute([':name' => $name, ':price' => $price, ':qty' => $quantity]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, float $price, int $quantity): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE products SET name=:name, price=:price, quantity_available=:qty WHERE id=:id'
        );
        return $stmt->execute([':name' => $name, ':price' => $price, ':qty' => $quantity, ':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
    
    public function decrementStock(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE products SET quantity_available = quantity_available - 1
             WHERE id = :id AND quantity_available > 0'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
