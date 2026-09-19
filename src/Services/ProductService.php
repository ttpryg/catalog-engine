<?php

namespace Ttpryg\CatalogEngine\Services;

use Ttpryg\CatalogEngine\Contracts\EventDispatcherInterface;
use Ttpryg\CatalogEngine\Contracts\ProductRepositoryInterface;
use Ttpryg\CatalogEngine\Contracts\SlugGeneratorInterface;
use Ttpryg\CatalogEngine\Entities\Product;
use Ttpryg\CatalogEngine\Events\LowStockDetectedEvent;
use Ttpryg\CatalogEngine\Events\ProductCreatedEvent;
use Ttpryg\CatalogEngine\Events\ProductPriceChangedEvent;
use Ttpryg\CatalogEngine\Events\ProductStockUpdatedEvent;
use Ttpryg\CatalogEngine\Exceptions\InsufficientStockException;
use Ttpryg\CatalogEngine\Exceptions\ProductNotFoundException;
use Ttpryg\CatalogEngine\Utilities\NativeSlugGenerator;
use Ttpryg\CatalogEngine\ValueObjects\ProductStatus;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ?SlugGeneratorInterface $slugGenerator = null,
        private ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->slugGenerator = $slugGenerator ?? new NativeSlugGenerator();
    }

    public function createProduct(
        string $name,
        float $price,
        int|string|null $storeId = null,
        int|string|null $ownerId = null,
        ?string $slug = null,
        ?string $sku = null,
        ?string $summary = null,
        ?string $description = null,
        ?float $salePrice = null,
        int $stock = 0,
        int $minStock = 5,
        int $weightGrams = 0,
        ?array $dimensions = null,
        string $status = 'draft',
        bool $isFeatured = false,
        array $attributes = [],
        array $images = []
    ): Product {
        $generatedSlug = $slug ?: $this->generateUniqueSlug($name);

        $product = new Product(
            name: $name,
            slug: $generatedSlug,
            price: $price,
            storeId: $storeId,
            ownerId: $ownerId,
            sku: $sku,
            summary: $summary,
            description: $description,
            salePrice: $salePrice,
            stock: $stock,
            minStock: $minStock,
            weightGrams: $weightGrams,
            dimensions: $dimensions,
            status: $status,
            isFeatured: $isFeatured,
            attributes: $attributes,
            images: $images
        );

        $savedProduct = $this->productRepository->save($product);

        $this->eventDispatcher?->dispatch(new ProductCreatedEvent($savedProduct));

        return $savedProduct;
    }

    public function updatePrice(int|string $productId, float $newPrice, ?float $newSalePrice = null): bool
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw ProductNotFoundException::byId($productId);
        }

        $oldPrice = $product->getPrice();
        $product->setPrice($newPrice);
        if ($newSalePrice !== null) {
            $product->setSalePrice($newSalePrice);
        }

        $result = $this->productRepository->update($product);

        if ($result && $oldPrice !== $newPrice) {
            $this->eventDispatcher?->dispatch(new ProductPriceChangedEvent($product, $oldPrice, $newPrice));
        }

        return $result;
    }

    public function updateStock(int|string $productId, int $quantityChange): bool
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw ProductNotFoundException::byId($productId);
        }

        $previousStock = $product->getStock();
        $newStock = $previousStock + $quantityChange;

        if ($newStock < 0) {
            throw new InsufficientStockException(abs($quantityChange), $previousStock);
        }

        $result = $this->productRepository->updateStock($productId, $quantityChange);

        if ($result) {
            $product->setStock($newStock);
            $this->eventDispatcher?->dispatch(new ProductStockUpdatedEvent($product, $previousStock, $newStock));

            if ($newStock <= $product->getMinStock()) {
                $this->eventDispatcher?->dispatch(new LowStockDetectedEvent($product, $newStock, $product->getMinStock()));
            }
        }

        return $result;
    }

    public function setStatus(int|string $productId, string $status): bool
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw ProductNotFoundException::byId($productId);
        }

        $product->setStatus($status);
        return $this->productRepository->update($product);
    }

    public function getProductBySlug(string $slug): Product
    {
        $product = $this->productRepository->findBySlug($slug);
        if (!$product) {
            throw ProductNotFoundException::bySlug($slug);
        }

        $this->productRepository->incrementViews($product->getId());
        return $product;
    }

    public function getProductsByStore(int|string $storeId, array $criteria = [], int $limit = 20, int $offset = 0): array
    {
        return $this->productRepository->findByStoreId($storeId, $criteria, $limit, $offset);
    }

    public function getProductsByOwner(int|string $ownerId, array $criteria = [], int $limit = 20, int $offset = 0): array
    {
        return $this->productRepository->findByOwnerId($ownerId, $criteria, $limit, $offset);
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = $this->slugGenerator->generate($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->productRepository->findBySlug($slug) !== null) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
