<?php

namespace Ttpryg\CatalogEngine\Contracts;

use Ttpryg\CatalogEngine\Entities\Product;

interface ProductRepositoryInterface
{
    public function findById(int|string $id, bool $includeTrashed = false): ?Product;
    public function findBySlug(string $slug, bool $includeTrashed = false): ?Product;
    public function findBySku(string $sku, bool $includeTrashed = false): ?Product;
    public function findByStoreId(int|string $storeId, array $criteria = [], int $limit = 20, int $offset = 0, array $orderBy = ['created_at' => 'DESC']): array;
    public function findByOwnerId(int|string $ownerId, array $criteria = [], int $limit = 20, int $offset = 0, array $orderBy = ['created_at' => 'DESC']): array;
    public function findAll(array $criteria = [], int $limit = 20, int $offset = 0, array $orderBy = ['created_at' => 'DESC']): array;
    public function count(array $criteria = []): int;
    public function save(Product $product): Product;
    public function update(Product $product): bool;
    public function delete(int|string $id, bool $softDelete = true): bool;
    public function restore(int|string $id): bool;
    public function updateStock(int|string $id, int $quantityChange): bool;
    public function incrementViews(int|string $id): bool;
    public function incrementSales(int|string $id, int $quantity = 1): bool;
}
