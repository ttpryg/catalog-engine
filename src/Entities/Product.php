<?php

namespace Ttpryg\CatalogEngine\Entities;

use DateTimeImmutable;
use DateTimeInterface;
use Ttpryg\CatalogEngine\Contracts\ProductInterface;
use Ttpryg\CatalogEngine\ValueObjects\ProductStatus;

class Product implements ProductInterface
{
    private string $status;

    private readonly ?DateTimeInterface $createdAt;

    private readonly ?DateTimeInterface $updatedAt;

    public function __construct(
        private string $name,
        private string $slug,
        private float $price,
        private int|string|null $storeId = null,
        private int|string|null $ownerId = null,
        private ?string $sku = null,
        private readonly ?string $barcode = null,
        private readonly ?string $summary = null,
        private readonly ?string $description = null,
        private ?float $salePrice = null,
        private readonly ?float $costPrice = null,
        private int $stock = 0,
        private readonly int $minStock = 5,
        private readonly int $weightGrams = 0,
        private readonly ?array $dimensions = null,
        string $status = 'draft',
        private readonly bool $isFeatured = false,
        private readonly array $attributes = [],
        private readonly array $images = [],
        private readonly int $viewCount = 0,
        private readonly int $salesCount = 0,
        private int|string|null $id = null,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
        private readonly ?DateTimeInterface $deletedAt = null
    ) {
        $this->status = ProductStatus::isValid($status) ? $status : ProductStatus::DRAFT->value;
        $this->createdAt = $createdAt ?? new DateTimeImmutable;
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable;
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }

    public function setId(int|string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getStoreId(): int|string|null
    {
        return $this->storeId;
    }

    public function setStoreId(int|string|null $storeId): self
    {
        $this->storeId = $storeId;

        return $this;
    }

    public function getOwnerId(): int|string|null
    {
        return $this->ownerId;
    }

    public function setOwnerId(int|string|null $ownerId): self
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSalePrice(): ?float
    {
        return $this->salePrice;
    }

    public function setSalePrice(?float $salePrice): self
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    public function isOnSale(): bool
    {
        return $this->salePrice !== null && $this->salePrice > 0 && $this->salePrice < $this->price;
    }

    public function getEffectivePrice(): float
    {
        return $this->isOnSale() ? $this->salePrice : $this->price;
    }

    public function getDiscountPercentage(): float
    {
        if (! $this->isOnSale() || $this->price <= 0) {
            return 0.0;
        }

        return round((($this->price - $this->salePrice) / $this->price) * 100, 1);
    }

    public function getCostPrice(): ?float
    {
        return $this->costPrice;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getMinStock(): int
    {
        return $this->minStock;
    }

    public function getWeightGrams(): int
    {
        return $this->weightGrams;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (ProductStatus::isValid($status)) {
            $this->status = $status;
        }

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function getSalesCount(): int
    {
        return $this->salesCount;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->storeId,
            'owner_id' => $this->ownerId,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'summary' => $this->summary,
            'description' => $this->description,
            'price' => $this->price,
            'sale_price' => $this->salePrice,
            'effective_price' => $this->getEffectivePrice(),
            'is_on_sale' => $this->isOnSale(),
            'discount_percentage' => $this->getDiscountPercentage(),
            'cost_price' => $this->costPrice,
            'stock' => $this->stock,
            'min_stock' => $this->minStock,
            'weight_grams' => $this->weightGrams,
            'dimensions' => $this->dimensions,
            'status' => $this->status,
            'is_featured' => $this->isFeatured,
            'attributes' => $this->attributes,
            'images' => $this->images,
            'view_count' => $this->viewCount,
            'sales_count' => $this->salesCount,
            'created_at' => $this->createdAt?->format(DateTimeInterface::ATOM),
            'updated_at' => $this->updatedAt?->format(DateTimeInterface::ATOM),
            'deleted_at' => $this->deletedAt?->format(DateTimeInterface::ATOM),
        ];
    }
}
