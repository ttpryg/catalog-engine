<?php

namespace Ttpryg\CatalogEngine\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Entities\Product;
use Ttpryg\CatalogEngine\Repositories\PdoProductRepository;

class PdoProductRepositoryTest extends TestCase
{
    private PDO $pdo;

    private PdoProductRepository $pdoProductRepository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create SQLite memory table
        $this->pdo->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INT NULL,
                owner_id INT NULL,
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

        $this->pdoProductRepository = new PdoProductRepository($this->pdo);
    }

    // POSITIVE CASE: Save and Find by Store ID & Owner ID
    public function test_save_and_find_by_store_and_owner_id(): void
    {
        $product1 = new Product(
            name: 'Kemeja Store A',
            slug: 'kemeja-store-a',
            price: 250000.0,
            storeId: 10,
            ownerId: 42,
            sku: 'KFM-001',
            status: 'active'
        );

        $product2 = new Product(
            name: 'Sepatu Store B',
            slug: 'sepatu-store-b',
            price: 500000.0,
            storeId: 20,
            ownerId: 42,
            sku: 'SPT-002',
            status: 'active'
        );

        $this->pdoProductRepository->save($product1);
        $this->pdoProductRepository->save($product2);

        $storeAProducts = $this->pdoProductRepository->findByStoreId(10);
        $this->assertCount(1, $storeAProducts);
        $this->assertEquals('Kemeja Store A', $storeAProducts[0]->getName());

        $ownerProducts = $this->pdoProductRepository->findByOwnerId(42);
        $this->assertCount(2, $ownerProducts);
    }

    // POSITIVE CASE: Stock & Sales Increment
    public function test_stock_and_sales_increment(): void
    {
        $product = new Product('Celana Chino', 'celana-chino', price: 180000.0, stock: 20);
        $saved = $this->pdoProductRepository->save($product);
        $id = $saved->getId();

        $this->pdoProductRepository->updateStock($id, -5);
        $this->pdoProductRepository->incrementSales($id, 5);

        $updated = $this->pdoProductRepository->findById($id);
        $this->assertEquals(15, $updated->getStock());
        $this->assertEquals(5, $updated->getSalesCount());
    }

    // NEGATIVE / SOFT DELETE CASE
    public function test_soft_delete_product(): void
    {
        $product = new Product('Produk Hapus', 'produk-hapus', price: 50000.0);
        $saved = $this->pdoProductRepository->save($product);
        $id = $saved->getId();

        $this->pdoProductRepository->delete($id, softDelete: true);

        $this->assertNull($this->pdoProductRepository->findById($id, includeTrashed: false));
        $this->assertNotNull($this->pdoProductRepository->findById($id, includeTrashed: true));
    }
}
