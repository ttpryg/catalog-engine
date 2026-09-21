<?php

namespace Ttpryg\CatalogEngine\Services;

use Ttpryg\CatalogEngine\Contracts\CategoryRepositoryInterface;
use Ttpryg\CatalogEngine\Contracts\SlugGeneratorInterface;
use Ttpryg\CatalogEngine\Entities\ProductCategory;
use Ttpryg\CatalogEngine\Exceptions\CategoryNotFoundException;
use Ttpryg\CatalogEngine\Utilities\NativeSlugGenerator;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private ?SlugGeneratorInterface $slugGenerator = null
    ) {
        $this->slugGenerator = $slugGenerator ?? new NativeSlugGenerator;
    }

    public function createCategory(string $name, ?int $parentId = null, ?string $slug = null, ?string $description = null): ProductCategory
    {
        $generatedSlug = $slug ?: $this->slugGenerator->generate($name);

        $productCategory = new ProductCategory(
            name: $name,
            slug: $generatedSlug,
            parentId: $parentId,
            description: $description
        );

        return $this->categoryRepository->save($productCategory);
    }

    public function attachCategoryToProduct(int|string $productId, int|string $categoryId): bool
    {
        $category = $this->categoryRepository->findById($categoryId);
        if (! $category instanceof \Ttpryg\CatalogEngine\Entities\ProductCategory) {
            throw CategoryNotFoundException::byId($categoryId);
        }

        return $this->categoryRepository->attachToProduct($productId, $categoryId);
    }
}
