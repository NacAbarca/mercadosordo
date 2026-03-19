-- ============================================================
-- MercadoSordo - Database Schema
-- Engine: MySQL 8.0+ | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS mercadosordo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mercadosordo;

-- ============================================================
-- USERS & AUTH
-- ============================================================
CREATE TABLE users (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid        CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(191) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    avatar      VARCHAR(500) NULL,
    role        ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
    status      ENUM('active','suspended','pending') NOT NULL DEFAULT 'pending',
    phone       VARCHAR(30) NULL,
    verified_at TIMESTAMP NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role  (role),
    INDEX idx_status(status)
);

CREATE TABLE user_tokens (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    token       VARCHAR(64) NOT NULL UNIQUE,
    type        ENUM('auth','refresh','reset','verify','api') NOT NULL DEFAULT 'auth',
    expires_at  TIMESTAMP NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_type (user_id, type)
);

CREATE TABLE user_addresses (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    label       VARCHAR(50) NOT NULL DEFAULT 'Casa',
    full_name   VARCHAR(100) NOT NULL,
    address     VARCHAR(255) NOT NULL,
    city        VARCHAR(100) NOT NULL,
    region      VARCHAR(100) NOT NULL,
    zip_code    VARCHAR(20) NULL,
    country     CHAR(2) NOT NULL DEFAULT 'CL',
    is_default  BOOLEAN NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- CATEGORIES
-- ============================================================
CREATE TABLE categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id   INT UNSIGNED NULL,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(120) NOT NULL UNIQUE,
    icon        VARCHAR(100) NULL,
    image       VARCHAR(500) NULL,
    description TEXT NULL,
    sort_order  SMALLINT NOT NULL DEFAULT 0,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug   (slug),
    INDEX idx_parent (parent_id)
);

-- ============================================================
-- PRODUCTS
-- ============================================================
CREATE TABLE products (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    seller_id       BIGINT UNSIGNED NOT NULL,
    category_id     INT UNSIGNED NOT NULL,
    title           VARCHAR(255) NOT NULL,
    slug            VARCHAR(280) NOT NULL UNIQUE,
    description     LONGTEXT NULL,
    price           DECIMAL(12,2) NOT NULL,
    compare_price   DECIMAL(12,2) NULL,
    cost_price      DECIMAL(12,2) NULL,
    sku             VARCHAR(100) NULL UNIQUE,
    stock           INT NOT NULL DEFAULT 0,
    stock_alert     INT NOT NULL DEFAULT 5,
    status          ENUM('draft','active','paused','sold_out','deleted') DEFAULT 'draft',
    condition_type  ENUM('new','used','refurbished') DEFAULT 'new',
    weight_kg       DECIMAL(8,3) NULL,
    views           INT UNSIGNED NOT NULL DEFAULT 0,
    sales_count     INT UNSIGNED NOT NULL DEFAULT 0,
    rating_avg      DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    rating_count    INT UNSIGNED NOT NULL DEFAULT 0,
    featured        BOOLEAN NOT NULL DEFAULT FALSE,
    free_shipping   BOOLEAN NOT NULL DEFAULT FALSE,
    meta_title      VARCHAR(255) NULL,
    meta_desc       VARCHAR(500) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_slug      (slug),
    INDEX idx_seller    (seller_id),
    INDEX idx_category  (category_id),
    INDEX idx_status    (status),
    INDEX idx_featured  (featured),
    FULLTEXT idx_search (title, description)
);

CREATE TABLE product_images (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT UNSIGNED NOT NULL,
    url         VARCHAR(500) NOT NULL,
    alt_text    VARCHAR(255) NULL,
    sort_order  SMALLINT NOT NULL DEFAULT 0,
    is_primary  BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
);

CREATE TABLE product_attributes (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT UNSIGNED NOT NULL,
    attr_key    VARCHAR(100) NOT NULL,
    attr_value  VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
);

CREATE TABLE product_variants (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    options     JSON NOT NULL,
    price_mod   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock       INT NOT NULL DEFAULT 0,
    sku         VARCHAR(100) NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- ORDERS
-- ============================================================
CREATE TABLE orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number    VARCHAR(20) NOT NULL UNIQUE,
    buyer_id        BIGINT UNSIGNED NOT NULL,
    status          ENUM('pending','paid','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
    subtotal        DECIMAL(12,2) NOT NULL,
    shipping_cost   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total           DECIMAL(12,2) NOT NULL,
    currency        CHAR(3) NOT NULL DEFAULT 'CLP',
    payment_method  VARCHAR(50) NULL,
    payment_id      VARCHAR(255) NULL,
    notes           TEXT NULL,
    address_snapshot JSON NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    INDEX idx_buyer   (buyer_id),
    INDEX idx_status  (status),
    INDEX idx_number  (order_number)
);

CREATE TABLE order_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    product_id      BIGINT UNSIGNED NOT NULL,
    seller_id       BIGINT UNSIGNED NOT NULL,
    variant_id      BIGINT UNSIGNED NULL,
    title           VARCHAR(255) NOT NULL,
    sku             VARCHAR(100) NULL,
    price           DECIMAL(12,2) NOT NULL,
    quantity        INT UNSIGNED NOT NULL,
    subtotal        DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (seller_id)  REFERENCES users(id)
);

CREATE TABLE order_tracking (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    BIGINT UNSIGNED NOT NULL,
    status      VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ============================================================
-- CART (session-based + user-based)
-- ============================================================
CREATE TABLE carts (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_key VARCHAR(64) NOT NULL UNIQUE,
    user_id     BIGINT UNSIGNED NULL,
    expires_at  TIMESTAMP NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_key),
    INDEX idx_user    (user_id)
);

CREATE TABLE cart_items (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id     BIGINT UNSIGNED NOT NULL,
    product_id  BIGINT UNSIGNED NOT NULL,
    variant_id  BIGINT UNSIGNED NULL,
    quantity    INT UNSIGNED NOT NULL DEFAULT 1,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id)    REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_cart_product (cart_id, product_id, variant_id)
);

-- ============================================================
-- REVIEWS
-- ============================================================
CREATE TABLE reviews (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    order_id    BIGINT UNSIGNED NULL,
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title       VARCHAR(150) NULL,
    body        TEXT NULL,
    status      ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE SET NULL,
    UNIQUE KEY uq_user_product_order (user_id, product_id, order_id)
);

-- ============================================================
-- WISHLIST
-- ============================================================
CREATE TABLE wishlists (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    product_id  BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_product (user_id, product_id)
);

-- ============================================================
-- COUPONS
-- ============================================================
CREATE TABLE coupons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    type            ENUM('percent','fixed','free_shipping') NOT NULL,
    value           DECIMAL(10,2) NOT NULL,
    min_purchase    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_uses        INT NULL,
    used_count      INT NOT NULL DEFAULT 0,
    starts_at       TIMESTAMP NULL,
    expires_at      TIMESTAMP NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code)
);

-- ============================================================
-- MESSAGING
-- ============================================================
CREATE TABLE conversations (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT UNSIGNED NULL,
    buyer_id    BIGINT UNSIGNED NOT NULL,
    seller_id   BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id       BIGINT UNSIGNED NOT NULL,
    body            TEXT NOT NULL,
    read_at         TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- AUDIT LOG
-- ============================================================
CREATE TABLE audit_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NULL,
    action      VARCHAR(100) NOT NULL,
    entity      VARCHAR(100) NULL,
    entity_id   BIGINT UNSIGNED NULL,
    old_values  JSON NULL,
    new_values  JSON NULL,
    ip_address  VARCHAR(45) NULL,
    user_agent  VARCHAR(500) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user   (user_id),
    INDEX idx_entity (entity, entity_id)
);

-- ============================================================
-- SEED: Admin user (password: Admin@1234)
-- ============================================================
INSERT INTO users (name, email, password, role, status, verified_at)
VALUES ('Admin MercadoSordo', 'admin@mercadosordo.cl',
        '$2y$12$hashed_here', 'admin', 'active', NOW());

INSERT INTO categories (name, slug, icon, sort_order, is_active) VALUES
('Electrónica',    'electronica',    'bi-laptop',         1, 1),
('Ropa y Moda',    'ropa-moda',      'bi-bag',            2, 1),
('Hogar',          'hogar',          'bi-house',          3, 1),
('Deportes',       'deportes',       'bi-trophy',         4, 1),
('Autos',          'autos',          'bi-car-front',      5, 1),
('Juegos',         'juegos',         'bi-controller',     6, 1),
('Herramientas',   'herramientas',   'bi-tools',          7, 1),
('Libros',         'libros',         'bi-book',           8, 1);
