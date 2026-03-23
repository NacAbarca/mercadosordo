-- ============================================================
-- MercadoSordo — Migración v5
-- Gestión de órdenes, notificaciones, chat, protocolo seguridad
-- mysql -u root -p mercadosordo < database/migrate_v5.sql
-- ============================================================

USE mercadosordo;

-- ── Actualizar ENUM de estados en orders ─────────────────────────────────
ALTER TABLE orders MODIFY COLUMN status
    ENUM('pending','paid','processing','dispatched','in_transit',
         'delivered','completed','dispute','refunded','cancelled')
    NOT NULL DEFAULT 'pending';

-- ── Columnas extra en orders ──────────────────────────────────────────────
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS subtotal_neto     DECIMAL(12,2) NULL AFTER total,
    ADD COLUMN IF NOT EXISTS iva_amount        DECIMAL(10,2) NULL AFTER subtotal_neto,
    ADD COLUMN IF NOT EXISTS commission_amount DECIMAL(10,2) NULL AFTER iva_amount,
    ADD COLUMN IF NOT EXISTS vendor_net        DECIMAL(12,2) NULL AFTER commission_amount,
    ADD COLUMN IF NOT EXISTS tracking_number   VARCHAR(100)  NULL AFTER vendor_net,
    ADD COLUMN IF NOT EXISTS tracking_carrier  VARCHAR(100)  NULL AFTER tracking_number,
    ADD COLUMN IF NOT EXISTS vendor_accepted_at   TIMESTAMP NULL AFTER tracking_carrier,
    ADD COLUMN IF NOT EXISTS dispatched_at        TIMESTAMP NULL AFTER vendor_accepted_at,
    ADD COLUMN IF NOT EXISTS delivered_at         TIMESTAMP NULL AFTER dispatched_at,
    ADD COLUMN IF NOT EXISTS completed_at         TIMESTAMP NULL AFTER delivered_at,
    ADD COLUMN IF NOT EXISTS auto_complete_at     TIMESTAMP NULL AFTER completed_at,
    ADD COLUMN IF NOT EXISTS seller_id            BIGINT UNSIGNED NULL AFTER buyer_id,
    ADD COLUMN IF NOT EXISTS dispute_reason       TEXT NULL AFTER auto_complete_at;

-- ── Confirmaciones del protocolo de seguridad ────────────────────────────
CREATE TABLE IF NOT EXISTS order_confirmations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    step            ENUM('vendor_accept','vendor_dispatch','buyer_confirm','auto_complete') NOT NULL,
    confirmed_by    BIGINT UNSIGNED NULL,
    confirmed_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes           TEXT NULL,
    metadata        JSON NULL,
    FOREIGN KEY (order_id)     REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_step  (step)
);

-- ── Disputas ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS order_disputes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL UNIQUE,
    opened_by       BIGINT UNSIGNED NOT NULL,
    reason          ENUM('not_received','not_as_described','damaged','fraud','other') NOT NULL,
    description     TEXT NOT NULL,
    status          ENUM('open','reviewing','resolved_buyer','resolved_vendor','closed') DEFAULT 'open',
    resolved_by     BIGINT UNSIGNED NULL,
    resolution_note TEXT NULL,
    opened_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at     TIMESTAMP NULL,
    FOREIGN KEY (order_id)    REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (opened_by)   REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ── Centro de notificaciones ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    type        VARCHAR(50) NOT NULL,
    title       VARCHAR(255) NOT NULL,
    body        TEXT NULL,
    icon        VARCHAR(50) NULL DEFAULT 'bi-bell',
    color       VARCHAR(20) NULL DEFAULT 'primary',
    entity_type VARCHAR(50) NULL,
    entity_id   BIGINT UNSIGNED NULL,
    action_url  VARCHAR(255) NULL,
    read_at     TIMESTAMP NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user    (user_id),
    INDEX idx_read    (user_id, read_at),
    INDEX idx_type    (type)
);

-- ── Chat por orden ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS order_messages (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    BIGINT UNSIGNED NOT NULL,
    sender_id   BIGINT UNSIGNED NOT NULL,
    message     TEXT NOT NULL,
    type        ENUM('text','image','system') NOT NULL DEFAULT 'text',
    read_at     TIMESTAMP NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)  REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_order  (order_id),
    INDEX idx_sender (sender_id)
);

SELECT 'migrate_v5 OK' AS status;
