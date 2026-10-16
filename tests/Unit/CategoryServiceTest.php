<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Contracts\CategoryRepositoryInterface;
use Ttpryg\CatalogEngine\Contracts\SlugGeneratorInterface;
use Ttpryg\CatalogEngine\Entities\ProductCategory;
use Ttpryg\CatalogEngine\Exceptions\CategoryNotFoundException;
use Ttpryg\CatalogEngine\Services\CategoryService;

class CategoryServiceTest extends TestCase
{
    public function test_create_category_with_custom_slug(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (ProductCategory $productCategory): \Ttpryg\CatalogEngine\Entities\ProductCategory {
                $productCategory->setId(1);

                return $productCategory;
            });

        $categoryService = new CategoryService($repo);
        $productCategory = $categoryService->createCategory('Pakaian Pria', slug: 'pakaian-pria-custom', description: 'Deskripsi');

        $this->assertEquals(1, $productCategory->getId());
        $this->assertEquals('Pakaian Pria', $productCategory->getName());
        $this->assertEquals('pakaian-pria-custom', $productCategory->getSlug());
        $this->assertEquals('Deskripsi', $productCategory->getDescription());
    }

    public function test_create_category_with_generated_slug(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $slugGen = $this->createMock(SlugGeneratorInterface::class);
        $slugGen->method('generate')->with('Elektronik')->willReturn('elektronik');

        $repo->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn (ProductCategory $productCategory): \Ttpryg\CatalogEngine\Entities\ProductCategory => $productCategory);

        $categoryService = new CategoryService($repo, $slugGen);
        $productCategory = $categoryService->createCategory('Elektronik');

        $this->assertEquals('elektronik', $productCategory->getSlug());
    }

    public function test_get_category_by_id_and_slug(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $productCategory = new ProductCategory('Fashion', 'fashion', id: 5);

        $repo->method('findById')->with(5)->willReturn($productCategory);
        $repo->method('findBySlug')->with('fashion')->willReturn($productCategory);

        $categoryService = new CategoryService($repo);

        $this->assertSame($productCategory, $categoryService->getCategory(5));
        $this->assertSame($productCategory, $categoryService->getCategoryBySlug('fashion'));
    }

    public function test_get_category_not_found_throws(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $repo->method('findById')->with(99)->willReturn(null);

        $this->expectException(CategoryNotFoundException::class);

        $categoryService = new CategoryService($repo);
        $categoryService->getCategory(99);
    }

    public function test_attach_and_detach_category(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $productCategory = new ProductCategory('Sepatu', 'sepatu', id: 3);

        $repo->method('findById')->with(3)->willReturn($productCategory);
        $repo->expects($this->once())->method('attachToProduct')->with(10, 3)->willReturn(true);
        $repo->expects($this->once())->method('detachFromProduct')->with(10, 3)->willReturn(true);

        $categoryService = new CategoryService($repo);

        $this->assertTrue($categoryService->attachCategoryToProduct(10, 3));
        $this->assertTrue($categoryService->detachCategoryFromProduct(10, 3));
    }

    public function test_update_category(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $productCategory = new ProductCategory('Old', 'old', id: 1);

        $repo->method('findById')->with(1)->willReturn($productCategory);
        $repo->expects($this->once())->method('update')->with($productCategory)->willReturn(true);

        $categoryService = new CategoryService($repo);
        $updated = $categoryService->updateCategory(1, [
            'name' => 'New Name',
            'slug' => 'new-slug',
            'description' => 'New Desc',
            'parent_id' => 2,
        ]);

        $this->assertEquals('New Name', $updated->getName());
        $this->assertEquals('new-slug', $updated->getSlug());
        $this->assertEquals('New Desc', $updated->getDescription());
        $this->assertEquals(2, $updated->getParentId());
    }

    public function test_delete_category(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $productCategory = new ProductCategory('To Delete', 'to-delete', id: 1);

        $repo->method('findById')->with(1)->willReturn($productCategory);
        $repo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $categoryService = new CategoryService($repo);
        $this->assertTrue($categoryService->deleteCategory(1));
    }
}
