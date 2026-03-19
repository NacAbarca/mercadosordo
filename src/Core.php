<?php
declare(strict_types=1);
namespace MercadoSordo\Core;

// ============================================================
// DB — PDO Singleton Wrapper
// ============================================================
class DB
{
    private static ?self $instance = null;
    private \PDO $pdo;

    private function __construct()
    {
        // Usar BASE_PATH si está disponible (definido en index.php), sino resolver desde src/
        $base = defined('BASE_PATH') ? BASE_PATH : realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
        $cfgFile = $base . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

        if (!file_exists($cfgFile)) {
            throw new \RuntimeException(
                "No se encontró config/database.php\n" .
                "BASE_PATH: {$base}\n" .
                "Crea el archivo copiando config/database.example.php"
            );
        }

        $cfg = require $cfgFile;
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
        $this->pdo = new \PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function query(string $sql, array $bindings = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    public function fetch(string $sql, array $bindings = []): ?array
    {
        return $this->query($sql, $bindings)->fetch() ?: null;
    }

    public function fetchAll(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    public function insert(string $table, array $data): string
    {
        $cols = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO {$table} ({$cols}) VALUES ({$placeholders})", array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereBindings = []): int
    {
        $set = implode(',', array_map(fn($k) => "{$k}=?", array_keys($data)));
        $stmt = $this->query("UPDATE {$table} SET {$set} WHERE {$where}", [...array_values($data), ...$whereBindings]);
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $bindings = []): int
    {
        return $this->query("DELETE FROM {$table} WHERE {$where}", $bindings)->rowCount();
    }

    public function beginTransaction(): void  { $this->pdo->beginTransaction(); }
    public function commit(): void            { $this->pdo->commit(); }
    public function rollback(): void          { $this->pdo->rollBack(); }

    /** Paginate helper */
    public function paginate(string $sql, array $bindings, int $page = 1, int $perPage = 20): array
    {
        $countSql = "SELECT COUNT(*) FROM ({$sql}) AS sub";
        $total    = (int) $this->query($countSql, $bindings)->fetchColumn();
        $offset   = ($page - 1) * $perPage;
        $rows     = $this->fetchAll("{$sql} LIMIT {$perPage} OFFSET {$offset}", $bindings);
        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}

// ============================================================
// Router — Simple regex-based router
// ============================================================
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = '';
    private array $groupMiddlewares = [];

    public function group(array $options, callable $callback): void
    {
        $prevPrefix = $this->prefix;
        $prevMW     = $this->groupMiddlewares;
        $this->prefix             = $prevPrefix . ($options['prefix'] ?? '');
        $this->groupMiddlewares   = array_merge($prevMW, $options['middleware'] ?? []);
        $callback($this);
        $this->prefix           = $prevPrefix;
        $this->groupMiddlewares = $prevMW;
    }

    public function add(string $method, string $path, $handler, array $middlewares = []): void
    {
        $pattern = '@^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $this->prefix . $path) . '$@';
        $this->routes[] = [
            'method'      => strtoupper($method),
            'pattern'     => $pattern,
            'handler'     => $handler,
            'middlewares' => array_merge($this->groupMiddlewares, $middlewares),
        ];
    }

    public function get(string $path, $handler, array $mw = []): void    { $this->add('GET',    $path, $handler, $mw); }
    public function post(string $path, $handler, array $mw = []): void   { $this->add('POST',   $path, $handler, $mw); }
    public function put(string $path, $handler, array $mw = []): void    { $this->add('PUT',    $path, $handler, $mw); }
    public function patch(string $path, $handler, array $mw = []): void  { $this->add('PATCH',  $path, $handler, $mw); }
    public function delete(string $path, $handler, array $mw = []): void { $this->add('DELETE', $path, $handler, $mw); }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = strtok($request->uri(), '?');
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $request->setRouteParams($params);
            // Run middlewares
            $handler = fn() => $this->callHandler($route['handler'], $request);
            $pipeline = array_reduce(
                array_reverse($route['middlewares']),
                fn($next, $mw) => fn() => (new $mw)->handle($request, $next),
                $handler
            );
            $pipeline();
            return;
        }
        Response::json(['error' => 'Not Found'], 404);
    }

    private function callHandler($handler, Request $request): void
    {
        if (is_callable($handler)) {
            $handler($request);
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler);
            (new $class)->{$method}($request);
        }
    }
}

// ============================================================
// Request
// ============================================================
class Request
{
    private array $routeParams = [];

    public function method(): string { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
    public function uri(): string    { return $_SERVER['REQUEST_URI'] ?? '/'; }
    public function isJson(): bool   { return str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json'); }

    public function all(): array
    {
        if ($this->isJson()) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return array_merge($_GET, $_POST);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function validate(array $rules): array
    {
        $data   = $this->all();
        $errors = [];
        foreach ($rules as $field => $ruleStr) {
            $rules_list = explode('|', $ruleStr);
            foreach ($rules_list as $rule) {
                [$ruleName, $param] = array_pad(explode(':', $rule), 2, null);
                $value = $data[$field] ?? null;
                match ($ruleName) {
                    'required' => (empty($value) ? $errors[$field][] = "El campo {$field} es requerido." : null),
                    'email'    => (!filter_var($value, FILTER_VALIDATE_EMAIL) ? $errors[$field][] = "Email inválido." : null),
                    'min'      => (strlen((string)$value) < (int)$param ? $errors[$field][] = "Mínimo {$param} caracteres." : null),
                    'max'      => (strlen((string)$value) > (int)$param ? $errors[$field][] = "Máximo {$param} caracteres." : null),
                    'numeric'  => (!is_numeric($value) ? $errors[$field][] = "Debe ser numérico." : null),
                    default    => null,
                };
            }
        }
        if ($errors) Response::json(['errors' => $errors], 422) && exit;
        return $data;
    }

    public function setRouteParams(array $params): void { $this->routeParams = $params; }
    public function param(string $key): ?string         { return $this->routeParams[$key] ?? null; }
    public function bearerToken(): ?string
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        return str_starts_with($auth, 'Bearer ') ? substr($auth, 7) : null;
    }

    public function ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
    public function userAgent(): string { return $_SERVER['HTTP_USER_AGENT'] ?? ''; }
}

// ============================================================
// Response
// ============================================================
class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function view(string $view, array $data = []): void
    {
        extract($data);
        $base = defined('BASE_PATH') ? BASE_PATH : realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
        $path = $base . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
        if (!file_exists($path)) {
            self::json(['error' => "View [{$view}] not found", 'path_tried' => $path], 500);
        }
        require $path;
    }

    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }
}

// ============================================================
// Auth Service
// ============================================================
class Auth
{
    private static ?array $user = null;

    public static function attempt(string $email, string $password): ?array
    {
        $db   = DB::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);
        if (!$user || !password_verify($password, $user['password'])) return null;
        return $user;
    }

    public static function createToken(int $userId, string $type = 'auth', int $ttlHours = 24 * 7): string
    {
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $ttlHours * 3600);
        DB::getInstance()->insert('user_tokens', [
            'user_id'    => $userId,
            'token'      => $token,
            'type'       => $type,
            'expires_at' => $expiresAt,
        ]);
        return $token;
    }

    public static function getUserByToken(string $token): ?array
    {
        if (self::$user) return self::$user;
        $row = DB::getInstance()->fetch(
            "SELECT u.* FROM users u
             JOIN user_tokens t ON t.user_id = u.id
             WHERE t.token = ? AND t.type = 'auth' AND t.expires_at > NOW() AND u.status = 'active'",
            [$token]
        );
        return self::$user = $row;
    }

    public static function revokeToken(string $token): void
    {
        DB::getInstance()->delete('user_tokens', 'token = ?', [$token]);
    }

    public static function user(): ?array { return self::$user; }
    public static function id(): ?int     { return self::$user['id'] ?? null; }
    public static function is(string $role): bool { return (self::$user['role'] ?? '') === $role; }
    public static function check(): bool  { return self::$user !== null; }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

// ============================================================
// Middleware Contracts
// ============================================================
interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): void;
}

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        $token = $request->bearerToken() ?? ($_COOKIE['ms_token'] ?? null);
        if (!$token || !Auth::getUserByToken($token)) {
            $request->isJson()
                ? Response::json(['error' => 'Unauthorized'], 401)
                : Response::redirect('/login');
        }
        $next();
    }
}

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        if (!Auth::check() || !Auth::is('admin')) {
            Response::json(['error' => 'Forbidden'], 403);
        }
        $next();
    }
}

class RateLimitMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        $key  = 'rl_' . md5($request->ip() . $request->uri());
        $file = sys_get_temp_dir() . "/{$key}";
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['count' => 0, 'reset' => time() + 60];
        if (time() > $data['reset']) $data = ['count' => 0, 'reset' => time() + 60];
        $data['count']++;
        file_put_contents($file, json_encode($data));
        if ($data['count'] > 60) Response::json(['error' => 'Too Many Requests'], 429);
        $next();
    }
}
