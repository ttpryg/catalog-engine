<?php

namespace Ttpryg\CatalogEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Contracts\EventDispatcherInterface;
use Ttpryg\CatalogEngine\Contracts\ProductRepositoryInterface;
use Ttpryg\CatalogEngine\Contracts\SlugGeneratorInterface;
use Ttpryg\CatalogEngine\Entities\Product;
use Ttpryg\CatalogEngine\Events\LowStockDetectedEvent;
use Ttpryg\CatalogEngine\Events\ProductCreatedEvent;
use Ttpryg\CatalogEngine\Events\ProductStockUpdatedEvent;
use Ttpryg\CatalogEngine\Exceptions\InsufficientStockException;
use Ttpryg\CatalogEngine\Exceptions\ProductNotFoundException;
use Ttpryg\CatalogEngine\Services\ProductService;

class ProductServiceTest extends TestCase
{
    // POSITIVE CASE: Create Product
    public function test_successful_product_creation(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $slugGen = $this->createMock(SlugGeneratorInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $slugGen->method('generate')->with('Laptop Gaming')->willReturn('laptop-gaming');
        $repo->method('findBySlug')->willReturn(null);

        $repo->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Product $p) {
                $p->setId(1);

                return $p;
            });

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ProductCreatedEvent::class));

        $service = new ProductService($repo, $slugGen, $dispatcher);
        $product = $service->createProduct('Laptop Gaming', price: 15000000.0, stock: 10);

        $this->assertEquals(1, $product->getId());
        $this->assertEquals('laptop-gaming', $product->getSlug());
        $this->assertEquals(15000000.0, $product->getPrice());
    }

    // POSITIVE CASE & EVENT: Stock Update & Low Stock Detection
    public function test_stock_deduction_triggers_low_stock_event(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $product = new Product('Mouse Wireless', 'mouse-wireless', price: 150000.0, stock: 6, minStock: 5, id: 1);
        $repo->method('findById')->with(1)->willReturn($product);
        $repo->method('updateStock')->with(1, -2)->willReturn(true);

        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (object $event) {
                if ($event instanceof ProductStockUpdatedEvent) {
                    $this->assertEquals(6, $event->previousStock);
                    $this->assertEquals(4, $event->newStock);
                } elseif ($event instanceof LowStockDetectedEvent) {
                    $this->assertEquals(4, $event->currentStock);
                    $this->assertEquals(5, $event->minStockThreshold);
                }
            });

        $service = new ProductService($repo, null, $dispatcher);
        $result = $service->updateStock(1, -2);

        $this->assertTrue($result);
    }

    // NEGATIVE CASE: Deduct Stock Exceeds Stock Throws InsufficientStockException
    public function test_insufficient_stock_throws_exception(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);

        $product = new Product('Keyboard', 'keyboard', price: 300000.0, stock: 3, id: 2);
        $repo->method('findById')->with(2)->willReturn($product);

        $this->expectException(InsufficientStockException::class);

        $service = new ProductService($repo);
        $service->updateStock(2, -5);
    }

    // NEGATIVE CASE: Non Existent Product Throws ProductNotFoundException
    public function test_update_price_fails_on_non_existent_product(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findById')->with(999)->willReturn(null);

        $this->expectException(ProductNotFoundException::class);

        $service = new ProductService($repo);
        $service->updatePrice(999, 200000.0);
    }
}
