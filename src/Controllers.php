<?php
declare(strict_types=1);
namespace MercadoSordo\Controllers;

use MercadoSordo\Core\{DB, Auth, Response, Request};

// ============================================================
// AuthController — /api/auth/*
// ============================================================
class AuthController
{
    public function register(Request $req): void
    {
        $data = $req->validate([
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email',
            'password' => 'required|min:8|max:72',
        ]);
        $db = DB::getInstance();
        if ($db->fetch("SELECT id FROM users WHERE email = ?", [$data['email']])) {
            Response::json(['error' => 'Email ya registrado.'], 409);
        }
        $verifyToken = bin2hex(random_bytes(16));
        $userId = $db->insert('users', [
            'name'     => trim($data['name']),
            'email'    => strtolower(trim($data['email'])),
            'password' => Auth::hashPassword($data['password']),
            'role'     => 'buyer',
            'status'   => 'active', // set 'pending' if email verify required
        ]);
        // TODO: Queue email verification → send $verifyToken
        $token = Auth::createToken((int)$userId);
        Response::json(['token' => $token, 'user' => $this->safeUser($db->fetch("SELECT * FROM users WHERE id=?", [$userId]))], 201);
    }

    public function login(Request $req): void
    {
        $data = $req->validate(['email' => 'required|email', 'password' => 'required']);
        $user = Auth::attempt($data['email'], $data['password']);
        if (!$user) Response::json(['error' => 'Credenciales inválidas.'], 401);
        $token = Auth::createToken((int)$user['id']);
        Response::json(['token' => $token, 'user' => $this->safeUser($user)]);
    }

    public function logout(Request $req): void
    {
        Auth::revokeToken($req->bearerToken() ?? '');
        Response::json(['message' => 'Sesión cerrada.']);
    }

    public function me(Request $req): void
    {
        Response::json(['user' => $this->safeUser(Auth::user())]);
    }

    public function forgotPassword(Request $req): void
    {
        $data = $req->validate(['email' => 'required|email']);
        $user = DB::getInstance()->fetch("SELECT id FROM users WHERE email=?", [$data['email']]);
        if ($user) {
            $token = Auth::createToken((int)$user['id'], 'reset', 2);
            // TODO: Mail::send reset link with $token
        }
        Response::json(['message' => 'Si el email existe, recibirás un enlace.']);
    }

    public function resetPassword(Request $req): void
    {
        $data  = $req->validate(['token' => 'required', 'password' => 'required|min:8']);
        $db    = DB::getInstance();
        $row   = $db->fetch("SELECT * FROM user_tokens WHERE token=? AND type='reset' AND expires_at > NOW()", [$data['token']]);
        if (!$row) Response::json(['error' => 'Token inválido o expirado.'], 400);
        $db->update('users', ['password' => Auth::hashPassword($data['password'])], 'id=?', [$row['user_id']]);
        $db->delete('user_tokens', 'token=?', [$data['token']]);
        Response::json(['message' => 'Contraseña actualizada.']);
    }

    private function safeUser(?array $user): ?array
    {
        if (!$user) return null;
        unset($user['password']);
        return $user;
    }
}

// ============================================================
// ProductController — /api/products/*
// ============================================================
class ProductController
{
    public function index(Request $req): void
    {
        $db      = DB::getInstance();
        $page    = (int)($req->input('page', 1));
        $perPage = min((int)($req->input('per_page', 20)), 100);
        $search  = $req->input('q', '');
        $cat     = $req->input('category', '');
        $sort    = $req->input('sort', 'created_at_desc');
        $minP    = $req->input('min_price');
        $maxP    = $req->input('max_price');
        $condition = $req->input('condition');

        $sql  = "SELECT p.*, pi.url AS primary_image, u.name AS seller_name
                 FROM products p
                 LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                 LEFT JOIN users u ON u.id = p.seller_id
                 WHERE p.status = 'active'";
        $bindings = [];

        if ($search) {
            $sql .= " AND MATCH(p.title, p.description) AGAINST(? IN BOOLEAN MODE)";
            $bindings[] = $search . '*';
        }
        if ($cat) {
            $sql .= " AND p.category_id = (SELECT id FROM categories WHERE slug=? LIMIT 1)";
            $bindings[] = $cat;
        }
        if ($minP) { $sql .= " AND p.price >= ?"; $bindings[] = $minP; }
        if ($maxP) { $sql .= " AND p.price <= ?"; $bindings[] = $maxP; }
        if ($condition) { $sql .= " AND p.condition_type = ?"; $bindings[] = $condition; }

        $orderMap = [
            'price_asc'    => 'p.price ASC',
            'price_desc'   => 'p.price DESC',
            'rating'       => 'p.rating_avg DESC',
            'sales'        => 'p.sales_count DESC',
            'created_at_desc' => 'p.created_at DESC',
        ];
        $sql .= " ORDER BY " . ($orderMap[$sort] ?? 'p.created_at DESC');

        Response::json($db->paginate($sql, $bindings, $page, $perPage));
    }

    public function show(Request $req): void
    {
        $db   = DB::getInstance();
        $slug = $req->param('slug');
        $p    = $db->fetch("SELECT p.*, u.name AS seller_name, u.avatar AS seller_avatar, u.id AS seller_id,
                            c.name AS category_name
                            FROM products p
                            JOIN users u ON u.id = p.seller_id
                            JOIN categories c ON c.id = p.category_id
                            WHERE p.slug = ? AND p.status = 'active'", [$slug]);
        if (!$p) Response::json(['error' => 'Producto no encontrado.'], 404);
        $p['images']     = $db->fetchAll("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order", [$p['id']]);
        $p['attributes'] = $db->fetchAll("SELECT * FROM product_attributes WHERE product_id=?", [$p['id']]);
        $p['variants']   = $db->fetchAll("SELECT * FROM product_variants WHERE product_id=?", [$p['id']]);
        $p['reviews']    = $db->fetchAll("SELECT r.*, u.name AS reviewer_name, u.avatar FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? AND r.status='approved' ORDER BY r.created_at DESC LIMIT 10", [$p['id']]);
        // Increment views async
        $db->query("UPDATE products SET views = views + 1 WHERE id = ?", [$p['id']]);
        Response::json($p);
    }

    public function store(Request $req): void
    {
        $data = $req->validate([
            'title'       => 'required|min:5|max:255',
            'category_id' => 'required|numeric',
            'price'       => 'required|numeric',
            'stock'       => 'required|numeric',
        ]);
        $db   = DB::getInstance();
        $slug = $this->uniqueSlug($data['title'], $db);

        // Sanitizar tipos para MySQL — evita SQLSTATE 1366
        $comparePrice = $req->input('compare_price');
        $weightKg     = $req->input('weight_kg');
        $stockAlert   = $req->input('stock_alert', 5);
        $freeShipping = $req->input('free_shipping', false);

        $id = $db->insert('products', [
            'seller_id'      => (int) Auth::id(),
            'category_id'    => (int) $data['category_id'],
            'title'          => trim($data['title']),
            'slug'           => $slug,
            'description'    => $req->input('description') ?: null,
            'price'          => (float) $data['price'],
            'compare_price'  => ($comparePrice !== null && $comparePrice !== '' && (float)$comparePrice > 0)
                                    ? (float) $comparePrice : null,
            'stock'          => (int) $data['stock'],
            'stock_alert'    => (int) ($stockAlert !== '' ? $stockAlert : 5),
            'condition_type' => $req->input('condition_type', $req->input('condition', 'new')),
            'free_shipping'  => ($freeShipping === true || $freeShipping === 'true' || $freeShipping === 1 || $freeShipping === '1') ? 1 : 0,
            'weight_kg'      => ($weightKg !== null && $weightKg !== '' && (float)$weightKg > 0)
                                    ? (float) $weightKg : null,
            'status'         => in_array($req->input('status'), ['active','draft','paused']) ? $req->input('status') : 'active',
            'sku'            => $req->input('sku') ?: null,
            'short_desc'     => $req->input('short_desc') ?: null,
            'meta_title'     => $req->input('short_desc') ? substr($req->input('short_desc'), 0, 255) : null,
            'meta_desc'      => $req->input('short_desc') ?: null,
            'delivery_type'  => in_array($req->input('delivery_type'), ['shipping','pickup','both']) ? $req->input('delivery_type') : 'shipping',
            'external_link'  => $req->input('external_link') ?: null,
            'featured'       => 0,
        ]);
        Response::json(['id' => $id, 'slug' => $slug], 201);
    }

    public function update(Request $req): void
    {
        $id      = (int)$req->param('id');
        $db      = DB::getInstance();
        $product = $db->fetch("SELECT * FROM products WHERE id=?", [$id]);
        if (!$product) Response::json(['error' => 'Not found'], 404);
        if ((int)$product['seller_id'] !== (int)Auth::id() && !Auth::is('admin')) {
            Response::json(['error' => 'Forbidden'], 403);
        }
        $raw     = $req->all();
        $allowed = ['title','description','price','compare_price','stock','stock_alert',
                    'condition_type','free_shipping','status','category_id','sku',
                    'weight_kg','meta_desc','meta_title','featured'];
        $data    = array_intersect_key($raw, array_flip($allowed));
        if (empty($data)) Response::json(['error' => 'Sin datos para actualizar.'], 422);

        // Sanitizar tipos — mismo criterio que store()
        if (isset($data['free_shipping'])) {
            $fs = $data['free_shipping'];
            $data['free_shipping'] = ($fs === true || $fs === 'true' || $fs === 1 || $fs === '1') ? 1 : 0;
        }
        if (isset($data['price']))         $data['price']         = (float) $data['price'];
        if (isset($data['compare_price'])) $data['compare_price'] = ($data['compare_price'] !== '' && (float)$data['compare_price'] > 0) ? (float)$data['compare_price'] : null;
        if (isset($data['stock']))         $data['stock']         = (int) $data['stock'];
        if (isset($data['stock_alert']))   $data['stock_alert']   = (int) $data['stock_alert'];
        if (isset($data['category_id']))   $data['category_id']   = (int) $data['category_id'];
        if (isset($data['weight_kg']))     $data['weight_kg']     = ($data['weight_kg'] !== '' && (float)$data['weight_kg'] > 0) ? (float)$data['weight_kg'] : null;
        if (isset($data['featured']))      $data['featured']      = $data['featured'] ? 1 : 0;

        // short_desc se guarda en meta_desc / meta_title
        if (isset($raw['short_desc'])) {
            $data['meta_desc']  = $raw['short_desc'] ?: null;
            $data['meta_title'] = $raw['short_desc'] ? substr($raw['short_desc'], 0, 255) : null;
        }

        $db->update('products', $data, 'id=?', [$id]);
        Response::json(['message' => 'Producto actualizado.']);
    }

    public function destroy(Request $req): void
    {
        $id = (int)$req->param('id');
        $db = DB::getInstance();
        $p  = $db->fetch("SELECT seller_id FROM products WHERE id=?", [$id]);
        if (!$p) Response::json(['error' => 'Not found'], 404);
        if ($p['seller_id'] !== Auth::id() && !Auth::is('admin')) Response::json(['error' => 'Forbidden'], 403);
        $db->update('products', ['status' => 'deleted'], 'id=?', [$id]);
        Response::json(['message' => 'Producto eliminado.']);
    }

    public function myProducts(Request $req): void
    {
        $db   = DB::getInstance();
        $page = (int)$req->input('page', 1);
        $sql  = "SELECT p.*, pi.url AS primary_image FROM products p
                 LEFT JOIN product_images pi ON pi.product_id=p.id AND pi.is_primary=1
                 WHERE p.seller_id=? AND p.status != 'deleted'
                 ORDER BY p.created_at DESC";
        Response::json($db->paginate($sql, [Auth::id()], $page));
    }

    private function uniqueSlug(string $title, DB $db): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s-]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $title))));
        $base = preg_replace('/[\s-]+/', '-', $base);
        $slug = $base;
        $i    = 0;
        while ($db->fetch("SELECT id FROM products WHERE slug=?", [$slug])) $slug = $base . '-' . ++$i;
        return $slug;
    }
}

// ============================================================
// CartController — /api/cart/*
// ============================================================
class CartController
{
    private function getOrCreateCart(Request $req, DB $db): array
    {
        $userId = Auth::id();
        if ($userId) {
            $cart = $db->fetch("SELECT * FROM carts WHERE user_id=? AND expires_at > NOW()", [$userId]);
            if (!$cart) {
                $key = bin2hex(random_bytes(16));
                $id  = $db->insert('carts', ['session_key' => $key, 'user_id' => $userId, 'expires_at' => date('Y-m-d H:i:s', time() + 86400 * 30)]);
                $cart = $db->fetch("SELECT * FROM carts WHERE id=?", [$id]);
            }
        } else {
            $key  = $_COOKIE['ms_cart'] ?? bin2hex(random_bytes(16));
            setcookie('ms_cart', $key, time() + 86400 * 30, '/', '', true, true);
            $cart = $db->fetch("SELECT * FROM carts WHERE session_key=? AND expires_at > NOW()", [$key]);
            if (!$cart) {
                $id  = $db->insert('carts', ['session_key' => $key, 'expires_at' => date('Y-m-d H:i:s', time() + 86400 * 7)]);
                $cart = $db->fetch("SELECT * FROM carts WHERE id=?", [$id]);
            }
        }
        return $cart;
    }

    public function index(Request $req): void
    {
        $db   = DB::getInstance();
        $cart = $this->getOrCreateCart($req, $db);
        $items = $db->fetchAll(
            "SELECT ci.*, p.title, p.price, p.free_shipping, p.stock, pi.url AS image
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE ci.cart_id = ?", [$cart['id']]
        );
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
        Response::json(['items' => $items, 'total' => $total, 'count' => count($items)]);
    }

    public function addItem(Request $req): void
    {
        $data = $req->validate(['product_id' => 'required|numeric', 'quantity' => 'required|numeric']);
        $db   = DB::getInstance();
        $prod = $db->fetch("SELECT * FROM products WHERE id=? AND status='active'", [$data['product_id']]);
        if (!$prod) Response::json(['error' => 'Producto no disponible.'], 404);
        if ($prod['stock'] < $data['quantity']) Response::json(['error' => 'Stock insuficiente.'], 400);
        $cart = $this->getOrCreateCart($req, $db);
        $existing = $db->fetch("SELECT * FROM cart_items WHERE cart_id=? AND product_id=?", [$cart['id'], $data['product_id']]);
        if ($existing) {
            $db->update('cart_items', ['quantity' => $existing['quantity'] + (int)$data['quantity']], 'id=?', [$existing['id']]);
        } else {
            $db->insert('cart_items', ['cart_id' => $cart['id'], 'product_id' => $data['product_id'], 'quantity' => $data['quantity']]);
        }
        Response::json(['message' => 'Agregado al carrito.']);
    }

    public function updateItem(Request $req): void
    {
        $db  = DB::getInstance();
        $id  = (int)$req->param('id');
        $qty = (int)$req->input('quantity', 1);
        if ($qty < 1) { $db->delete('cart_items', 'id=?', [$id]); Response::json(['message' => 'Item eliminado.']); }
        $db->update('cart_items', ['quantity' => $qty], 'id=?', [$id]);
        Response::json(['message' => 'Carrito actualizado.']);
    }

    public function removeItem(Request $req): void
    {
        DB::getInstance()->delete('cart_items', 'id=?', [(int)$req->param('id')]);
        Response::json(['message' => 'Item eliminado.']);
    }

    public function clear(Request $req): void
    {
        $db   = DB::getInstance();
        $cart = $this->getOrCreateCart($req, $db);
        $db->delete('cart_items', 'cart_id=?', [$cart['id']]);
        Response::json(['message' => 'Carrito vacío.']);
    }
}

// ============================================================
// OrderController — /api/orders/*
// ============================================================
class OrderController
{
    public function index(Request $req): void
    {
        $db   = DB::getInstance();
        $page = (int)$req->input('page', 1);
        $sql  = "SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS items_count
                 FROM orders o WHERE o.buyer_id=? ORDER BY o.created_at DESC";
        Response::json($db->paginate($sql, [Auth::id()], $page));
    }

    public function show(Request $req): void
    {
        $db    = DB::getInstance();
        $order = $db->fetch("SELECT * FROM orders WHERE id=? AND buyer_id=?", [$req->param('id'), Auth::id()]);
        if (!$order) Response::json(['error' => 'Orden no encontrada.'], 404);
        $order['items']   = $db->fetchAll("SELECT * FROM order_items WHERE order_id=?", [$order['id']]);
        $order['tracking'] = $db->fetchAll("SELECT * FROM order_tracking WHERE order_id=? ORDER BY created_at DESC", [$order['id']]);
        Response::json($order);
    }

    public function checkout(Request $req): void
    {
        $data = $req->validate(['address_id' => 'required|numeric', 'payment_method' => 'required']);
        $db   = DB::getInstance();
        // Get cart
        $cart  = $db->fetch("SELECT * FROM carts WHERE user_id=? AND expires_at > NOW()", [Auth::id()]);
        $items = $db->fetchAll("SELECT ci.*, p.price, p.title, p.sku, p.seller_id, p.stock FROM cart_items ci JOIN products p ON p.id=ci.product_id WHERE ci.cart_id=?", [$cart['id'] ?? 0]);
        if (empty($items)) Response::json(['error' => 'Carrito vacío.'], 422);
        $addr  = $db->fetch("SELECT * FROM user_addresses WHERE id=? AND user_id=?", [$data['address_id'], Auth::id()]);
        if (!$addr) Response::json(['error' => 'Dirección inválida.'], 422);

        $db->beginTransaction();
        try {
            $subtotal    = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
            $orderNumber = 'MS-' . strtoupper(substr(uniqid(), -8));
            $orderId     = $db->insert('orders', [
                'order_number'    => $orderNumber,
                'buyer_id'        => Auth::id(),
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'shipping_cost'   => 0,
                'total'           => $subtotal,
                'payment_method'  => $data['payment_method'],
                'address_snapshot'=> json_encode($addr),
            ]);
            foreach ($items as $item) {
                if ($item['stock'] < $item['quantity']) {
                    $db->rollback();
                    Response::json(['error' => "Stock insuficiente: {$item['title']}"], 400);
                }
                $db->insert('order_items', [
                    'order_id'   => $orderId, 'product_id' => $item['product_id'],
                    'seller_id'  => $item['seller_id'],  'title' => $item['title'],
                    'sku'        => $item['sku'],         'price' => $item['price'],
                    'quantity'   => $item['quantity'],    'subtotal' => $item['price'] * $item['quantity'],
                ]);
                $db->query("UPDATE products SET stock=stock-?, sales_count=sales_count+? WHERE id=?",
                    [$item['quantity'], $item['quantity'], $item['product_id']]);
            }
            $db->insert('order_tracking', ['order_id' => $orderId, 'status' => 'pending', 'description' => 'Orden creada, esperando pago.']);
            $db->delete('cart_items', 'cart_id=?', [$cart['id']]);
            $db->commit();
            Response::json(['order_id' => $orderId, 'order_number' => $orderNumber, 'total' => $subtotal], 201);
        } catch (\Throwable $e) {
            $db->rollback();
            Response::json(['error' => 'Error al crear la orden.'], 500);
        }
    }
}

// ============================================================
// AdminController — /api/admin/*
// ============================================================
class AdminController
{
    public function dashboard(Request $req): void
    {
        $db   = DB::getInstance();
        $data = [
            'users_total'    => $db->fetch("SELECT COUNT(*) AS c FROM users")['c'],
            'users_today'    => $db->fetch("SELECT COUNT(*) AS c FROM users WHERE DATE(created_at)=CURDATE()")['c'],
            'products_total' => $db->fetch("SELECT COUNT(*) AS c FROM products WHERE status != 'deleted'")['c'],
            'orders_today'   => $db->fetch("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at)=CURDATE()")['c'],
            'revenue_today'  => $db->fetch("SELECT IFNULL(SUM(total),0) AS r FROM orders WHERE DATE(created_at)=CURDATE() AND status NOT IN ('cancelled','refunded')")['r'],
            'revenue_month'  => $db->fetch("SELECT IFNULL(SUM(total),0) AS r FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND status NOT IN ('cancelled','refunded')")['r'],
            'recent_orders'  => $db->fetchAll("SELECT o.*, u.name AS buyer FROM orders o JOIN users u ON u.id=o.buyer_id ORDER BY o.created_at DESC LIMIT 10"),
            'top_products'   => $db->fetchAll("SELECT p.title, p.sales_count, p.price FROM products p ORDER BY p.sales_count DESC LIMIT 10"),
            'revenue_chart'  => $db->fetchAll("SELECT DATE(created_at) AS date, SUM(total) AS total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date"),
        ];
        Response::json($data);
    }

    public function users(Request $req): void
    {
        $db   = DB::getInstance();
        $page = (int)$req->input('page', 1);
        $q    = $req->input('q', '');
        $sql  = "SELECT id, uuid, name, email, role, status, created_at FROM users WHERE 1=1";
        $b    = [];
        if ($q) { $sql .= " AND (name LIKE ? OR email LIKE ?)"; $b[] = "%{$q}%"; $b[] = "%{$q}%"; }
        $sql .= " ORDER BY created_at DESC";
        Response::json($db->paginate($sql, $b, $page));
    }

    public function updateUser(Request $req): void
    {
        $id   = (int)$req->param('id');
        $data = array_intersect_key($req->all(), array_flip(['role','status','name']));
        DB::getInstance()->update('users', $data, 'id=?', [$id]);
        Response::json(['message' => 'Usuario actualizado.']);
    }

    public function products(Request $req): void
    {
        $db   = DB::getInstance();
        $page = (int)$req->input('page', 1);
        $sql  = "SELECT p.*, u.name AS seller FROM products p JOIN users u ON u.id=p.seller_id WHERE p.status != 'deleted' ORDER BY p.created_at DESC";
        Response::json($db->paginate($sql, [], $page));
    }

    public function orders(Request $req): void
    {
        $db   = DB::getInstance();
        $page = (int)$req->input('page', 1);
        $status = $req->input('status');
        $sql  = "SELECT o.*, u.name AS buyer FROM orders o JOIN users u ON u.id=o.buyer_id WHERE 1=1";
        $b    = [];
        if ($status) { $sql .= " AND o.status=?"; $b[] = $status; }
        $sql .= " ORDER BY o.created_at DESC";
        Response::json($db->paginate($sql, $b, $page));
    }

    public function updateOrderStatus(Request $req): void
    {
        $id     = (int)$req->param('id');
        $status = $req->input('status');
        $db     = DB::getInstance();
        $db->update('orders', ['status' => $status], 'id=?', [$id]);
        $db->insert('order_tracking', ['order_id' => $id, 'status' => $status, 'description' => $req->input('description', '')]);
        Response::json(['message' => 'Estado actualizado.']);
    }

    public function categories(Request $req): void
    {
        Response::json(DB::getInstance()->fetchAll("SELECT * FROM categories ORDER BY sort_order, name"));
    }

    public function auditLog(Request $req): void
    {
        $db   = DB::getInstance();
        $page = (int)$req->input('page', 1);
        Response::json($db->paginate("SELECT al.*, u.name AS user FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id ORDER BY al.created_at DESC", [], $page));
    }
}

// ============================================================
// ProfileController — /api/profile/*
// ============================================================
class ProfileController
{
    // ── Validar RUT chileno formato xx.xxx.xxx-x ────────────
    private function validateRut(string $rut): bool
    {
        $clean = preg_replace('/[^0-9kK]/', '', $rut);
        if (strlen($clean) < 8 || strlen($clean) > 9) return false;
        $body   = substr($clean, 0, -1);
        $dv     = strtolower(substr($clean, -1));
        $sum    = 0;
        $factor = 2;
        for ($i = strlen($body) - 1; $i >= 0; $i--) {
            $sum += (int)$body[$i] * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }
        $remainder = 11 - ($sum % 11);
        $expected  = match($remainder) {
            11 => '0', 10 => 'k', default => (string)$remainder
        };
        return $dv === $expected;
    }

    private function formatRut(string $rut): string
    {
        $clean = preg_replace('/[^0-9kK]/', '', strtoupper($rut));
        $dv    = substr($clean, -1);
        $body  = substr($clean, 0, -1);
        return number_format((int)$body, 0, '', '.') . '-' . $dv;
    }

    public function show(Request $req): void
    {
        $user = DB::getInstance()->fetch(
            "SELECT id, uuid, name, email, role, status, phone, rut, rut_verified, birthdate, avatar, created_at FROM users WHERE id=?",
            [Auth::id()]
        );
        Response::json($user);
    }

    public function update(Request $req): void
    {
        $data   = $req->all();
        $db     = DB::getInstance();
        $user   = $db->fetch("SELECT rut FROM users WHERE id=?", [Auth::id()]);
        $update = [];

        if (!empty($data['name']))      $update['name']      = trim($data['name']);
        if (isset($data['phone']))      $update['phone']     = trim($data['phone']) ?: null;
        if (isset($data['birthdate'])) {
            $bd = trim($data['birthdate']);
            $update['birthdate'] = ($bd && preg_match('/^\d{4}-\d{2}-\d{2}$/', $bd)) ? $bd : null;
        }

        // RUT: obligatorio al registrarse, inmutable una vez guardado
        if (!empty($data['rut'])) {
            if (!empty($user['rut'])) {
                Response::json(['error' => 'El RUT ya fue registrado y no puede modificarse por razones de seguridad.'], 403);
            }
            $rawRut = trim($data['rut']);
            if (!$this->validateRut($rawRut)) {
                Response::json(['error' => 'RUT inválido. Verifica el formato y el dígito verificador.'], 422);
            }
            $formatted = $this->formatRut($rawRut);
            $exists = $db->fetch("SELECT id FROM users WHERE rut=? AND id != ?", [$formatted, Auth::id()]);
            if ($exists) {
                Response::json(['error' => 'Este RUT ya está asociado a otra cuenta.'], 409);
            }
            $update['rut']          = $formatted;
            $update['rut_verified'] = 1;
        }

        if (empty($update)) Response::json(['error' => 'Sin datos para actualizar.'], 422);
        $db->update('users', $update, 'id=?', [Auth::id()]);
        Response::json(['message' => 'Perfil actualizado correctamente.']);
    }

    public function changePassword(Request $req): void
    {
        $data = $req->validate(['current' => 'required', 'password' => 'required|min:8']);
        $db   = DB::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE id=?", [Auth::id()]);
        if (!password_verify($data['current'], $user['password'])) {
            Response::json(['error' => 'La contraseña actual es incorrecta.'], 400);
        }
        $db->update('users', ['password' => Auth::hashPassword($data['password'])], 'id=?', [Auth::id()]);
        // Revocar todos los tokens excepto el actual
        $current = $req->bearerToken();
        $db->query("DELETE FROM user_tokens WHERE user_id=? AND token != ?", [Auth::id(), $current]);
        Response::json(['message' => 'Contraseña actualizada.']);
    }

    public function uploadAvatar(Request $req): void
    {
        if (empty($_FILES['avatar'])) {
            Response::json(['error' => 'No se recibió ninguna imagen.'], 400);
        }

        $file     = $_FILES['avatar'];
        $allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!isset($allowed[$mimeType])) {
            Response::json(['error' => 'Formato no permitido. Solo JPG, PNG o WebP.'], 422);
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            Response::json(['error' => 'La imagen no puede superar 2MB.'], 422);
        }

        // Crear carpeta si no existe
        $uploadDir = defined('BASE_PATH')
            ? BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars'
            : __DIR__ . '/../public/uploads/avatars';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Nombre único — eliminar avatar anterior
        $db       = DB::getInstance();
        $current  = $db->fetch("SELECT avatar FROM users WHERE id=?", [Auth::id()]);
        if (!empty($current['avatar'])) {
            $oldFile = defined('BASE_PATH')
                ? BASE_PATH . '/public' . $current['avatar']
                : __DIR__ . '/../public' . $current['avatar'];
            if (file_exists($oldFile)) @unlink($oldFile);
        }

        $ext      = $allowed[$mimeType];
        $filename = 'avatar_' . Auth::id() . '_' . time() . '.' . $ext;
        $dest     = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Response::json(['error' => 'Error al guardar la imagen.'], 500);
        }

        $avatarUrl = '/uploads/avatars/' . $filename;
        $db->update('users', ['avatar' => $avatarUrl], 'id=?', [Auth::id()]);

        Response::json(['avatar_url' => $avatarUrl, 'message' => 'Avatar actualizado.']);
    }

    public function deleteAvatar(Request $req): void
    {
        $db   = DB::getInstance();
        $user = $db->fetch("SELECT avatar FROM users WHERE id=?", [Auth::id()]);
        if (!empty($user['avatar'])) {
            $file = defined('BASE_PATH')
                ? BASE_PATH . '/public' . $user['avatar']
                : __DIR__ . '/../public' . $user['avatar'];
            if (file_exists($file)) @unlink($file);
        }
        $db->update('users', ['avatar' => null], 'id=?', [Auth::id()]);
        Response::json(['message' => 'Avatar eliminado.']);
    }

    public function getAddresses(Request $req): void
    {
        $rows = DB::getInstance()->fetchAll(
            "SELECT * FROM user_addresses WHERE user_id=? ORDER BY is_default DESC, id ASC",
            [Auth::id()]
        );
        Response::json($rows);
    }

    public function storeAddress(Request $req): void
    {
        $data = $req->validate([
            'full_name' => 'required|min:2',
            'address'   => 'required|min:5',
            'city'      => 'required',
            'region'    => 'required',
        ]);
        $db        = DB::getInstance();
        $isDefault = (bool)($req->input('is_default', false));
        if ($isDefault) {
            $db->query("UPDATE user_addresses SET is_default=0 WHERE user_id=?", [Auth::id()]);
        }
        $id = $db->insert('user_addresses', [
            'user_id'    => Auth::id(),
            'label'      => $req->input('label', 'Casa'),
            'full_name'  => trim($data['full_name']),
            'address'    => trim($data['address']),
            'city'       => trim($data['city']),
            'region'     => trim($data['region']),
            'zip_code'   => $req->input('zip_code') ?: null,
            'country'    => 'CL',
            'is_default' => $isDefault ? 1 : 0,
        ]);
        Response::json(['id' => $id, 'message' => 'Dirección agregada.'], 201);
    }

    public function updateAddress(Request $req): void
    {
        $id   = (int)$req->param('id');
        $db   = DB::getInstance();
        $addr = $db->fetch("SELECT * FROM user_addresses WHERE id=? AND user_id=?", [$id, Auth::id()]);
        if (!$addr) Response::json(['error' => 'Dirección no encontrada.'], 404);
        $isDefault = filter_var($req->input('is_default', false), FILTER_VALIDATE_BOOLEAN);
        if ($isDefault) {
            $db->query("UPDATE user_addresses SET is_default=0 WHERE user_id=?", [Auth::id()]);
        }
        $db->update('user_addresses', [
            'label'      => $req->input('label', $addr['label']),
            'full_name'  => trim($req->input('full_name', $addr['full_name'])),
            'address'    => trim($req->input('address', $addr['address'])),
            'city'       => trim($req->input('city', $addr['city'])),
            'region'     => trim($req->input('region', $addr['region'])),
            'zip_code'   => $req->input('zip_code') ?: null,
            'is_default' => $isDefault ? 1 : 0,
        ], 'id=?', [$id]);
        Response::json(['message' => 'Dirección actualizada.']);
    }

    public function setDefault(Request $req): void
    {
        $id = (int)$req->param('id');
        $db = DB::getInstance();
        $db->query("UPDATE user_addresses SET is_default=0 WHERE user_id=?", [Auth::id()]);
        $db->update('user_addresses', ['is_default' => 1], 'id=? AND user_id=?', [$id, Auth::id()]);
        Response::json(['message' => 'Dirección principal actualizada.']);
    }

    public function deleteAddress(Request $req): void
    {
        $id = (int)$req->param('id');
        $db = DB::getInstance();
        $ok = $db->delete('user_addresses', 'id=? AND user_id=?', [$id, Auth::id()]);
        if (!$ok) Response::json(['error' => 'Dirección no encontrada.'], 404);
        Response::json(['message' => 'Dirección eliminada.']);
    }

    public function deleteAccount(Request $req): void
    {
        $db = DB::getInstance();
        $db->update('users', ['status' => 'suspended', 'email' => 'deleted_' . Auth::id() . '_' . time() . '@deleted.ms'], 'id=?', [Auth::id()]);
        $db->delete('user_tokens', 'user_id=?', [Auth::id()]);
        Response::json(['message' => 'Cuenta eliminada.']);
    }
}

// ============================================================
// ReviewController
// ============================================================
class ReviewController
{
    public function store(Request $req): void
    {
        $data = $req->validate(['product_id' => 'required|numeric', 'rating' => 'required|numeric', 'body' => 'required|min:10']);
        $db   = DB::getInstance();
        $id   = $db->insert('reviews', [
            'product_id' => $data['product_id'],
            'user_id'    => Auth::id(),
            'order_id'   => $req->input('order_id'),
            'rating'     => min(5, max(1, (int)$data['rating'])),
            'title'      => $req->input('title'),
            'body'       => $data['body'],
            'status'     => 'approved',
        ]);
        // Update product avg
        $db->query("UPDATE products SET rating_avg=(SELECT AVG(rating) FROM reviews WHERE product_id=? AND status='approved'), rating_count=(SELECT COUNT(*) FROM reviews WHERE product_id=? AND status='approved') WHERE id=?",
            [$data['product_id'], $data['product_id'], $data['product_id']]);
        Response::json(['id' => $id], 201);
    }
}

