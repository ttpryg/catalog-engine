<?php

namespace Ttpryg\CatalogEngine\Repositories;

use DateTimeImmutable;
use PDO;
use Ttpryg\CatalogEngine\Contracts\CategoryRepositoryInterface;
use Ttpryg\CatalogEngine\Entities\ProductCategory;

class PdoCategoryRepository implements CategoryRepositoryInterface
{
    private PDO $pdo;

    private string $table;

    private string $pivotTable;

    public function __construct(PDO $pdo, string $table = 'product_categories', string $pivotTable = 'product_category_pivot')
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->pivotTable = $pivotTable;
    }

    public function findById(int|string $id): ?ProductCategory
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findBySlug(string $slug): ?ProductCategory
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findAll(?int $parentId = null): array
    {
        if ($parentId !== null) {
            $sql = "SELECT * FROM {$this->table} WHERE parent_id = :parent_id ORDER BY name ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['parent_id' => $parentId]);
        } else {
            $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        }

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->mapToEntity($row);
        }

        return $results;
    }

    public function save(ProductCategory $category): ProductCategory
    {
        $sql = "INSERT INTO {$this->table} (parent_id, name, slug, description, created_at) 
                VALUES (:parent_id, :name, :slug, :description, :created_at)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'parent_id' => $category->getParentId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
            'description' => $category->getDescription(),
            'created_at' => $category->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);

        $id = $this->pdo->lastInsertId();
        $category->setId($id);

        return $category;
    }

    public function update(ProductCategory $category): bool
    {
        $sql = "UPDATE {$this->table} SET parent_id = :parent_id, name = :name, slug = :slug, description = :description WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $category->getId(),
            'parent_id' => $category->getParentId(),
            'name' => $category->getName(),
            'slug' => $category->getSlug(),
            'description' => $category->getDescription(),
        ]);
    }

    public function delete(int|string $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function attachToProduct(int|string $productId, int|string $categoryId): bool
    {
        $sql = "INSERT IGNORE INTO {$this->pivotTable} (product_id, category_id) VALUES (:product_id, :category_id)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);
    }

    public function detachFromProduct(int|string $productId, int|string $categoryId): bool
    {
        $sql = "DELETE FROM {$this->pivotTable} WHERE product_id = :product_id AND category_id = :category_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);
    }

    public function getCategoriesByProduct(int|string $productId): array
    {
        $sql = "SELECT c.* FROM {$this->table} c
                INNER JOIN {$this->pivotTable} pc ON c.id = pc.category_id
                WHERE pc.product_id = :product_id
                ORDER BY c.name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->mapToEntity($row);
        }

        return $results;
    }

    private function mapToEntity(array $data): ProductCategory
    {
        return new ProductCategory(
            name: $data['name'],
            slug: $data['slug'],
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            description: $data['description'] ?? null,
            id: $data['id'],
            createdAt: ! empty($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null
        );
    }
}
