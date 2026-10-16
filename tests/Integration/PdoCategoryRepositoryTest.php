<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Entities\ProductCategory;
use Ttpryg\CatalogEngine\Repositories\PdoCategoryRepository;

class PdoCategoryRepositoryTest extends TestCase
{
    private PDO $pdo;

    private PdoCategoryRepository $pdoCategoryRepository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('
            CREATE TABLE product_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                parent_id INT NULL,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE product_category_pivot (
                product_id INT NOT NULL,
                category_id INT NOT NULL,
                PRIMARY KEY (product_id, category_id)
            );
        ');

        $this->pdoCategoryRepository = new PdoCategoryRepository($this->pdo);
    }

    public function test_save_find_and_list_all(): void
    {
        $parent = new ProductCategory('Pakaian', 'pakaian', description: 'Kategori utama');
        $this->pdoCategoryRepository->save($parent);

        $child = new ProductCategory('Kemeja', 'kemeja', parentId: (int) $parent->getId());
        $this->pdoCategoryRepository->save($child);

        $byId = $this->pdoCategoryRepository->findById($parent->getId());
        $this->assertNotNull($byId);
        $this->assertEquals('Pakaian', $byId->getName());

        $bySlug = $this->pdoCategoryRepository->findBySlug('kemeja');
        $this->assertNotNull($bySlug);
        $this->assertEquals('Kemeja', $bySlug->getName());

        $all = $this->pdoCategoryRepository->findAll();
        $this->assertCount(2, $all);

        $childrenOnly = $this->pdoCategoryRepository->findAll((int) $parent->getId());
        $this->assertCount(1, $childrenOnly);
        $this->assertEquals('Kemeja', $childrenOnly[0]->getName());
    }

    public function test_attach_and_detach_from_product(): void
    {
        $cat1 = new ProductCategory('Elektronik', 'elektronik');
        $cat2 = new ProductCategory('Gadget', 'gadget');
        $this->pdoCategoryRepository->save($cat1);
        $this->pdoCategoryRepository->save($cat2);

        $productId = 100;

        $this->pdoCategoryRepository->attachToProduct($productId, $cat1->getId());
        $this->pdoCategoryRepository->attachToProduct($productId, $cat2->getId());

        $productCategories = $this->pdoCategoryRepository->getCategoriesByProduct($productId);
        $this->assertCount(2, $productCategories);

        $this->pdoCategoryRepository->detachFromProduct($productId, $cat1->getId());
        $afterDetach = $this->pdoCategoryRepository->getCategoriesByProduct($productId);
        $this->assertCount(1, $afterDetach);
        $this->assertEquals('Gadget', $afterDetach[0]->getName());
    }

    public function test_update_and_delete_category(): void
    {
        $productCategory = new ProductCategory('Old Name', 'old-name');
        $this->pdoCategoryRepository->save($productCategory);
        $id = $productCategory->getId();

        $productCategory->setName('Updated Name');
        $productCategory->setSlug('updated-name');
        $this->assertTrue($this->pdoCategoryRepository->update($productCategory));

        $fresh = $this->pdoCategoryRepository->findById($id);
        $this->assertEquals('Updated Name', $fresh->getName());

        $this->assertTrue($this->pdoCategoryRepository->delete($id));
        $this->assertNull($this->pdoCategoryRepository->findById($id));
    }
}
