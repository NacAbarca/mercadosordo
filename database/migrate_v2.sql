-- ============================================================
-- MercadoSordo — Migración v2
-- Ejecutar UNA sola vez sobre la DB existente
-- mysql -u root -p mercadosordo < database/migrate_v2.sql
-- ============================================================

-- Agregar columnas faltantes a products (solo si no existen)
ALTER TABLE products
    ADD COLUMN IF NOT EXISTS delivery_type  ENUM('shipping','pickup','both') NOT NULL DEFAULT 'shipping' AFTER free_shipping,
    ADD COLUMN IF NOT EXISTS external_link  VARCHAR(500) NULL AFTER meta_desc,
    ADD COLUMN IF NOT EXISTS short_desc     VARCHAR(160) NULL AFTER description;

-- Asegurar que free_shipping tenga default correcto
ALTER TABLE products
    MODIFY COLUMN free_shipping TINYINT(1) NOT NULL DEFAULT 0;

-- Asegurar que featured tenga default correcto  
ALTER TABLE products
    MODIFY COLUMN featured TINYINT(1) NOT NULL DEFAULT 0;

-- Asegurar que stock_alert tenga default
ALTER TABLE products
    MODIFY COLUMN stock_alert INT NOT NULL DEFAULT 5;

-- Verificar resultado
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'products'
ORDER BY ORDINAL_POSITION;
