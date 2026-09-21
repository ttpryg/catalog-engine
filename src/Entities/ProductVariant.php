<?php

namespace Ttpryg\CatalogEngine\Entities;

use DateTimeImmutable;
use DateTimeInterface;

class ProductVariant
{
    private readonly ?DateTimeInterface $createdAt;

    public function __construct(
        private readonly int|string $productId,
        private readonly string $name,
        private readonly ?string $sku = null,
        private readonly ?float $price = null,
        private int $stock = 0,
        private readonly array $variantAttributes = [],
        private int|string|null $id = null,
        ?DateTimeInterface $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable;
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

    public function getProductId(): int|string
    {
        return $this->productId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function getPrice(): ?float
    {
        return $this->price;
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

    public function getVariantAttributes(): array
    {
        return $this->variantAttributes;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock' => $this->stock,
            'variant_attributes' => $this->variantAttributes,
            'created_at' => $this->createdAt?->format(DateTimeInterface::ATOM),
        ];
    }
}
