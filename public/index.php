<?php
// ============================================================
// public/index.php — Entry Point
// Compatible: PHP 8.1+ | macOS Homebrew | Linux
// ============================================================
declare(strict_types=1);

// BASE_PATH apunta a la raíz del proyecto (un nivel arriba de /public)
define('BASE_PATH', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..'));
define('START_TIME', microtime(true));

// Verificación de archivos críticos antes de incluir
$coreFile       = BASE_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Core.php';
$controllersFile= BASE_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controllers.php';
$routesFile     = BASE_PATH . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';

if (!file_exists($coreFile)) {
    http_response_code(500);
    die("Error: No se encuentra src/Core.php\nBASE_PATH resuelto: " . BASE_PATH . "\nVerifica que ejecutas el servidor desde la carpeta correcta:\n  cd /ruta/a/mercadosordo\n  php -S localhost:8080 -t public");
}

// Helper env() — disponible ANTES de cargar Core.php
function env(string $key, mixed $default = null): mixed
{
    $v = $_ENV[$key] ?? getenv($key);
    return ($v !== false && $v !== null && $v !== '') ? $v : $default;
}

// Cargar .env si existe (parser manual simple, sin dependencias)
$envFile = BASE_PATH . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            $v = trim($v, '"\'');
            $_ENV[$k] = $v;
            putenv("{$k}={$v}");
        }
    }
}

// Activar errores en desarrollo para facilitar debug
$debug = filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN);
if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Incluir archivos fuente (orden importa: Core primero)
require_once $coreFile;
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'R2Uploader.php';
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Mailer.php';
require_once $controllersFile;

// CORS
$origin = env('CORS_ORIGIN', '*');
header("Access-Control-Allow-Origin: {$origin}");
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Boot
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Santiago');

// Exception handler global (solo producción)
if (!$debug) {
    set_exception_handler(function (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Internal Server Error']);
        exit;
    });
}

// Dispatch
$request = new \MercadoSordo\Core\Request();
$router  = require $routesFile;
$router->dispatch($request);
