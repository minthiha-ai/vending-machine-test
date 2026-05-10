<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use Core\Middleware;
use Core\Route;
use App\Models\Product;
use App\Models\Transaction;
use PDO;

class ProductsController extends Controller
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

    #[Route('/', method: 'GET')]
    public function storefront(): void
    {
        Middleware::requireAuth();

        $sort     = $_GET['sort'] ?? 'id';
        $dir      = $_GET['dir']  ?? 'asc';
        $products = $this->products->all($sort, $dir);

        $this->render('products/storefront', [
            'products' => $products,
            'sort'     => $sort,
            'dir'      => $dir,
        ]);
    }
    
    #[Route('/products', method: 'GET')]
    public function index(): void
    {
        Middleware::requireRole('admin');

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $sort    = $_GET['sort'] ?? 'id';
        $dir     = $_GET['dir']  ?? 'asc';
        $perPage = 10;

        $result = $this->products->paginate($page, $perPage, $sort, $dir);

        $this->render('products/index', [
            'items'   => $result['items'],
            'total'   => $result['total'],
            'pages'   => $result['pages'],
            'page'    => $page,
            'sort'    => $sort,
            'dir'     => $dir,
        ]);
    }

    
    #[Route('/products/create', method: 'GET')]
    public function create(): void
    {
        Middleware::requireRole('admin');
        $this->render('products/create', ['errors' => []]);
    }

    #[Route('/products/create', method: 'POST')]
    public function store(): void
    {
        Middleware::requireRole('admin');

        [$data, $errors] = $this->validateProduct($_POST);

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            $this->render('products/create', ['errors' => $errors]);
            return;
        }

        $this->products->create($data['name'], $data['price'], $data['quantity']);
        $this->flash('success', 'Product created successfully.');
        $this->redirect('/products');
    }

    #[Route('/products/{id}/edit', method: 'GET')]
    public function edit(string $id): void
    {
        Middleware::requireRole('admin');

        $product = $this->products->find((int) $id);
        if (!$product) {
            $this->notFound();
        }

        $this->render('products/edit', ['product' => $product, 'errors' => []]);
    }

    #[Route('/products/{id}/edit', method: 'POST')]
    public function update(string $id): void
    {
        Middleware::requireRole('admin');

        $product = $this->products->find((int) $id);
        if (!$product) {
            $this->notFound();
        }

        [$data, $errors] = $this->validateProduct($_POST);

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            $this->render('products/edit', ['product' => $product, 'errors' => $errors]);
            return;
        }

        $this->products->update((int) $id, $data['name'], $data['price'], $data['quantity']);
        $this->flash('success', 'Product updated successfully.');
        $this->redirect('/products');
    }

    #[Route('/products/{id}/delete', method: 'POST')]
    public function destroy(string $id): void
    {
        Middleware::requireRole('admin');

        $product = $this->products->find((int) $id);
        if (!$product) {
            $this->notFound();
        }

        $this->products->delete((int) $id);
        $this->flash('success', 'Product deleted.');
        $this->redirect('/products');
    }

    
    #[Route('/buy/{id}', method: 'POST')]
    public function purchase(string $id): void
    {
        Middleware::requireAuth();

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        $result = $this->processPurchase((int) $id, $userId);

        $this->flash($result['success'] ? 'success' : 'danger', $result['message']);
        $this->redirect('/');
    }

    /**
     * Atomic purchase business logic — separated for testability.
     *
     * @return array{success: bool, message: string}
     */
    public function processPurchase(int $productId, int $userId): array
    {
        $product = $this->products->find($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if ($product['quantity_available'] <= 0) {
            return ['success' => false, 'message' => 'Sorry, "' . $product['name'] . '" is out of stock.'];
        }

        $this->db->beginTransaction();
        try {
            $decremented = $this->products->decrementStock($productId);
            if (!$decremented) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Sorry, "' . $product['name'] . '" just sold out.'];
            }

            $this->transactions->log($productId, $userId, (float) $product['price']);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Purchase failed. Please try again.'];
        }

        return [
            'success' => true,
            'message' => 'You purchased "' . $product['name'] . '" for $' . number_format((float) $product['price'], 3) . '!',
        ];
    }

    
    /**
     * Validate product form input.
     *
     * @return array{0: array{name: string, price: float, quantity: int}, 1: array<string, string>}
     */
    private function validateProduct(array $input): array
    {
        $errors = [];
        $name   = trim($input['name'] ?? '');
        $price  = $input['price'] ?? '';
        $qty    = $input['quantity_available'] ?? '';

        if ($name === '') {
            $errors['name'] = 'Product name is required.';
        } elseif (strlen($name) > 100) {
            $errors['name'] = 'Product name must be 100 characters or fewer.';
        }

        if ($price === '' || !is_numeric($price)) {
            $errors['price'] = 'Price must be a valid number.';
        } elseif ((float) $price <= 0) {
            $errors['price'] = 'Price must be greater than 0.';
        }

        if ($qty === '' || !ctype_digit((string) $qty)) {
            $errors['quantity_available'] = 'Quantity must be a non-negative integer.';
        } elseif ((int) $qty < 0) {
            $errors['quantity_available'] = 'Quantity cannot be negative.';
        }

        $data = [
            'name'     => $name,
            'price'    => (float) $price,
            'quantity' => (int) $qty,
        ];

        return [$data, $errors];
    }

    private function notFound(): never
    {
        http_response_code(404);
        require BASE_PATH . '/views/errors/404.php';
        exit;
    }
}
