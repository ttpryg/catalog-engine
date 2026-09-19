<?php

namespace Ttpryg\CatalogEngine\Contracts;

use DateTimeInterface;

interface ProductInterface
{
    public function getId(): int|string|null;

    public function getStoreId(): int|string|null;

    public function getOwnerId(): int|string|null;

    public function getName(): string;

    public function getSlug(): string;

    public function getSku(): ?string;

    public function getPrice(): float;

    public function getSalePrice(): ?float;

    public function getCostPrice(): ?float;

    public function getStock(): int;

    public function getMinStock(): int;

    public function getWeightGrams(): int;

    public function getDimensions(): ?array;

    public function getStatus(): string;

    public function isFeatured(): bool;

    public function getAttributes(): array;

    public function getImages(): array;

    public function getViewCount(): int;

    public function getSalesCount(): int;

    public function getCreatedAt(): ?DateTimeInterface;

    public function getUpdatedAt(): ?DateTimeInterface;

    public function getDeletedAt(): ?DateTimeInterface;
}
