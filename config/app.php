<?php
// ============================================================
// config/app.php — Application Configuration
// ============================================================
return [
    'name'     => 'MercadoSordo',
    'url'      => env('APP_URL', 'http://localhost'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => env('APP_DEBUG', false),
    'key'      => env('APP_KEY', ''),
    'timezone' => 'America/Santiago',
    'locale'   => 'es',
    'currency' => 'CLP',
];

// ============================================================
// config/database.php
// ============================================================
// return [
//     'driver'   => 'mysql',
//     'host'     => env('DB_HOST', '127.0.0.1'),
//     'port'     => env('DB_PORT', 3306),
//     'database' => env('DB_NAME', 'mercadosordo'),
//     'username' => env('DB_USER', 'root'),
//     'password' => env('DB_PASS', ''),
//     'charset'  => 'utf8mb4',
//     'options'  => [
//         PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//         PDO::ATTR_EMULATE_PREPARES   => false,
//     ],
// ];
