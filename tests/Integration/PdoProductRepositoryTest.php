<?php

namespace Ttpryg\CatalogEngine\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Entities\Product;
use Ttpryg\CatalogEngine\Repositories\PdoProductRepository;

class PdoProductRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PdoProductRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create SQLite memory table
        $this->pdo->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                sku VARCHAR(100) NULL UNIQUE,
                barcode VARCHAR(100) NULL,
                summary VARCHAR(500) NULL,
                description TEXT NULL,
                price DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
                sale_price DECIMAL(15, 2) NULL,
                cost_price DECIMAL(15, 2) NULL,
                stock INT NOT NULL DEFAULT 0,
                min_stock INT NOT NULL DEFAULT 5,
                weight_grams INT NOT NULL DEFAULT 0,
                dimensions TEXT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'draft',
                is_featured TINYINT(1) DEFAULT 0,
                attributes TEXT NULL,
                images TEXT NULL,
                view_count INT NOT NULL DEFAULT 0,
                sales_count INT NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL DEFAULT NULL
            )
        ");

        $this->repository = new PdoProductRepository($this->pdo);
    }

    // POSITIVE CASE: Save and Find
    public function testSaveAndFindProduct(): void
    {
        $product = new Product(
            name: 'Kemeja Formal Pria',
            slug: 'kemeja-formal-pria',
            price: 250000.0,
            sku: 'KFM-001',
            stock: 50,
            weightGrams: 300,
            status: 'active'
        );

        $saved = $this->repository->save($product);
        $this->assertNotNull($saved->getId());

        $foundBySlug = $this->repository->findBySlug('kemeja-formal-pria');
        $this->assertNotNull($foundBySlug);
        $this->assertEquals('Kemeja Formal Pria', $foundBySlug->getName());
        $this->assertEquals(250000.0, $foundBySlug->getPrice());

        $foundBySku = $this->repository->findBySku('KFM-001');
        $this->assertNotNull($foundBySku);
        $this->assertEquals($saved->getId(), $foundBySku->getId());
    }

    // POSITIVE CASE: Stock & Sales Increment
    public function testStockAndSalesIncrement(): void
    {
        $product = new Product('Celana Chino', 'celana-chino', price: 180000.0, stock: 20);
        $saved = $this->repository->save($product);
        $id = $saved->getId();

        $this->repository->updateStock($id, -5);
        $this->repository->incrementSales($id, 5);

        $updated = $this->repository->findById($id);
        $this->assertEquals(15, $updated->getStock());
        $this->assertEquals(5, $updated->getSalesCount());
    }

    // NEGATIVE / SOFT DELETE CASE
    public function testSoftDeleteProduct(): void
    {
        $product = new Product('Produk Hapus', 'produk-hapus', price: 50000.0);
        $saved = $this->repository->save($product);
        $id = $saved->getId();

        $this->repository->delete($id, softDelete: true);

        $this->assertNull($this->repository->findById($id, includeTrashed: false));
        $this->assertNotNull($this->repository->findById($id, includeTrashed: true));
    }
}
