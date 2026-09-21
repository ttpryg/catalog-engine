<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Contracts;

use Ttpryg\CatalogEngine\Entities\ProductVariant;

interface VariantRepositoryInterface
{
    public function findById(int|string $id): ?ProductVariant;

    public function findBySku(string $sku): ?ProductVariant;

    public function findByProductId(int|string $productId): array;

    public function save(ProductVariant $productVariant): ProductVariant;

    public function update(ProductVariant $productVariant): bool;

    public function delete(int|string $id): bool;

    public function updateStock(int|string $id, int $quantityChange): bool;
}
