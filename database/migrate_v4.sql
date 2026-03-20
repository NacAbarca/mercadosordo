-- ============================================================
-- MercadoSordo — Migración v4
-- Pagos duales: Mercado Pago OAuth + Transferencia Bancaria
-- mysql -u root -p mercadosordo < database/migrate_v4.sql
-- ============================================================

USE mercadosordo;

-- Cuentas Mercado Pago de vendedores
CREATE TABLE IF NOT EXISTS vendor_payment_accounts (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id        BIGINT UNSIGNED NOT NULL UNIQUE,
    mp_user_id       VARCHAR(100) NOT NULL,
    mp_access_token  TEXT NOT NULL,
    mp_refresh_token TEXT NULL,
    mp_public_key    VARCHAR(255) NULL,
    mp_email         VARCHAR(191) NULL,
    token_expires_at TIMESTAMP NULL,
    is_active        TINYINT(1) NOT NULL DEFAULT 1,
    connected_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_vendor (vendor_id)
);

-- Cuentas bancarias de vendedores
CREATE TABLE IF NOT EXISTS vendor_bank_accounts (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id       BIGINT UNSIGNED NOT NULL UNIQUE,
    bank_name       VARCHAR(100) NOT NULL,
    account_type    ENUM('cuenta_corriente','cuenta_ahorro','cuenta_vista','cuenta_rut') NOT NULL DEFAULT 'cuenta_corriente',
    account_number  VARCHAR(50) NOT NULL,
    account_rut     VARCHAR(12) NOT NULL,
    account_name    VARCHAR(100) NOT NULL,
    account_email   VARCHAR(191) NULL,
    is_verified     TINYINT(1) NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Pagos (ambos métodos)
CREATE TABLE IF NOT EXISTS payments (
    id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id           BIGINT UNSIGNED NOT NULL,
    vendor_id          BIGINT UNSIGNED NOT NULL,
    payment_method     ENUM('mercadopago','bank_transfer') NOT NULL,
    mp_preference_id   VARCHAR(255) NULL,
    mp_payment_id      VARCHAR(255) NULL,
    khipu_payment_id   VARCHAR(255) NULL,
    khipu_payment_url  VARCHAR(500) NULL,
    amount             DECIMAL(12,2) NOT NULL,
    commission_pct     DECIMAL(4,2)  NOT NULL DEFAULT 5.00,
    commission_amount  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    vendor_amount      DECIMAL(12,2) NOT NULL,
    currency           CHAR(3) NOT NULL DEFAULT 'CLP',
    status             ENUM('pending','approved','rejected','cancelled','refunded','in_process') DEFAULT 'pending',
    status_detail      VARCHAR(100) NULL,
    payer_email        VARCHAR(191) NULL,
    raw_response       JSON NULL,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)  REFERENCES orders(id),
    FOREIGN KEY (vendor_id) REFERENCES users(id),
    INDEX idx_order      (order_id),
    INDEX idx_mp_payment (mp_payment_id),
    INDEX idx_khipu      (khipu_payment_id)
);

-- Columnas extra en orders
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS mp_preference_id  VARCHAR(255) NULL AFTER payment_id,
    ADD COLUMN IF NOT EXISTS commission_rate   DECIMAL(4,2) NOT NULL DEFAULT 5.00 AFTER mp_preference_id,
    ADD COLUMN IF NOT EXISTS payment_method_detail VARCHAR(50) NULL AFTER commission_rate;

SELECT 'migrate_v4 OK' AS status;
