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

    public function getVariant(int|string $variantId): ProductVariant
    {
        $variant = $this->variantRepository->findById($variantId);
        if (! $variant instanceof ProductVariant) {
            throw VariantNotFoundException::byId($variantId);
        }

        return $variant;
    }

    public function getVariantBySku(string $sku): ProductVariant
    {
        $variant = $this->variantRepository->findBySku($sku);
        if (! $variant instanceof ProductVariant) {
            throw VariantNotFoundException::bySku($sku);
        }

        return $variant;
    }

    public function getVariantsByProduct(int|string $productId): array
    {
        return $this->variantRepository->findByProductId($productId);
    }

    public function updateVariant(int|string $variantId, array $data): ProductVariant
    {
        $productVariant = $this->getVariant($variantId);

        if (isset($data['name'])) {
            $productVariant->setName((string) $data['name']);
        }
        if (array_key_exists('sku', $data)) {
            $productVariant->setSku($data['sku'] !== null ? (string) $data['sku'] : null);
        }
        if (array_key_exists('price', $data)) {
            $productVariant->setPrice($data['price'] !== null ? (float) $data['price'] : null);
        }
        if (isset($data['stock'])) {
            $productVariant->setStock((int) $data['stock']);
        }
        if (isset($data['variant_attributes']) && is_array($data['variant_attributes'])) {
            $productVariant->setVariantAttributes($data['variant_attributes']);
        }

        $this->variantRepository->update($productVariant);

        return $productVariant;
    }

    public function deleteVariant(int|string $variantId): bool
    {
        $this->getVariant($variantId);

        return $this->variantRepository->delete($variantId);
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

        $result = $this->variantRepository->updateStock($variantId, $quantityChange);

        if (! $result) {
            if ($quantityChange < 0) {
                $latest = $this->variantRepository->findById($variantId);
                $latestStock = $latest instanceof \Ttpryg\CatalogEngine\Entities\ProductVariant ? $latest->getStock() : $previousStock;
                throw new InsufficientStockException(abs($quantityChange), $latestStock);
            }

            return false;
        }

        $variant->setStock($newStock);

        return true;
    }
}
