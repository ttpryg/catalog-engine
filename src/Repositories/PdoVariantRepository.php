<?php

namespace Ttpryg\CatalogEngine\Repositories;

use DateTimeImmutable;
use PDO;
use Ttpryg\CatalogEngine\Contracts\VariantRepositoryInterface;
use Ttpryg\CatalogEngine\Entities\ProductVariant;

class PdoVariantRepository implements VariantRepositoryInterface
{
    private PDO $pdo;

    private string $table;

    public function __construct(PDO $pdo, string $table = 'product_variants')
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function findById(int|string $id): ?ProductVariant
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findBySku(string $sku): ?ProductVariant
    {
        $sql = "SELECT * FROM {$this->table} WHERE sku = :sku";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['sku' => $sku]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findByProductId(int|string $productId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = :product_id ORDER BY id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->mapToEntity($row);
        }

        return $results;
    }

    public function save(ProductVariant $variant): ProductVariant
    {
        $sql = "INSERT INTO {$this->table} (product_id, sku, name, price, stock, variant_attributes, created_at) 
                VALUES (:product_id, :sku, :name, :price, :stock, :variant_attributes, :created_at)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'product_id' => $variant->getProductId(),
            'sku' => $variant->getSku(),
            'name' => $variant->getName(),
            'price' => $variant->getPrice(),
            'stock' => $variant->getStock(),
            'variant_attributes' => json_encode($variant->getVariantAttributes()),
            'created_at' => $variant->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);

        $id = $this->pdo->lastInsertId();
        $variant->setId($id);

        return $variant;
    }

    public function update(ProductVariant $variant): bool
    {
        $sql = "UPDATE {$this->table} 
                SET sku = :sku, 
                    name = :name, 
                    price = :price, 
                    stock = :stock, 
                    variant_attributes = :variant_attributes 
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $variant->getId(),
            'sku' => $variant->getSku(),
            'name' => $variant->getName(),
            'price' => $variant->getPrice(),
            'stock' => $variant->getStock(),
            'variant_attributes' => json_encode($variant->getVariantAttributes()),
        ]);
    }

    public function delete(int|string $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function updateStock(int|string $id, int $quantityChange): bool
    {
        $sql = "UPDATE {$this->table} SET stock = stock + :change WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['id' => $id, 'change' => $quantityChange]);
    }

    private function mapToEntity(array $data): ProductVariant
    {
        $attributes = [];
        if (! empty($data['variant_attributes'])) {
            $decoded = json_decode($data['variant_attributes'], true);
            if (is_array($decoded)) {
                $attributes = $decoded;
            }
        }

        return new ProductVariant(
            productId: $data['product_id'],
            name: $data['name'],
            sku: $data['sku'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            stock: (int) ($data['stock'] ?? 0),
            variantAttributes: $attributes,
            id: $data['id'],
            createdAt: ! empty($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null
        );
    }
}
