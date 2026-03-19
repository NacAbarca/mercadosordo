<?php
// ============================================================
// config/database.php
// Lee desde variables de entorno (.env) o usa defaults locales
// ============================================================

function _env(string $key, mixed $default = null): mixed
{
    $v = $_ENV[$key] ?? getenv($key);
    return ($v !== false && $v !== null && $v !== '') ? $v : $default;
}

return [
    'driver'   => 'mysql',
    'host'     => _env('DB_HOST', '127.0.0.1'),
    'port'     => (int) _env('DB_PORT', 3306),
    'database' => _env('DB_NAME', 'mercadosordo'),
    'username' => _env('DB_USER', 'root'),
    'password' => _env('DB_PASS', ''),
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
