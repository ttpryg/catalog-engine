<?php

declare(strict_types=1);

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

    public function getCategory(int|string $categoryId): ProductCategory
    {
        $category = $this->categoryRepository->findById($categoryId);
        if (! $category instanceof ProductCategory) {
            throw CategoryNotFoundException::byId($categoryId);
        }

        return $category;
    }

    public function getCategoryBySlug(string $slug): ProductCategory
    {
        $category = $this->categoryRepository->findBySlug($slug);
        if (! $category instanceof ProductCategory) {
            throw CategoryNotFoundException::bySlug($slug);
        }

        return $category;
    }

    public function getCategories(?int $parentId = null): array
    {
        return $this->categoryRepository->findAll($parentId);
    }

    public function getCategoriesByProduct(int|string $productId): array
    {
        return $this->categoryRepository->getCategoriesByProduct($productId);
    }

    public function attachCategoryToProduct(int|string $productId, int|string $categoryId): bool
    {
        $productCategory = $this->getCategory($categoryId);

        return $this->categoryRepository->attachToProduct($productId, $productCategory->getId());
    }

    public function detachCategoryFromProduct(int|string $productId, int|string $categoryId): bool
    {
        $productCategory = $this->getCategory($categoryId);

        return $this->categoryRepository->detachFromProduct($productId, $productCategory->getId());
    }

    public function updateCategory(int|string $categoryId, array $data): ProductCategory
    {
        $productCategory = $this->getCategory($categoryId);

        if (isset($data['name'])) {
            $productCategory->setName((string) $data['name']);
            if (empty($data['slug'])) {
                $productCategory->setSlug($this->slugGenerator->generate($data['name']));
            }
        }
        if (isset($data['slug'])) {
            $productCategory->setSlug((string) $data['slug']);
        }
        if (array_key_exists('parent_id', $data)) {
            $productCategory->setParentId($data['parent_id'] !== null ? (int) $data['parent_id'] : null);
        }
        if (array_key_exists('description', $data)) {
            $productCategory->setDescription($data['description'] !== null ? (string) $data['description'] : null);
        }

        $this->categoryRepository->update($productCategory);

        return $productCategory;
    }

    public function deleteCategory(int|string $categoryId): bool
    {
        $this->getCategory($categoryId);

        return $this->categoryRepository->delete($categoryId);
    }
}
