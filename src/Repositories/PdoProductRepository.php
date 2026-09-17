<?php

namespace Ttpryg\CatalogEngine\Repositories;

use DateTimeImmutable;
use PDO;
use Ttpryg\CatalogEngine\Contracts\ProductRepositoryInterface;
use Ttpryg\CatalogEngine\Entities\Product;

class PdoProductRepository implements ProductRepositoryInterface
{
    private PDO $pdo;
    private string $table;

    public function __construct(PDO $pdo, string $table = 'products')
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function findById(int|string $id, bool $includeTrashed = false): ?Product
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        if (!$includeTrashed) {
            $sql .= " AND deleted_at IS NULL";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->mapToEntity($data) : null;
    }

    public function findBySlug(string $slug, bool $includeTrashed = false): ?Product
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug";
        if (!$includeTrashed) {
            $sql .= " AND deleted_at IS NULL";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->mapToEntity($data) : null;
    }

    public function findBySku(string $sku, bool $includeTrashed = false): ?Product
    {
        $sql = "SELECT * FROM {$this->table} WHERE sku = :sku";
        if (!$includeTrashed) {
            $sql .= " AND deleted_at IS NULL";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['sku' => $sku]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->mapToEntity($data) : null;
    }

    public function findAll(array $criteria = [], int $limit = 20, int $offset = 0, array $orderBy = ['created_at' => 'DESC']): array
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        if (isset($criteria['status'])) {
            $where[] = "status = :status";
            $params['status'] = $criteria['status'];
        }

        if (isset($criteria['is_featured'])) {
            $where[] = "is_featured = :is_featured";
            $params['is_featured'] = $criteria['is_featured'] ? 1 : 0;
        }

        if (isset($criteria['min_price'])) {
            $where[] = "price >= :min_price";
            $params['min_price'] = $criteria['min_price'];
        }

        if (isset($criteria['max_price'])) {
            $where[] = "price <= :max_price";
            $params['max_price'] = $criteria['max_price'];
        }

        if (isset($criteria['search'])) {
            $where[] = "(name LIKE :search OR summary LIKE :search OR description LIKE :search OR sku LIKE :search)";
            $params['search'] = '%' . $criteria['search'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        $orderSqls = [];
        foreach ($orderBy as $field => $dir) {
            $direction = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
            $orderSqls[] = "{$field} {$direction}";
        }
        $orderSql = implode(', ', $orderSqls);

        $sql = "SELECT * FROM {$this->table} WHERE {$whereSql} ORDER BY {$orderSql} LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->mapToEntity($row);
        }

        return $results;
    }

    public function count(array $criteria = []): int
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        if (isset($criteria['status'])) {
            $where[] = "status = :status";
            $params['status'] = $criteria['status'];
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereSql}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function save(Product $product): Product
    {
        $sql = "INSERT INTO {$this->table} 
                (name, slug, sku, barcode, summary, description, price, sale_price, cost_price, stock, min_stock, weight_grams, dimensions, status, is_featured, attributes, images, view_count, sales_count, created_at, updated_at) 
                VALUES (:name, :slug, :sku, :barcode, :summary, :description, :price, :sale_price, :cost_price, :stock, :min_stock, :weight_grams, :dimensions, :status, :is_featured, :attributes, :images, :view_count, :sales_count, :created_at, :updated_at)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'sku' => $product->getSku(),
            'barcode' => $product->getBarcode(),
            'summary' => $product->getSummary(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'sale_price' => $product->getSalePrice(),
            'cost_price' => $product->getCostPrice(),
            'stock' => $product->getStock(),
            'min_stock' => $product->getMinStock(),
            'weight_grams' => $product->getWeightGrams(),
            'dimensions' => $product->getDimensions() ? json_encode($product->getDimensions()) : null,
            'status' => $product->getStatus(),
            'is_featured' => $product->isFeatured() ? 1 : 0,
            'attributes' => json_encode($product->getAttributes()),
            'images' => json_encode($product->getImages()),
            'view_count' => $product->getViewCount(),
            'sales_count' => $product->getSalesCount(),
            'created_at' => $product->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $product->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);

        $id = $this->pdo->lastInsertId();
        $product->setId($id);

        return $product;
    }

    public function update(Product $product): bool
    {
        $sql = "UPDATE {$this->table} 
                SET name = :name, 
                    slug = :slug, 
                    sku = :sku, 
                    barcode = :barcode, 
                    summary = :summary, 
                    description = :description, 
                    price = :price, 
                    sale_price = :sale_price, 
                    cost_price = :cost_price, 
                    stock = :stock, 
                    min_stock = :min_stock, 
                    weight_grams = :weight_grams, 
                    dimensions = :dimensions, 
                    status = :status, 
                    is_featured = :is_featured, 
                    attributes = :attributes, 
                    images = :images, 
                    view_count = :view_count, 
                    sales_count = :sales_count, 
                    updated_at = :updated_at 
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'sku' => $product->getSku(),
            'barcode' => $product->getBarcode(),
            'summary' => $product->getSummary(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'sale_price' => $product->getSalePrice(),
            'cost_price' => $product->getCostPrice(),
            'stock' => $product->getStock(),
            'min_stock' => $product->getMinStock(),
            'weight_grams' => $product->getWeightGrams(),
            'dimensions' => $product->getDimensions() ? json_encode($product->getDimensions()) : null,
            'status' => $product->getStatus(),
            'is_featured' => $product->isFeatured() ? 1 : 0,
            'attributes' => json_encode($product->getAttributes()),
            'images' => json_encode($product->getImages()),
            'view_count' => $product->getViewCount(),
            'sales_count' => $product->getSalesCount(),
            'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(int|string $id, bool $softDelete = true): bool
    {
        if ($softDelete) {
            $sql = "UPDATE {$this->table} SET deleted_at = :deleted_at WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'deleted_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        }

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function restore(int|string $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NULL WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function updateStock(int|string $id, int $quantityChange): bool
    {
        $sql = "UPDATE {$this->table} SET stock = stock + :change WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id, 'change' => $quantityChange]);
    }

    public function incrementViews(int|string $id): bool
    {
        $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function incrementSales(int|string $id, int $quantity = 1): bool
    {
        $sql = "UPDATE {$this->table} SET sales_count = sales_count + :qty WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id, 'qty' => $quantity]);
    }

    private function mapToEntity(array $data): Product
    {
        $dimensions = null;
        if (!empty($data['dimensions'])) {
            $decoded = json_decode($data['dimensions'], true);
            if (is_array($decoded)) {
                $dimensions = $decoded;
            }
        }

        $attributes = [];
        if (!empty($data['attributes'])) {
            $decoded = json_decode($data['attributes'], true);
            if (is_array($decoded)) {
                $attributes = $decoded;
            }
        }

        $images = [];
        if (!empty($data['images'])) {
            $decoded = json_decode($data['images'], true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        }

        return new Product(
            name: $data['name'],
            slug: $data['slug'],
            price: (float) $data['price'],
            sku: $data['sku'] ?? null,
            barcode: $data['barcode'] ?? null,
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? null,
            salePrice: isset($data['sale_price']) ? (float) $data['sale_price'] : null,
            costPrice: isset($data['cost_price']) ? (float) $data['cost_price'] : null,
            stock: (int) ($data['stock'] ?? 0),
            minStock: (int) ($data['min_stock'] ?? 5),
            weightGrams: (int) ($data['weight_grams'] ?? 0),
            dimensions: $dimensions,
            status: $data['status'] ?? 'draft',
            isFeatured: (bool) ($data['is_featured'] ?? false),
            attributes: $attributes,
            images: $images,
            viewCount: (int) ($data['view_count'] ?? 0),
            salesCount: (int) ($data['sales_count'] ?? 0),
            id: $data['id'],
            createdAt: !empty($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: !empty($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
            deletedAt: !empty($data['deleted_at']) ? new DateTimeImmutable($data['deleted_at']) : null
        );
    }
}
