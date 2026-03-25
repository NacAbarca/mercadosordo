-- migrate_v6.sql — IVA configurable por vendedor
ALTER TABLE vendor_bank_accounts
  ADD COLUMN tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Tasa IVA: 0=exento, 10=reducido, 19=estándar';

-- Por defecto 0% (persona natural puede ser exento)
-- El vendedor configura desde su perfil
