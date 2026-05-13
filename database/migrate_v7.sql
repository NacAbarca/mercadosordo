-- migrate_v7.sql — rate_limits table para MySQL storage
CREATE TABLE IF NOT EXISTS rate_limits (
  id           BIGINT AUTO_INCREMENT PRIMARY KEY,
  key_hash     VARCHAR(64) NOT NULL,
  hits         INT DEFAULT 1,
  window_start DATETIME NOT NULL,
  UNIQUE INDEX idx_key_hash (key_hash),
  INDEX        idx_window (window_start)
);
