<?php
// ============================================================
// public/index.php — Entry Point
// ============================================================
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

// Autoloader (PSR-4 simple)
spl_autoload_register(function (string $class) {
    $map = [
        'MercadoSordo\\Core\\'        => BASE_PATH . '/src/Core.php',
        'MercadoSordo\\Controllers\\' => BASE_PATH . '/src/Controllers.php',
    ];
    // Simple include strategy — in prod use Composer autoload
    require_once BASE_PATH . '/src/Core.php';
    require_once BASE_PATH . '/src/Controllers.php';
});

require_once BASE_PATH . '/src/Core.php';
require_once BASE_PATH . '/src/Controllers.php';

// CORS
header('Access-Control-Allow-Origin: ' . ($_ENV['CORS_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Helpers
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Boot
session_start();
date_default_timezone_set('America/Santiago');

// Error handling (prod: log, not display)
if (!env('APP_DEBUG', false)) {
    error_reporting(0);
    ini_set('display_errors', '0');
    set_exception_handler(fn(\Throwable $e) => \MercadoSordo\Core\Response::json([
        'error' => 'Internal Server Error'
    ], 500));
}

// Dispatch
$request = new \MercadoSordo\Core\Request();
$router  = require BASE_PATH . '/routes/api.php';
$router->dispatch($request);
