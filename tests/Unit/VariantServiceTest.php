<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Contracts\ProductRepositoryInterface;
use Ttpryg\CatalogEngine\Contracts\VariantRepositoryInterface;
use Ttpryg\CatalogEngine\Entities\Product;
use Ttpryg\CatalogEngine\Entities\ProductVariant;
use Ttpryg\CatalogEngine\Exceptions\InsufficientStockException;
use Ttpryg\CatalogEngine\Exceptions\ProductNotFoundException;
use Ttpryg\CatalogEngine\Exceptions\VariantNotFoundException;
use Ttpryg\CatalogEngine\Services\VariantService;

class VariantServiceTest extends TestCase
{
    public function test_add_variant_successfully(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);

        $product = new Product('Sepatu', 'sepatu', 100000.0, id: 1);
        $productRepo->method('findById')->with(1)->willReturn($product);

        $variantRepo->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (ProductVariant $productVariant): \Ttpryg\CatalogEngine\Entities\ProductVariant {
                $productVariant->setId(10);

                return $productVariant;
            });

        $variantService = new VariantService($variantRepo, $productRepo);
        $productVariant = $variantService->addVariant(1, 'Merah - 42', 'SPT-RED-42', 120000.0, 5, ['color' => 'Red', 'size' => '42']);

        $this->assertEquals(10, $productVariant->getId());
        $this->assertEquals('Merah - 42', $productVariant->getName());
        $this->assertEquals('SPT-RED-42', $productVariant->getSku());
        $this->assertEquals(120000.0, $productVariant->getPrice());
        $this->assertEquals(5, $productVariant->getStock());
    }

    public function test_add_variant_throws_if_product_not_found(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);
        $productRepo->method('findById')->with(99)->willReturn(null);

        $this->expectException(ProductNotFoundException::class);

        $variantService = new VariantService($variantRepo, $productRepo);
        $variantService->addVariant(99, 'Varian');
    }

    public function test_get_variant_by_id_and_sku(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);

        $productVariant = new ProductVariant(1, 'Biru - 40', 'BLU-40', 95000.0, 10, id: 5);
        $variantRepo->method('findById')->with(5)->willReturn($productVariant);
        $variantRepo->method('findBySku')->with('BLU-40')->willReturn($productVariant);

        $variantService = new VariantService($variantRepo, $productRepo);

        $this->assertSame($productVariant, $variantService->getVariant(5));
        $this->assertSame($productVariant, $variantService->getVariantBySku('BLU-40'));
    }

    public function test_get_variant_throws_if_not_found(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);
        $variantRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(VariantNotFoundException::class);

        $variantService = new VariantService($variantRepo, $productRepo);
        $variantService->getVariant(999);
    }

    public function test_get_variants_by_product(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);

        $variants = [
            new ProductVariant(1, 'Varian A', id: 1),
            new ProductVariant(1, 'Varian B', id: 2),
        ];
        $variantRepo->method('findByProductId')->with(1)->willReturn($variants);

        $variantService = new VariantService($variantRepo, $productRepo);
        $this->assertCount(2, $variantService->getVariantsByProduct(1));
    }

    public function test_update_variant(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);

        $productVariant = new ProductVariant(1, 'Old Name', 'OLD-SKU', 100.0, 5, ['size' => 'S'], id: 1);
        $variantRepo->method('findById')->with(1)->willReturn($productVariant);
        $variantRepo->expects($this->once())->method('update')->with($productVariant)->willReturn(true);

        $variantService = new VariantService($variantRepo, $productRepo);
        $updated = $variantService->updateVariant(1, [
            'name' => 'New Name',
            'sku' => 'NEW-SKU',
            'price' => 150.0,
            'stock' => 10,
            'variant_attributes' => ['size' => 'M'],
        ]);

        $this->assertEquals('New Name', $updated->getName());
        $this->assertEquals('NEW-SKU', $updated->getSku());
        $this->assertEquals(150.0, $updated->getPrice());
        $this->assertEquals(10, $updated->getStock());
        $this->assertEquals(['size' => 'M'], $updated->getVariantAttributes());
    }

    public function test_delete_variant(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);

        $productVariant = new ProductVariant(1, 'To Delete', id: 1);
        $variantRepo->method('findById')->with(1)->willReturn($productVariant);
        $variantRepo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $variantService = new VariantService($variantRepo, $productRepo);
        $this->assertTrue($variantService->deleteVariant(1));
    }

    public function test_update_variant_stock_success(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);

        $productVariant = new ProductVariant(1, 'Varian', stock: 10, id: 1);
        $variantRepo->method('findById')->with(1)->willReturn($productVariant);
        $variantRepo->method('updateStock')->with(1, -3)->willReturn(true);

        $variantService = new VariantService($variantRepo, $productRepo);
        $result = $variantService->updateVariantStock(1, -3);

        $this->assertTrue($result);
        $this->assertEquals(7, $productVariant->getStock());
    }

    public function test_update_variant_stock_insufficient_throws_exception(): void
    {
        $variantRepo = $this->createMock(VariantRepositoryInterface::class);
        $productRepo = $this->createMock(ProductRepositoryInterface::class);

        $productVariant = new ProductVariant(1, 'Varian', stock: 2, id: 1);
        $variantRepo->method('findById')->with(1)->willReturn($productVariant);

        $this->expectException(InsufficientStockException::class);

        $variantService = new VariantService($variantRepo, $productRepo);
        $variantService->updateVariantStock(1, -5);
    }
}
