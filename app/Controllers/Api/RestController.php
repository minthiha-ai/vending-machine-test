<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Controller;
use Core\Middleware;
use Core\Database;
use Core\Route;
use App\Models\Product;
use App\Models\Transaction;
use PDO;

class RestController extends Controller
{
    private Product     $products;
    private Transaction $transactions;
    private PDO         $db;

    public function __construct(?Product $products = null, ?Transaction $transactions = null, ?PDO $db = null)
    {
        $this->db           = $db           ?? Database::getInstance();
        $this->products     = $products     ?? new Product($this->db);
        $this->transactions = $transactions ?? new Transaction($this->db);
    }

    #[Route('/api/products', method: 'GET')]
    public function products(): void
    {
        $sort = $_GET['sort'] ?? 'id';
        $dir  = $_GET['dir']  ?? 'asc';

        $items = $this->products->all($sort, $dir);

        $this->json([
            'data' => array_map(fn($p) => [
                'id'                 => (int)   $p['id'],
                'name'               => $p['name'],
                'price'              => (float)  $p['price'],
                'quantity_available' => (int)   $p['quantity_available'],
            ], $items),
            'count' => count($items),
        ]);
    }

    #[Route('/api/purchase', method: 'POST')]
    public function purchase(): void
    {
        $payload = Middleware::requireJwt();

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $productId = isset($body['product_id']) ? (int) $body['product_id'] : 0;

        if ($productId <= 0) {
            $this->json(['error' => 'product_id is required and must be a positive integer'], 422);
        }

        $product = $this->products->find($productId);
        if (!$product) {
            $this->json(['error' => 'Product not found'], 404);
        }

        if ($product['quantity_available'] <= 0) {
            $this->json(['error' => 'Product is out of stock'], 409);
        }

        $userId = (int) $payload->sub;

        $this->db->beginTransaction();
        try {
            $decremented = $this->products->decrementStock($productId);
            if (!$decremented) {
                $this->db->rollBack();
                $this->json(['error' => 'Product just sold out'], 409);
            }

            $txId = $this->transactions->log($productId, $userId, (float) $product['price']);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->json(['error' => 'Purchase failed: ' . $e->getMessage()], 500);
        }

        $this->json([
            'message'        => 'Purchase successful',
            'transaction_id' => $txId,
            'product'        => $product['name'],
            'price'          => (float) $product['price'],
        ], 201);
    }
}
