<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Contracts;

use Ttpryg\CatalogEngine\Entities\ProductCategory;

interface CategoryRepositoryInterface
{
    public function findById(int|string $id): ?ProductCategory;

    public function findBySlug(string $slug): ?ProductCategory;

    public function findAll(?int $parentId = null): array;

    public function save(ProductCategory $productCategory): ProductCategory;

    public function update(ProductCategory $productCategory): bool;

    public function delete(int|string $id): bool;

    public function attachToProduct(int|string $productId, int|string $categoryId): bool;

    public function detachFromProduct(int|string $productId, int|string $categoryId): bool;

    public function getCategoriesByProduct(int|string $productId): array;
}
