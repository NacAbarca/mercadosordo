-- ============================================================
-- MercadoSordo — Migración v3
-- Agregar RUT chileno a tabla users
-- mysql -u root -p mercadosordo < database/migrate_v3.sql
-- ============================================================

USE mercadosordo;

-- Agregar columna rut
ALTER TABLE users
    ADD COLUMN rut VARCHAR(12) NULL UNIQUE AFTER phone,
    ADD COLUMN rut_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER rut;

-- Índice para búsqueda por RUT
ALTER TABLE users
    ADD INDEX idx_rut (rut);

-- Verificar
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
ORDER BY ORDINAL_POSITION;

-- Agregar fecha de nacimiento a users
ALTER TABLE users
    ADD COLUMN birthdate DATE NULL AFTER rut_verified;
