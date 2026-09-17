<?php

namespace Ttpryg\CatalogEngine\Entities;

use DateTimeImmutable;
use DateTimeInterface;

class ProductCategory
{
    private int|string|null $id;
    private ?int $parentId;
    private string $name;
    private string $slug;
    private ?string $description;
    private ?DateTimeInterface $createdAt;

    public function __construct(
        string $name,
        string $slug,
        ?int $parentId = null,
        ?string $description = null,
        int|string|null $id = null,
        ?DateTimeInterface $createdAt = null
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
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
