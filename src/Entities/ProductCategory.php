<?php

namespace Ttpryg\CatalogEngine\Entities;

use DateTimeImmutable;
use DateTimeInterface;

class ProductCategory
{
    private readonly ?DateTimeInterface $createdAt;

    public function __construct(
        private readonly string $name,
        private readonly string $slug,
        private readonly ?int $parentId = null,
        private readonly ?string $description = null,
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

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parentId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'created_at' => $this->createdAt?->format(DateTimeInterface::ATOM),
        ];
    }
}
