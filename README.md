# CatalogEngine Library

`ttpryg/catalog-engine` is a framework-agnostic standalone PHP library for product catalog management, variants, inventory/stock management, pricing calculations, physical dimensions (weight/shipping), and hierarchical product categories.

## 🌟 Key Features

- **Framework Agnostic**: Compatible with any PHP 8.1+ project (Vanilla PHP, Slim, Laravel, Symfony, CodeIgniter).
- **Comprehensive E-Commerce Product Schema**:
  - Multi-tenant support with `store_id` and `owner_id` scoping (`findByStoreId`, `findByOwnerId`).
  - `products` table with SKU, barcode, price, sale price (harga coret), COGS cost price, stock, min stock threshold, weight (grams), dimensions (cm), and attributes JSON.
  - `product_variants` table for variant attributes (Color, Size, SKU override, Price override).
  - `product_categories` & `product_category_pivot` tables for parent-child category hierarchy.
- **Value Objects & Sale Price Helpers**:
  - `Price`: Calculate effective price, sale discounts (`getDiscountPercentage()`), and currency formatting.
  - `Product` Entity Helpers: `$product->isOnSale()`, `$product->getEffectivePrice()`, `$product->getDiscountPercentage()`.
  - `Dimensions`: Length, width, height (cm) and volume calculations.
  - `ProductStatus`: Enum (`draft`, `active`, `inactive`, `archived`).
- **Inventory & Low Stock Alerts**: Automatic triggering of `LowStockDetectedEvent` when stock drops to or below `min_stock`.
- **Domain Events**: `ProductCreatedEvent`, `ProductPriceChangedEvent`, `ProductStockUpdatedEvent`, `LowStockDetectedEvent`.

---

## 🗄️ Database Schema

Run the SQL script from `database/schema.sql` or use `DatabaseMigrator`:

```sql
CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_id BIGINT UNSIGNED NULL COMMENT 'ID Toko',
    owner_id BIGINT UNSIGNED NULL COMMENT 'ID Owner (AuthUser)',
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    sku VARCHAR(100) NULL UNIQUE,
    barcode VARCHAR(100) NULL,
    summary VARCHAR(500) NULL,
    description LONGTEXT NULL,
    price DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(15, 2) NULL COMMENT 'Harga Diskon / Harga Coret',
    cost_price DECIMAL(15, 2) NULL,
    stock INT NOT NULL DEFAULT 0,
    min_stock INT NOT NULL DEFAULT 5,
    weight_grams INT UNSIGNED NOT NULL DEFAULT 0,
    dimensions JSON NULL,
    status ENUM('draft', 'active', 'inactive', 'archived') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) DEFAULT 0,
    attributes JSON NULL,
    images JSON NULL,
    view_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    sales_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_store_products (store_id, status),
    INDEX idx_owner_products (owner_id, status),
    INDEX idx_status_price (status, price),
    INDEX idx_slug (slug),
    INDEX idx_sku (sku)
);
```

---

## 🚀 Quick Usage Example

```php
use PDO;
use Ttpryg\CatalogEngine\Repositories\PdoProductRepository;
use Ttpryg\CatalogEngine\Services\ProductService;
use Ttpryg\CatalogEngine\ValueObjects\Price;

// 1. Initialize PDO
$pdo = new PDO("mysql:host=localhost;dbname=my_db", "root", "secret");

// 2. Setup Service
$productRepo = new PdoProductRepository($pdo);
$productService = new ProductService($productRepo);

// 3. Create a Product
$product = $productService->createProduct(
    name: 'Running Shoes Nike Air',
    price: 1500000.0,
    salePrice: 1200000.0,
    sku: 'NIKE-AIR-001',
    stock: 25,
    weightGrams: 450,
    status: 'active'
);

// 4. Calculate Price & Discount
$priceVo = new Price($product->getPrice(), $product->getSalePrice());
echo $priceVo->format(); // Rp 1.200.000
echo $priceVo->getDiscountPercentage(); // 20.0%

// 5. Deduct Stock on Order
$productService->updateStock($product->getId(), -2);
```

---

## 📄 License
MIT License.
