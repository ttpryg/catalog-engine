<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Entities;

use DateTimeImmutable;
use DateTimeInterface;

class ProductVariant
{
    private readonly DateTimeInterface $createdAt;

    public function __construct(
        private readonly int|string $productId,
        private string $name,
        private ?string $sku = null,
        private ?float $price = null,
        private int $stock = 0,
        private array $variantAttributes = [],
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

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
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

    public function setVariantAttributes(array $variantAttributes): self
    {
        $this->variantAttributes = $variantAttributes;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
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
            'created_at' => $this->createdAt->format(DateTimeInterface::ATOM),
        ];
    }
}
