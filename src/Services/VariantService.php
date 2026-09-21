<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Services;

use Ttpryg\CatalogEngine\Contracts\ProductRepositoryInterface;
use Ttpryg\CatalogEngine\Contracts\VariantRepositoryInterface;
use Ttpryg\CatalogEngine\Entities\ProductVariant;
use Ttpryg\CatalogEngine\Exceptions\InsufficientStockException;
use Ttpryg\CatalogEngine\Exceptions\ProductNotFoundException;
use Ttpryg\CatalogEngine\Exceptions\VariantNotFoundException;

class VariantService
{
    public function __construct(
        private readonly VariantRepositoryInterface $variantRepository,
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function addVariant(
        int|string $productId,
        string $name,
        ?string $sku = null,
        ?float $price = null,
        int $stock = 0,
        array $variantAttributes = []
    ): ProductVariant {
        $product = $this->productRepository->findById($productId);
        if (! $product instanceof \Ttpryg\CatalogEngine\Entities\Product) {
            throw ProductNotFoundException::byId($productId);
        }

        $productVariant = new ProductVariant(
            productId: $productId,
            name: $name,
            sku: $sku,
            price: $price,
            stock: $stock,
            variantAttributes: $variantAttributes
        );

        return $this->variantRepository->save($productVariant);
    }

    public function updateVariantStock(int|string $variantId, int $quantityChange): bool
    {
        $variant = $this->variantRepository->findById($variantId);
        if (! $variant instanceof \Ttpryg\CatalogEngine\Entities\ProductVariant) {
            throw VariantNotFoundException::byId($variantId);
        }

        $previousStock = $variant->getStock();
        $newStock = $previousStock + $quantityChange;

        if ($newStock < 0) {
            throw new InsufficientStockException(abs($quantityChange), $previousStock);
        }

        return $this->variantRepository->updateStock($variantId, $quantityChange);
    }
}
