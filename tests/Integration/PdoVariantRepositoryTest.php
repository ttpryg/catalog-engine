<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Entities\ProductVariant;
use Ttpryg\CatalogEngine\Repositories\PdoVariantRepository;

class PdoVariantRepositoryTest extends TestCase
{
    private PDO $pdo;

    private PdoVariantRepository $pdoVariantRepository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('
            CREATE TABLE product_variants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INT NOT NULL,
                sku VARCHAR(100) NULL UNIQUE,
                name VARCHAR(150) NOT NULL,
                price DECIMAL(15, 2) NULL,
                stock INT NOT NULL DEFAULT 0,
                variant_attributes TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');

        $this->pdoVariantRepository = new PdoVariantRepository($this->pdo);
    }

    public function test_save_find_and_list_by_product(): void
    {
        $variant1 = new ProductVariant(10, 'Red XL', 'VAR-RED-XL', 150000.0, 5, ['color' => 'Red', 'size' => 'XL']);
        $variant2 = new ProductVariant(10, 'Blue M', 'VAR-BLU-M', 140000.0, 8, ['color' => 'Blue', 'size' => 'M']);
        $variant3 = new ProductVariant(20, 'Green S', 'VAR-GRN-S', 120000.0, 3);

        $this->pdoVariantRepository->save($variant1);
        $this->pdoVariantRepository->save($variant2);
        $this->pdoVariantRepository->save($variant3);

        $byId = $this->pdoVariantRepository->findById($variant1->getId());
        $this->assertNotNull($byId);
        $this->assertEquals('Red XL', $byId->getName());
        $this->assertEquals(['color' => 'Red', 'size' => 'XL'], $byId->getVariantAttributes());

        $bySku = $this->pdoVariantRepository->findBySku('VAR-BLU-M');
        $this->assertNotNull($bySku);
        $this->assertEquals('Blue M', $bySku->getName());

        $byProduct = $this->pdoVariantRepository->findByProductId(10);
        $this->assertCount(2, $byProduct);
    }

    public function test_update_and_delete_variant(): void
    {
        $productVariant = new ProductVariant(1, 'Orig', 'SKU-01', 100.0, 10);
        $this->pdoVariantRepository->save($productVariant);
        $id = $productVariant->getId();

        $productVariant->setName('Updated Name');
        $productVariant->setPrice(200.0);
        $productVariant->setStock(15);
        $this->assertTrue($this->pdoVariantRepository->update($productVariant));

        $fresh = $this->pdoVariantRepository->findById($id);
        $this->assertEquals('Updated Name', $fresh->getName());
        $this->assertEquals(200.0, $fresh->getPrice());
        $this->assertEquals(15, $fresh->getStock());

        $this->assertTrue($this->pdoVariantRepository->delete($id));
        $this->assertNull($this->pdoVariantRepository->findById($id));
    }

    public function test_atomic_stock_update_prevents_negative(): void
    {
        $productVariant = new ProductVariant(1, 'Varian Stock', 'SKU-STK', 50.0, 3);
        $this->pdoVariantRepository->save($productVariant);
        $id = $productVariant->getId();

        // Deduct 5 from 3 should fail
        $res = $this->pdoVariantRepository->updateStock($id, -5);
        $this->assertFalse($res);
        $this->assertEquals(3, $this->pdoVariantRepository->findById($id)->getStock());

        // Deduct 2 from 3 should succeed
        $resSuccess = $this->pdoVariantRepository->updateStock($id, -2);
        $this->assertTrue($resSuccess);
        $this->assertEquals(1, $this->pdoVariantRepository->findById($id)->getStock());
    }
}
