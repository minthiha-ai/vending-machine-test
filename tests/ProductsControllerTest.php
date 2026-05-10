<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\ProductsController;
use App\Models\Product;
use App\Models\Transaction;
use Core\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ProductsControllerTest extends TestCase
{
    
    /**
     * Build a ProductsController with injected mocks.
     *
     * @return array{0: ProductsController, 1: MockObject&Product, 2: MockObject&Transaction, 3: MockObject&PDO}
     */
    private function makeController(
        ?MockObject $productMock = null,
        ?MockObject $txMock = null,
        ?MockObject $pdoMock = null
    ): array {
        $productMock = $productMock ?? $this->createMock(Product::class);
        $txMock      = $txMock      ?? $this->createMock(Transaction::class);
        $pdoMock     = $pdoMock     ?? $this->createMock(PDO::class);

        $controller = new ProductsController($productMock, $txMock, $pdoMock);

        return [$controller, $productMock, $txMock, $pdoMock];
    }

    public function testValidationAcceptsValidInput(): void
    {
        [$controller] = $this->makeController();

        $method = new \ReflectionMethod($controller, 'validateProduct');
        $method->setAccessible(true);

        [$data, $errors] = $method->invoke($controller, [
            'name'               => 'Coke',
            'price'              => '3.990',
            'quantity_available' => '50',
        ]);

        $this->assertEmpty($errors);
        $this->assertSame('Coke', $data['name']);
        $this->assertSame(3.99, $data['price']);
        $this->assertSame(50, $data['quantity']);
    }

    public function testStoreValidationRejectsEmptyName(): void
    {
        [$controller] = $this->makeController();

        $method = new \ReflectionMethod($controller, 'validateProduct');
        $method->setAccessible(true);

        [, $errors] = $method->invoke($controller, [
            'name'               => '',
            'price'              => '3.99',
            'quantity_available' => '10',
        ]);

        $this->assertArrayHasKey('name', $errors);
        $this->assertStringContainsString('required', strtolower($errors['name']));
    }

    public function testStoreValidationRejectsNegativePrice(): void
    {
        [$controller] = $this->makeController();

        $method = new \ReflectionMethod($controller, 'validateProduct');
        $method->setAccessible(true);

        [, $errors] = $method->invoke($controller, [
            'name'               => 'Water',
            'price'              => '-1',
            'quantity_available' => '5',
        ]);

        $this->assertArrayHasKey('price', $errors);
        $this->assertStringContainsString('greater than 0', $errors['price']);
    }

    public function testStoreValidationRejectsZeroPrice(): void
    {
        [$controller] = $this->makeController();

        $method = new \ReflectionMethod($controller, 'validateProduct');
        $method->setAccessible(true);

        [, $errors] = $method->invoke($controller, [
            'name'               => 'Water',
            'price'              => '0',
            'quantity_available' => '5',
        ]);

        $this->assertArrayHasKey('price', $errors);
    }

    public function testStoreValidationRejectsNonNumericPrice(): void
    {
        [$controller] = $this->makeController();

        $method = new \ReflectionMethod($controller, 'validateProduct');
        $method->setAccessible(true);

        [, $errors] = $method->invoke($controller, [
            'name'               => 'Water',
            'price'              => 'abc',
            'quantity_available' => '5',
        ]);

        $this->assertArrayHasKey('price', $errors);
    }

    public function testStoreValidationRejectsNegativeQuantity(): void
    {
        [$controller] = $this->makeController();

        $method = new \ReflectionMethod($controller, 'validateProduct');
        $method->setAccessible(true);

        [, $errors] = $method->invoke($controller, [
            'name'               => 'Pepsi',
            'price'              => '6.885',
            'quantity_available' => '-1',
        ]);

        $this->assertArrayHasKey('quantity_available', $errors);
    }

    public function testStoreValidationAcceptsZeroQuantity(): void
    {
        [$controller] = $this->makeController();

        $method = new \ReflectionMethod($controller, 'validateProduct');
        $method->setAccessible(true);

        [$data, $errors] = $method->invoke($controller, [
            'name'               => 'Pepsi',
            'price'              => '6.885',
            'quantity_available' => '0',
        ]);

        $this->assertEmpty($errors);
        $this->assertSame(0, $data['quantity']);
    }

    public function testStoreValidationRejectsNameOver100Chars(): void
    {
        [$controller] = $this->makeController();

        $method = new \ReflectionMethod($controller, 'validateProduct');
        $method->setAccessible(true);

        [, $errors] = $method->invoke($controller, [
            'name'               => str_repeat('A', 101),
            'price'              => '1.00',
            'quantity_available' => '10',
        ]);

        $this->assertArrayHasKey('name', $errors);
    }

    public function testPurchaseDecrementsStockAndLogsTransaction(): void
    {
        $product = [
            'id'                 => 1,
            'name'               => 'Coke',
            'price'              => '3.990',
            'quantity_available' => 5,
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($product);
        $productMock->expects($this->once())
            ->method('decrementStock')
            ->with(1)
            ->willReturn(true);

        $txMock = $this->createMock(Transaction::class);
        $txMock->expects($this->once())
            ->method('log')
            ->with(1, 42, 3.99)
            ->willReturn(1);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())->method('beginTransaction')->willReturn(true);
        $pdoMock->expects($this->once())->method('commit')->willReturn(true);
        $pdoMock->expects($this->never())->method('rollBack');

        [$controller] = $this->makeController($productMock, $txMock, $pdoMock);

        $result = $controller->processPurchase(1, 42);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Coke', $result['message']);
        $this->assertStringContainsString('3.990', $result['message']);
    }

    public function testPurchaseFailsWhenOutOfStock(): void
    {
        $product = [
            'id'                 => 2,
            'name'               => 'Pepsi',
            'price'              => '6.885',
            'quantity_available' => 0,
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn($product);
        $productMock->expects($this->never())->method('decrementStock');

        $txMock  = $this->createMock(Transaction::class);
        $txMock->expects($this->never())->method('log');

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->never())->method('beginTransaction');

        [$controller] = $this->makeController($productMock, $txMock, $pdoMock);

        $result = $controller->processPurchase(2, 42);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('out of stock', strtolower($result['message']));
    }

    public function testPurchaseFailsWhenProductNotFound(): void
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(false);

        [$controller] = $this->makeController($productMock);

        $result = $controller->processPurchase(999, 1);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', strtolower($result['message']));
    }

    public function testPurchaseRollsBackOnException(): void
    {
        $product = [
            'id'                 => 3,
            'name'               => 'Water',
            'price'              => '0.500',
            'quantity_available' => 10,
        ];

        $productMock = $this->createMock(Product::class);
        $productMock->method('find')->willReturn($product);
        $productMock->method('decrementStock')->willThrowException(new \RuntimeException('DB error'));

        $txMock = $this->createMock(Transaction::class);
        $txMock->expects($this->never())->method('log');

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())->method('beginTransaction')->willReturn(true);
        $pdoMock->expects($this->once())->method('rollBack')->willReturn(true);
        $pdoMock->expects($this->never())->method('commit');

        [$controller] = $this->makeController($productMock, $txMock, $pdoMock);

        $result = $controller->processPurchase(3, 1);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('failed', strtolower($result['message']));
    }

    public function testProductModelPaginateBuildsCorrectResult(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchColumn')->willReturn(25);
        $stmt->method('fetchAll')->willReturn(array_fill(0, 10, ['id' => 1, 'name' => 'X', 'price' => 1.0, 'quantity_available' => 5]));
        $stmt->method('bindValue')->willReturn(true);
        $stmt->method('execute')->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('query')->willReturn($stmt);
        $pdo->method('prepare')->willReturn($stmt);

        $model  = new Product($pdo);
        $result = $model->paginate(1, 10);

        $this->assertSame(25, $result['total']);
        $this->assertSame(3, $result['pages']);
        $this->assertCount(10, $result['items']);
    }
}
