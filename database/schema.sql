-- CatalogEngine Database Schema
-- Standard MySQL / MariaDB DDL with Multi-Store & Multi-Owner support

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_id BIGINT UNSIGNED NULL COMMENT 'ID Toko tempat produk dijual',
    owner_id BIGINT UNSIGNED NULL COMMENT 'ID User pemilik dari auth-user',
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    sku VARCHAR(100) NULL UNIQUE COMMENT 'Stock Keeping Unit',
    barcode VARCHAR(100) NULL,
    summary VARCHAR(500) NULL COMMENT 'Ringkasan singkat produk',
    description LONGTEXT NULL COMMENT 'Deskripsi lengkap produk',
    price DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(15, 2) NULL COMMENT 'Harga diskon',
    cost_price DECIMAL(15, 2) NULL COMMENT 'Harga modal/COGS',
    stock INT NOT NULL DEFAULT 0,
    min_stock INT NOT NULL DEFAULT 5 COMMENT 'Batas peringatan stok menipis',
    weight_grams INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Berat produk (gram) untuk ongkir',
    dimensions JSON NULL COMMENT 'Panjang, lebar, tinggi (cm)',
    status ENUM('draft', 'active', 'inactive', 'archived') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) DEFAULT 0,
    attributes JSON NULL COMMENT 'Atribut produk (bahan, garansi, brand)',
    images JSON NULL COMMENT 'Array URL gambar (main & gallery)',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(100) NULL UNIQUE,
    name VARCHAR(150) NOT NULL COMMENT 'Misal: Merah - Size L',
    price DECIMAL(15, 2) NULL COMMENT 'Harga spesifik varian',
    stock INT NOT NULL DEFAULT 0,
    variant_attributes JSON NULL COMMENT '{"color": "Red", "size": "L"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_variant_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_category_pivot (
    product_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, category_id),
    CONSTRAINT fk_pivot_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_pivot_category FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
