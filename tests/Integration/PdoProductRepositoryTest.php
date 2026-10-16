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

    public function test_atomic_stock_deduction_prevents_negative_stock(): void
    {
        $product = new Product('Item Terbatas', 'item-terbatas', price: 100000.0, stock: 5);
        $saved = $this->pdoProductRepository->save($product);
        $id = $saved->getId();

        // Attempt to deduct 10 when stock is only 5
        $success = $this->pdoProductRepository->updateStock($id, -10);
        $this->assertFalse($success);

        // Verify stock is still 5 (not -5)
        $fresh = $this->pdoProductRepository->findById($id);
        $this->assertEquals(5, $fresh->getStock());
    }

    public function test_order_by_whitelist_and_sql_injection_defense(): void
    {
        $p1 = new Product('Product Expensive', 'prod-exp', price: 500000.0);
        $p2 = new Product('Product Cheap', 'prod-cheap', price: 100000.0);
        $this->pdoProductRepository->save($p1);
        $this->pdoProductRepository->save($p2);

        // Normal sort ascending
        $asc = $this->pdoProductRepository->findAll([], orderBy: ['price' => 'ASC']);
        $this->assertEquals('Product Cheap', $asc[0]->getName());

        // Malicious sort input should be sanitized and ignore malicious field
        $malicious = $this->pdoProductRepository->findAll([], orderBy: ['price; DROP TABLE products;--' => 'ASC']);
        $this->assertNotEmpty($malicious);
        // Products table should still exist
        $this->assertGreaterThanOrEqual(2, $this->pdoProductRepository->count());
    }

    public function test_find_all_with_filtering_criteria(): void
    {
        $p1 = new Product('Gamis Syari Batik', 'gamis-syari-batik', price: 250000.0, sku: 'GMS-01', status: 'active');
        $p2 = new Product('Kemeja Flanel Kotak', 'kemeja-flanel-kotak', price: 150000.0, sku: 'KMJ-02', status: 'active');
        $p3 = new Product('Jaket Kulit Motor', 'jaket-kulit-motor', price: 750000.0, sku: 'JKT-03', status: 'draft');

        $this->pdoProductRepository->save($p1);
        $this->pdoProductRepository->save($p2);
        $this->pdoProductRepository->save($p3);

        // Search by keyword
        $searchRes = $this->pdoProductRepository->findAll(['search' => 'Batik']);
        $this->assertCount(1, $searchRes);
        $this->assertEquals('Gamis Syari Batik', $searchRes[0]->getName());

        // Filter by price range
        $priceRes = $this->pdoProductRepository->findAll(['min_price' => 100000, 'max_price' => 200000]);
        $this->assertCount(1, $priceRes);
        $this->assertEquals('Kemeja Flanel Kotak', $priceRes[0]->getName());

        // Filter by status
        $draftRes = $this->pdoProductRepository->findAll(['status' => 'draft']);
        $this->assertCount(1, $draftRes);
        $this->assertEquals('Jaket Kulit Motor', $draftRes[0]->getName());
    }
}
