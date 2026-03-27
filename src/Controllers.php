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

        // Vincular carrito guest al usuario al hacer login
        $sessionKey = $_COOKIE['ms_cart'] ?? null;
        if ($sessionKey) {
            $db = DB::getInstance();
            $guestCart = $db->fetch("SELECT * FROM carts WHERE session_key=? AND user_id IS NULL AND expires_at > NOW()", [$sessionKey]);
            if ($guestCart) {
                // Verificar si ya tiene carrito con items
                $userCart = $db->fetch("SELECT * FROM carts WHERE user_id=? AND expires_at > NOW()", [$user['id']]);
                if ($userCart) {
                    // Mover items del carrito guest al del usuario
                    $db->query("UPDATE cart_items SET cart_id=? WHERE cart_id=?", [$userCart['id'], $guestCart['id']]);
                    $db->delete('carts', 'id=?', [$guestCart['id']]);
                } else {
                    // Vincular el carrito guest al usuario
                    $db->update('carts', [
                        'user_id'    => $user['id'],
                        'expires_at' => date('Y-m-d H:i:s', time() + 86400 * 30),
                    ], 'id=?', [$guestCart['id']]);
                }
            }
        }

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
        $sql  = "SELECT p.*, 
                    (SELECT url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image
                 FROM products p
                 WHERE p.seller_id=? AND p.status != 'deleted'
                 ORDER BY p.created_at DESC";
        $result = $db->paginate($sql, [Auth::id()], $page);
        // Agregar imágenes completas a cada producto
        foreach ($result['data'] as &$product) {
            $product['images'] = $db->fetchAll(
                "SELECT id, url, is_primary, sort_order FROM product_images 
                 WHERE product_id=? ORDER BY sort_order ASC, is_primary DESC",
                [$product['id']]
            );
        }
        Response::json($result);
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
                // Buscar carrito guest y vincularlo
                $sessionKey = $_COOKIE['ms_cart'] ?? null;
                if ($sessionKey) {
                    $guestCart = $db->fetch("SELECT * FROM carts WHERE session_key=? AND expires_at > NOW()", [$sessionKey]);
                    if ($guestCart) {
                        $db->update('carts', [
                            'user_id'    => $userId,
                            'expires_at' => date('Y-m-d H:i:s', time() + 86400 * 30),
                        ], 'id=?', [$guestCart['id']]);
                        return $db->fetch("SELECT * FROM carts WHERE id=?", [$guestCart['id']]);
                    }
                }
                // Crear carrito nuevo para el usuario
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
            "SELECT ci.*, p.title, p.price, p.free_shipping, p.stock, p.seller_id, pi.url AS image
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

        // Bloquear vendedor comprando sus propios productos
        $uid = Auth::id();
        if ($uid && (int)$prod['seller_id'] === (int)$uid) {
            Response::json(['error' => 'No puedes agregar tus propios productos al carrito.'], 422);
        }
        // Bloquear admin comprando
        if ($uid) {
            $buyer = $db->fetch("SELECT role FROM users WHERE id=?", [$uid]);
            if ($buyer && $buyer['role'] === 'admin') {
                Response::json(['error' => 'Los administradores no pueden realizar compras.'], 422);
            }
        }
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

        // Buscar el carrito con items — primero por user_id, luego guest
        $userId = Auth::id();
        $cart   = null;

        // 1. Carrito del usuario con items
        $userCart = $db->fetch("SELECT * FROM carts WHERE user_id=? AND expires_at > NOW()", [$userId]);
        if ($userCart) {
            $itemCount = (int)$db->fetch("SELECT COUNT(*) AS c FROM cart_items WHERE cart_id=?", [$userCart['id']])['c'];
            if ($itemCount > 0) $cart = $userCart;
        }

        // 2. Si carrito usuario vacío → buscar guest por cookie y migrar
        if (!$cart) {
            $sessionKey = $_COOKIE['ms_cart'] ?? null;
            if ($sessionKey) {
                $guestCart = $db->fetch("SELECT * FROM carts WHERE session_key=? AND expires_at > NOW()", [$sessionKey]);
                if ($guestCart) {
                    $guestItems = (int)$db->fetch("SELECT COUNT(*) AS c FROM cart_items WHERE cart_id=?", [$guestCart['id']])['c'];
                    if ($guestItems > 0) {
                        // Migrar items al carrito del usuario (o usar el guest directamente)
                        if ($userCart) {
                            $db->query("UPDATE cart_items SET cart_id=? WHERE cart_id=?", [$userCart['id'], $guestCart['id']]);
                            $db->query("DELETE FROM carts WHERE id=?", [$guestCart['id']]);
                            $cart = $userCart;
                        } else {
                            $db->update('carts', ['user_id' => $userId, 'expires_at' => date('Y-m-d H:i:s', time() + 86400 * 30)], 'id=?', [$guestCart['id']]);
                            $cart = $guestCart;
                        }
                    }
                }
            }
        }

        // 3. Último recurso — usar carrito usuario aunque esté vacío (mostrará error "carrito vacío")
        if (!$cart) $cart = $userCart;

        $items = $db->fetchAll("SELECT ci.*, p.price, p.title, p.sku, p.seller_id, p.stock FROM cart_items ci JOIN products p ON p.id=ci.product_id WHERE ci.cart_id=?", [$cart['id'] ?? 0]);
        if (empty($items)) Response::json(['error' => 'Carrito vacío. Agrega productos antes de continuar.'], 422);

        // Validar que el comprador no sea el vendedor de ningún item
        $buyerInfo = $db->fetch("SELECT role FROM users WHERE id=?", [Auth::id()]);
        if ($buyerInfo && $buyerInfo['role'] === 'admin') {
            Response::json(['error' => 'Los administradores no pueden realizar compras.'], 422);
        }
        foreach ($items as $item) {
            if ((int)$item['seller_id'] === (int)Auth::id()) {
                Response::json(['error' => 'Tu carrito contiene productos propios. Elimínalos antes de continuar.'], 422);
            }
        }
        $addr  = $db->fetch("SELECT * FROM user_addresses WHERE id=? AND user_id=?", [$data['address_id'], Auth::id()]);
        if (!$addr) Response::json(['error' => 'Dirección inválida.'], 422);

        $db->beginTransaction();
        try {
            $subtotal    = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
            $orderNumber = 'MS-' . strtoupper(substr(uniqid(), -8));
            $sellerId    = $items[0]['seller_id'] ?? null;
            $ivaAmount   = round($subtotal - ($subtotal / 1.19), 2);
            $commAmount  = round($subtotal * 0.05, 2);
            $vendorNet   = round($subtotal - $commAmount, 2);
            $orderId     = $db->insert('orders', [
                'order_number'     => $orderNumber,
                'buyer_id'         => Auth::id(),
                'seller_id'        => $sellerId,
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping_cost'    => 0,
                'total'            => $subtotal,
                'subtotal_neto'    => round($subtotal / 1.19, 2),
                'iva_amount'       => $ivaAmount,
                'commission_amount'=> $commAmount,
                'vendor_net'       => $vendorNet,
                'payment_method'   => $data['payment_method'],
                'address_snapshot' => json_encode($addr),
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

            // Notificar al vendedor — nuevo pedido
            $this->notify($db, (int)$sellerId, 'new_order', '🛍️ Nuevo pedido recibido', "Orden {$orderNumber} por $" . number_format($subtotal, 0, ',', '.') . " CLP. Tienes 24h para aceptar.", 'bi-bag-check', 'success', 'order', $orderId);

            $db->commit();
            // Email pedido creado → comprador
            if (\MercadoSordo\Core\Mailer::isEnabled()) {
                try {
                    $buyerU   = $db->fetch("SELECT name, email FROM users WHERE id=?", [Auth::id()]);
                    $orderRow = $db->fetch("SELECT * FROM orders WHERE id=?", [$orderId]);
                    $itmRows  = $db->fetchAll("SELECT * FROM order_items WHERE order_id=?", [$orderId]);
                    (new \MercadoSordo\Core\Mailer())->send($buyerU['email'], $buyerU['name'], '✅ Pedido creado — ' . $orderNumber, \MercadoSordo\Core\Mailer::orderCreated($orderRow, $itmRows));
                } catch (\Throwable $me) { error_log('[Mail] checkout: ' . $me->getMessage()); }
            }
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
        $db = DB::getInstance();

        // ── KPIs principales ──────────────────────────────────────────────
        $usersTotal    = (int)$db->fetch("SELECT COUNT(*) AS c FROM users WHERE status != 'suspended'")['c'];
        $usersToday    = (int)$db->fetch("SELECT COUNT(*) AS c FROM users WHERE DATE(created_at)=CURDATE()")['c'];
        $usersWeek     = (int)$db->fetch("SELECT COUNT(*) AS c FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'];
        $sellersTotal  = (int)$db->fetch("SELECT COUNT(*) AS c FROM users WHERE role IN ('seller','admin')")['c'];
        $productsTotal = (int)$db->fetch("SELECT COUNT(*) AS c FROM products WHERE status='active'")['c'];
        $ordersTotal   = (int)$db->fetch("SELECT COUNT(*) AS c FROM orders WHERE status NOT IN ('cancelled')")['c'];
        $ordersToday   = (int)$db->fetch("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at)=CURDATE()")['c'];
        $ordersWeek    = (int)$db->fetch("SELECT COUNT(*) AS c FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status NOT IN ('cancelled')")['c'];
        $ordersPending = (int)$db->fetch("SELECT COUNT(*) AS c FROM orders WHERE status='paid'")['c'];
        $ordersDispute = (int)$db->fetch("SELECT COUNT(*) AS c FROM orders WHERE status='dispute'")['c'];

        // ── Revenue ───────────────────────────────────────────────────────
        $revenueToday  = (float)$db->fetch("SELECT IFNULL(SUM(total),0) AS r FROM orders WHERE DATE(created_at)=CURDATE() AND status NOT IN ('cancelled','refunded')")['r'];
        $revenueWeek   = (float)$db->fetch("SELECT IFNULL(SUM(total),0) AS r FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status NOT IN ('cancelled','refunded')")['r'];
        $revenueMonth  = (float)$db->fetch("SELECT IFNULL(SUM(total),0) AS r FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND status NOT IN ('cancelled','refunded')")['r'];
        $revenueTotal  = (float)$db->fetch("SELECT IFNULL(SUM(total),0) AS r FROM orders WHERE status NOT IN ('cancelled','refunded')")['r'];
        $commTotal     = round($revenueTotal * 0.05, 2);

        // ── Órdenes por estado ────────────────────────────────────────────
        $ordersByStatus = $db->fetchAll(
            "SELECT status, COUNT(*) AS count FROM orders GROUP BY status ORDER BY count DESC"
        );

        // ── Gráfico ingresos 30 días ──────────────────────────────────────
        $revenueChart = $db->fetchAll(
            "SELECT DATE(created_at) AS date, SUM(total) AS total, COUNT(*) AS orders
             FROM orders
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               AND status NOT IN ('cancelled','refunded')
             GROUP BY DATE(created_at) ORDER BY date"
        );

        // ── Top productos más vendidos ────────────────────────────────────
        $topSelling = $db->fetchAll(
            "SELECT p.id, p.title, p.price, p.sales_count, p.views, p.rating_avg, p.rating_count,
                    (SELECT url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS image,
                    u.name AS seller_name,
                    IFNULL(p.sales_count * p.price, 0) AS revenue
             FROM products p
             LEFT JOIN users u ON u.id=p.seller_id
             WHERE p.status='active'
             ORDER BY p.sales_count DESC LIMIT 8"
        );

        // ── Top productos más vistos ──────────────────────────────────────
        $topViewed = $db->fetchAll(
            "SELECT p.id, p.title, p.price, p.views, p.sales_count, p.rating_avg,
                    (SELECT url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS image,
                    u.name AS seller_name
             FROM products p
             LEFT JOIN users u ON u.id=p.seller_id
             WHERE p.status='active'
             ORDER BY p.views DESC LIMIT 8"
        );

        // ── Top favoritos (wishlists) ─────────────────────────────────────
        $topFavorites = $db->fetchAll(
            "SELECT p.id, p.title, p.price, COUNT(w.id) AS wishlist_count,
                    (SELECT url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS image
             FROM products p
             LEFT JOIN wishlists w ON w.product_id=p.id
             WHERE p.status='active'
             GROUP BY p.id, p.title, p.price ORDER BY wishlist_count DESC LIMIT 6"
        );

        // ── Mejores vendedores ────────────────────────────────────────────
        $topSellers = $db->fetchAll(
            "SELECT u.id, u.name, u.avatar,
                    COUNT(DISTINCT p.id) AS products_count,
                    IFNULL(SUM(oi.quantity),0) AS total_sales,
                    IFNULL(SUM(oi.subtotal),0) AS total_revenue,
                    ROUND(AVG(p.rating_avg),2) AS avg_rating
             FROM users u
             LEFT JOIN products p ON p.seller_id=u.id AND p.status='active'
             LEFT JOIN order_items oi ON oi.seller_id=u.id
             WHERE u.role IN ('seller','admin')
             GROUP BY u.id, u.name, u.avatar ORDER BY total_sales DESC LIMIT 6"
        );

        // ── Mejores compradores ───────────────────────────────────────────
        $topBuyers = $db->fetchAll(
            "SELECT u.id, u.name, u.avatar,
                    COUNT(DISTINCT o.id) AS orders_count,
                    IFNULL(SUM(o.total),0) AS total_spent
             FROM users u
             LEFT JOIN orders o ON o.buyer_id=u.id AND o.status NOT IN ('cancelled','refunded')
             WHERE u.role IN ('buyer','seller')
             GROUP BY u.id ORDER BY total_spent DESC LIMIT 6"
        );

        // ── Reputación global ─────────────────────────────────────────────
        $avgRating    = (float)($db->fetch("SELECT ROUND(AVG(rating),2) AS r FROM reviews WHERE status='approved'")['r'] ?? 0);
        $totalReviews = (int)$db->fetch("SELECT COUNT(*) AS c FROM reviews WHERE status='approved'")['c'];
        $ratingDist   = $db->fetchAll(
            "SELECT rating, COUNT(*) AS count FROM reviews WHERE status='approved' GROUP BY rating ORDER BY rating DESC"
        );

        // ── Actividad reciente ────────────────────────────────────────────
        $recentOrders = $db->fetchAll(
            "SELECT o.order_number, o.total, o.status, o.created_at,
                    u.name AS buyer_name
             FROM orders o JOIN users u ON u.id=o.buyer_id
             ORDER BY o.created_at DESC LIMIT 10"
        );

        // ── Categorías más activas ────────────────────────────────────────
        $topCategories = $db->fetchAll(
            "SELECT c.name, COUNT(p.id) AS products, IFNULL(SUM(p.sales_count),0) AS sales
             FROM categories c
             LEFT JOIN products p ON p.category_id=c.id AND p.status='active'
             GROUP BY c.id, c.name ORDER BY sales DESC LIMIT 8"
        );

        Response::json([
            'kpis' => [
                'users_total'    => $usersTotal,
                'users_today'    => $usersToday,
                'users_week'     => $usersWeek,
                'sellers_total'  => $sellersTotal,
                'products_total' => $productsTotal,
                'orders_total'   => $ordersTotal,
                'orders_today'   => $ordersToday,
                'orders_week'    => $ordersWeek,
                'orders_pending' => $ordersPending,
                'orders_dispute' => $ordersDispute,
                'revenue_today'  => $revenueToday,
                'revenue_week'   => $revenueWeek,
                'revenue_month'  => $revenueMonth,
                'revenue_total'  => $revenueTotal,
                'commission_total' => $commTotal,
                'avg_rating'     => $avgRating,
                'total_reviews'  => $totalReviews,
            ],
            'orders_by_status' => $ordersByStatus,
            'revenue_chart'    => $revenueChart,
            'top_selling'      => $topSelling,
            'top_viewed'       => $topViewed,
            'top_favorites'    => $topFavorites,
            'top_sellers'      => $topSellers,
            'top_buyers'       => $topBuyers,
            'rating_dist'      => $ratingDist,
            'recent_orders'    => $recentOrders,
            'top_categories'   => $topCategories,
        ]);
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

    public function dailyReport(Request $req): void
    {
        // Solo accesible con clave secreta (para cron job) o admin autenticado
        $secret = $req->input('secret', '');
        $envSecret = getenv('REPORT_SECRET') ?: '';
        if ($envSecret && $secret !== $envSecret && !Auth::is('admin')) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $db   = DB::getInstance();
        $today = date('Y-m-d');

        $stats = [
            'orders_today'    => (int)$db->fetch("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at)=?", [$today])['c'],
            'revenue_today'   => (float)$db->fetch("SELECT IFNULL(SUM(total),0) AS r FROM orders WHERE DATE(created_at)=? AND status NOT IN ('cancelled','refunded')", [$today])['r'],
            'commission_today'=> 0,
            'new_users'       => (int)$db->fetch("SELECT COUNT(*) AS c FROM users WHERE DATE(created_at)=?", [$today])['c'],
        ];
        $stats['commission_today'] = round($stats['revenue_today'] * 0.05, 2);

        $recentOrders = $db->fetchAll(
            "SELECT o.order_number, u.name AS buyer_name, o.total, o.status
             FROM orders o JOIN users u ON u.id=o.buyer_id
             ORDER BY o.created_at DESC LIMIT 10"
        );

        // Enviar email al admin
        if (\MercadoSordo\Core\Mailer::isEnabled()) {
            try {
                $admin = $db->fetch("SELECT name, email FROM users WHERE role='admin' LIMIT 1");
                if ($admin) {
                    $html = \MercadoSordo\Core\Mailer::adminDailyReport($stats, $recentOrders);
                    (new \MercadoSordo\Core\Mailer())->send($admin['email'], $admin['name'], '📊 Reporte diario MercadoSordo — ' . date('d/m/Y'), $html);
                }
            } catch (\Throwable $e) { error_log('[Mail] report: ' . $e->getMessage()); }
        }

        Response::json(['message' => 'Reporte enviado.', 'stats' => $stats]);
    }

    public function auditLog(Request $req): void
    {
        $db   = DB::getInstance();
        $page = (int)$req->input('page', 1);
        Response::json($db->paginate("SELECT al.*, u.name AS user FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id ORDER BY al.created_at DESC", [], $page));
    }
}

// ============================================================
// ImageUploadTrait — detección MIME robusta para móviles
// ============================================================
trait ImageUploadTrait
{
    /**
     * Detecta MIME real de imagen usando magic bytes
     * Compatible con Android (Samsung, Pixel), iOS (HEIC→JPG), desktop
     * Retorna 'image/jpeg' | 'image/png' | 'image/webp' | null si no es imagen
     */
    private function detectImageMime(string $tmpPath, string $originalName = ''): ?string
    {
        // Validar que el archivo existe y fue subido correctamente
        if (empty($tmpPath) || !file_exists($tmpPath) || !is_uploaded_file($tmpPath)) {
            // Fallback solo por extensión
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $extMap = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','heic'=>'image/jpeg','heif'=>'image/jpeg'];
            return $extMap[$ext] ?? null;
        }

        // 1. Leer magic bytes (primeros 12 bytes)
        $handle = fopen($tmpPath, 'rb');
        if (!$handle) return null;
        $bytes = fread($handle, 12);
        fclose($handle);

        // JPEG: FF D8 FF
        if (substr($bytes, 0, 3) === "ÿØÿ") return 'image/jpeg';
        // PNG: 89 50 4E 47 0D 0A 1A 0A
        if (substr($bytes, 0, 8) === "PNG

") return 'image/png';
        // WebP: RIFF????WEBP
        if (substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP') return 'image/webp';
        // HEIC/HEIF: ftyp
        if (substr($bytes, 4, 4) === 'ftyp') return 'image/jpeg'; // convertir a jpg

        // 2. Fallback por extensión del nombre original
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $extMap = [
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',  'webp' => 'image/webp',
            'heic' => 'image/jpeg', 'heif' => 'image/jpeg',
        ];
        if (isset($extMap[$ext])) return $extMap[$ext];

        // 3. Fallback mime_content_type
        $mime = mime_content_type($tmpPath);
        $mimeMap = [
            'image/jpeg' => 'image/jpeg', 'image/jpg' => 'image/jpeg',
            'image/png'  => 'image/png',  'image/webp' => 'image/webp',
            'image/heic' => 'image/jpeg', 'image/heif' => 'image/jpeg',
        ];
        return $mimeMap[$mime] ?? null;
    }

    /**
     * Comprime y redimensiona imagen con GD
     * Productos: máx 1200x1200px, calidad 82% JPEG — ahorra ~70% espacio
     * Avatares:  máx 400x400px,   calidad 85% JPEG
     */
    private function compressImage(string $tmpPath, string $mimeType, int $maxDim = 1200, int $quality = 82): string
    {
        if (!function_exists('imagecreatefromjpeg') || empty($tmpPath) || !file_exists($tmpPath)) {
            return $tmpPath;
        }
        $src = match($mimeType) {
            'image/png'  => @imagecreatefrompng($tmpPath),
            'image/webp' => @imagecreatefromwebp($tmpPath),
            default      => @imagecreatefromjpeg($tmpPath),
        };
        if (!$src) return $tmpPath;

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Calcular nuevas dimensiones manteniendo proporción
        if ($origW > $origH) {
            $newW = min($origW, $maxDim);
            $newH = (int)round($origH * $newW / $origW);
        } else {
            $newH = min($origH, $maxDim);
            $newW = (int)round($origW * $newH / $origH);
        }

        $dst = imagecreatetruecolor($newW, $newH);
        // Fondo blanco para PNG con transparencia
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $outPath = $tmpPath . '_c.jpg';
        imagejpeg($dst, $outPath, $quality);
        imagedestroy($src);
        imagedestroy($dst);

        // Usar comprimida solo si es más pequeña
        if (file_exists($outPath) && filesize($outPath) < filesize($tmpPath)) {
            return $outPath;
        }
        @unlink($outPath);
        return $tmpPath;
    }
}

// ============================================================
// ProfileController — /api/profile/*
// ============================================================
class ProfileController
{
    use ImageUploadTrait;
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

        $db       = DB::getInstance();
        $file = $_FILES['avatar'];
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errMsg = [
                UPLOAD_ERR_PARTIAL => 'La imagen no se subió completamente. Intenta de nuevo.',
                UPLOAD_ERR_NO_FILE => 'No se recibió ninguna imagen.',
            ];
            $msg = $errMsg[$file['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'Error al subir imagen. Intenta de nuevo.';
            Response::json(['error' => $msg], 400);
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            Response::json(['error' => 'La imagen no puede superar 2MB.'], 422);
        }
        $mimeType = $this->detectImageMime($file['tmp_name'], $file['name']);
        if (!$mimeType) {
            Response::json(['error' => 'Formato no permitido. Solo JPG, PNG o WebP.'], 422);
        }
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];

        $ext      = $allowed[$mimeType];
        // Comprimir avatar: máx 400x400, calidad 85%
        $processedPath = $this->compressImage($file['tmp_name'], $mimeType, 400, 85);
        $mimeType      = 'image/jpeg';
        $ext           = 'jpg';
        $filename      = 'avatars/avatar_' . Auth::id() . '_' . time() . '.' . $ext;
        $current       = $db->fetch("SELECT avatar FROM users WHERE id=?", [Auth::id()]);

        if (\MercadoSordo\Core\R2Uploader::isEnabled()) {
            try {
                $r2 = new \MercadoSordo\Core\R2Uploader();
                // Eliminar avatar anterior de R2
                if (!empty($current['avatar']) && str_contains($current['avatar'], 'r2.dev')) {
                    $r2->delete(\MercadoSordo\Core\R2Uploader::filenameFromUrl($current['avatar']));
                }
                $avatarUrl = $r2->upload($processedPath, $filename, $mimeType);
            } catch (\Exception $e) {
                Response::json(['error' => 'Error al subir imagen: ' . $e->getMessage()], 500);
            }
        } else {
            // Fallback local
            $uploadDir = defined('BASE_PATH')
                ? BASE_PATH . '/public/uploads/avatars'
                : __DIR__ . '/../public/uploads/avatars';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $localFile = $uploadDir . '/avatar_' . Auth::id() . '_' . time() . '.' . $ext;
            if (!empty($current['avatar']) && !str_contains($current['avatar'], 'r2.dev')) {
                $old = defined('BASE_PATH') ? BASE_PATH . '/public' . $current['avatar'] : __DIR__ . '/../public' . $current['avatar'];
                if (file_exists($old)) @unlink($old);
            }
            if (!move_uploaded_file($file['tmp_name'], $localFile)) {
                Response::json(['error' => 'Error al guardar la imagen.'], 500);
            }
            $avatarUrl = '/uploads/avatars/avatar_' . Auth::id() . '_' . time() . '.' . $ext;
        }

        $db->update('users', ['avatar' => $avatarUrl], 'id=?', [Auth::id()]);
        Response::json(['avatar_url' => $avatarUrl, 'message' => 'Avatar actualizado.']);
    }

    public function deleteAvatar(Request $req): void
    {
        $db   = DB::getInstance();
        $user = $db->fetch("SELECT avatar FROM users WHERE id=?", [Auth::id()]);
        if (!empty($user['avatar'])) {
            if (str_contains($user['avatar'], 'r2.dev') && \MercadoSordo\Core\R2Uploader::isEnabled()) {
                (new \MercadoSordo\Core\R2Uploader())->delete(\MercadoSordo\Core\R2Uploader::filenameFromUrl($user['avatar']));
            } else {
                $file = defined('BASE_PATH') ? BASE_PATH . '/public' . $user['avatar'] : __DIR__ . '/../public' . $user['avatar'];
                if (file_exists($file)) @unlink($file);
            }
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
// ProductImageController — /api/products/{id}/images
// ============================================================
class ProductImageController
{
    use ImageUploadTrait;

    private function uploadDir(): string
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
        $dir  = $base . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir;
    }

    public function store(Request $req): void
    {
        $productId = (int)$req->param('id');
        $db        = DB::getInstance();

        // Verificar ownership
        $product = $db->fetch("SELECT seller_id FROM products WHERE id=?", [$productId]);
        if (!$product) Response::json(['error' => 'Producto no encontrado.'], 404);
        if ((int)$product['seller_id'] !== (int)Auth::id() && !Auth::is('admin')) {
            Response::json(['error' => 'Forbidden'], 403);
        }

        // Verificar límite 8 imágenes
        $count = $db->fetch("SELECT COUNT(*) AS c FROM product_images WHERE product_id=?", [$productId])['c'];
        if ($count >= 8) Response::json(['error' => 'Máximo 8 imágenes por producto.'], 422);

        if (empty($_FILES['image'])) Response::json(['error' => 'No se recibió imagen.'], 400);

        $file = $_FILES['image'];
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errMsg = [
                UPLOAD_ERR_INI_SIZE   => 'Imagen muy grande (límite del servidor).',
                UPLOAD_ERR_FORM_SIZE  => 'Imagen muy grande.',
                UPLOAD_ERR_PARTIAL    => 'La imagen no se subió completamente. Intenta de nuevo.',
                UPLOAD_ERR_NO_FILE    => 'No se recibió ninguna imagen.',
                UPLOAD_ERR_NO_TMP_DIR => 'Error de servidor (tmp).',
                UPLOAD_ERR_CANT_WRITE => 'Error al guardar imagen.',
            ];
            $msg = $errMsg[$file['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'Error al subir imagen. Intenta de nuevo.';
            Response::json(['error' => $msg], 400);
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            Response::json(['error' => 'La imagen no puede superar 5MB.'], 422);
        }
        $mimeType = $this->detectImageMime($file['tmp_name'], $file['name']);
        if (!$mimeType) {
            Response::json(['error' => 'Formato no permitido. Solo JPG, PNG o WebP.'], 422);
        }
        // Comprimir: máx 1200x1200px, calidad 82% JPEG — ahorra ~70% espacio
        $processedPath = $this->compressImage($file['tmp_name'], $mimeType, 1200, 82);
        $mimeType      = 'image/jpeg';
        $filename      = 'products/prod_' . $productId . '_' . uniqid() . '.jpg';

        if (\MercadoSordo\Core\R2Uploader::isEnabled()) {
            try {
                $r2  = new \MercadoSordo\Core\R2Uploader();
                $url = $r2->upload($processedPath, $filename, $mimeType);
            } catch (\Exception $e) {
                Response::json(['error' => 'Error al subir imagen: ' . $e->getMessage()], 500);
            }
        } else {
            $dest = $this->uploadDir() . DIRECTORY_SEPARATOR . basename($filename);
            if (!move_uploaded_file($processedPath, $dest)) {
                Response::json(['error' => 'Error al guardar la imagen.'], 500);
            }
            $url = '/uploads/products/' . basename($filename);
        }

        $isPrimary = (int)($req->input('is_primary', 0));
        $sortOrder = (int)($req->input('sort_order', $count));

        // Si es primaria, quitar primary a las demás
        if ($isPrimary) {
            $db->query("UPDATE product_images SET is_primary=0 WHERE product_id=?", [$productId]);
        }
        $id  = $db->insert('product_images', [
            'product_id' => $productId,
            'url'        => $url,
            'alt_text'   => $req->input('alt_text') ?: null,
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary ? 1 : 0,
        ]);

        Response::json(['id' => $id, 'url' => $url, 'message' => 'Imagen subida.'], 201);
    }

    public function updateOrder(Request $req): void
    {
        $productId = (int)$req->param('id');
        $db        = DB::getInstance();
        $product   = $db->fetch("SELECT seller_id FROM products WHERE id=?", [$productId]);
        if (!$product || ((int)$product['seller_id'] !== (int)Auth::id() && !Auth::is('admin'))) {
            Response::json(['error' => 'Forbidden'], 403);
        }
        $images = $req->input('images', []);
        foreach ($images as $img) {
            if (empty($img['id'])) continue;
            $db->update('product_images',
                ['sort_order' => (int)($img['sort_order'] ?? 0), 'is_primary' => (int)($img['is_primary'] ?? 0)],
                'id=? AND product_id=?', [(int)$img['id'], $productId]
            );
        }
        Response::json(['message' => 'Orden actualizado.']);
    }

    public function destroy(Request $req): void
    {
        $imageId = (int)$req->param('imageId');
        $db      = DB::getInstance();
        $img     = $db->fetch(
            "SELECT pi.*, p.seller_id FROM product_images pi
             JOIN products p ON p.id = pi.product_id
             WHERE pi.id=?", [$imageId]
        );
        if (!$img) Response::json(['error' => 'Imagen no encontrada.'], 404);
        if ((int)$img['seller_id'] !== (int)Auth::id() && !Auth::is('admin')) {
            Response::json(['error' => 'Forbidden'], 403);
        }

        // Eliminar archivo físico (R2 o local)
        if (str_contains($img['url'], 'r2.dev') && \MercadoSordo\Core\R2Uploader::isEnabled()) {
            (new \MercadoSordo\Core\R2Uploader())->delete(\MercadoSordo\Core\R2Uploader::filenameFromUrl($img['url']));
        } else {
            $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
            $file = $base . '/public' . $img['url'];
            if (file_exists($file)) @unlink($file);
        }

        $db->delete('product_images', 'id=?', [$imageId]);

        // Si era primaria, asignar la siguiente
        if ($img['is_primary']) {
            $next = $db->fetch("SELECT id FROM product_images WHERE product_id=? ORDER BY sort_order LIMIT 1", [$img['product_id']]);
            if ($next) $db->update('product_images', ['is_primary' => 1], 'id=?', [$next['id']]);
        }

        Response::json(['message' => 'Imagen eliminada.']);
    }
}

// ============================================================
// MercadoPagoController — OAuth + Preferencias + Webhook
// ============================================================
class MercadoPagoController
{
    private function cfg(): array
    {
        return [
            'app_id'       => env('MP_APP_ID', ''),
            'client_id'    => env('MP_CLIENT_ID', ''),
            'client_secret'=> env('MP_CLIENT_SECRET', ''),
            'commission'   => (float)env('MP_COMMISSION', 5.0),
            'base_url'     => 'https://api.mercadopago.com',
            'oauth_url'    => 'https://auth.mercadopago.com/authorization',
            'redirect_uri' => env('APP_URL','http://localhost:8080').'/api/vendor/mp/callback',
            'sandbox'      => env('MP_ENV','sandbox') === 'sandbox',
        ];
    }

    private function curl(string $method, string $url, array $data=[], array $headers=[]): array
    {
        $ch = curl_init();
        curl_setopt_array($ch,[
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/json','Accept: application/json'], $headers),
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        if (in_array($method,['POST','PUT','PATCH'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $res  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($err) throw new \RuntimeException("cURL: {$err}");
        return ['status'=>$code,'body'=>json_decode($res,true)??[],'raw'=>$res];
    }

    public function accountStatus(Request $req): void
    {
        $acc = DB::getInstance()->fetch(
            "SELECT mp_email, mp_public_key, is_active, connected_at FROM vendor_payment_accounts WHERE vendor_id=?",
            [Auth::id()]
        );
        Response::json(['connected'=>!empty($acc)&&$acc['is_active'],'account'=>$acc]);
    }

    public function authorize(Request $req): void
    {
        $cfg   = $this->cfg();
        $state = bin2hex(random_bytes(16));
        DB::getInstance()->query(
            "INSERT INTO user_tokens(user_id,token,type,expires_at) VALUES(?,?,'mp_oauth',DATE_ADD(NOW(),INTERVAL 10 MINUTE)) ON DUPLICATE KEY UPDATE token=VALUES(token),expires_at=VALUES(expires_at)",
            [Auth::id(),$state]
        );
        $url = $cfg['oauth_url'].'?'.http_build_query([
            'response_type'=>'code','client_id'=>$cfg['client_id'],
            'redirect_uri'=>$cfg['redirect_uri'],'state'=>$state,
        ]);
        Response::json(['redirect_url'=>$url]);
    }

    public function oauthCallback(Request $req): void
    {
        $code = $req->input('code');
        if (!$code) Response::json(['error'=>'Código OAuth no recibido.'],400);
        $cfg = $this->cfg();
        $res = $this->curl('POST',$cfg['base_url'].'/oauth/token',[
            'client_id'=>$cfg['client_id'],'client_secret'=>$cfg['client_secret'],
            'code'=>$code,'redirect_uri'=>$cfg['redirect_uri'],'grant_type'=>'authorization_code',
        ]);
        if ($res['status']!==200||empty($res['body']['access_token']))
            Response::json(['error'=>'Error obteniendo token MP.','detail'=>$res['body']],400);
        $token = $res['body'];
        $db    = DB::getInstance();
        $data  = [
            'vendor_id'=>Auth::id(),'mp_user_id'=>$token['user_id']??'',
            'mp_access_token'=>$token['access_token'],
            'mp_refresh_token'=>$token['refresh_token']??null,
            'mp_public_key'=>$token['public_key']??null,
            'token_expires_at'=>isset($token['expires_in'])?date('Y-m-d H:i:s',time()+(int)$token['expires_in']):null,
            'is_active'=>1,
        ];
        $existing = $db->fetch("SELECT id FROM vendor_payment_accounts WHERE vendor_id=?",[Auth::id()]);
        $existing ? $db->update('vendor_payment_accounts',$data,'vendor_id=?',[Auth::id()])
                  : $db->insert('vendor_payment_accounts',$data);
        Response::json(['message'=>'Mercado Pago conectado.']);
    }

    public function createPreference(Request $req): void
    {
        $orderId = (int)$req->input('order_id');
        $db      = DB::getInstance();
        $order   = $db->fetch("SELECT * FROM orders WHERE id=? AND buyer_id=?",[$orderId,Auth::id()]);
        if (!$order) Response::json(['error'=>'Orden no encontrada.'],404);
        if ($order['status']!=='pending') Response::json(['error'=>'Orden ya procesada.'],400);
        $items   = $db->fetchAll("SELECT oi.*,p.seller_id FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?",[$orderId]);
        $sellerId= $items[0]['seller_id'];
        $account = $db->fetch("SELECT * FROM vendor_payment_accounts WHERE vendor_id=? AND is_active=1",[$sellerId]);
        if (!$account) Response::json(['error'=>'El vendedor no tiene Mercado Pago configurado.'],400);
        $cfg    = $this->cfg();
        $total  = (float)$order['total'];
        $comm   = round($total*$cfg['commission']/100,2);
        $appUrl = env('APP_URL','http://localhost:8080');
        $buyer  = $db->fetch("SELECT name,email FROM users WHERE id=?",[Auth::id()]);
        $res    = $this->curl('POST',$cfg['base_url'].'/checkout/preferences',[
            'items'=>array_map(fn($i)=>['id'=>(string)$i['product_id'],'title'=>$i['title'],'quantity'=>(int)$i['quantity'],'unit_price'=>(float)$i['price'],'currency_id'=>'CLP'],$items),
            'payer'=>['name'=>$buyer['name'],'email'=>$buyer['email']],
            'marketplace_fee'=>$comm,
            'back_urls'=>['success'=>$appUrl.'/checkout/success?order_id='.$orderId,'failure'=>$appUrl.'/checkout/failure?order_id='.$orderId,'pending'=>$appUrl.'/checkout/pending?order_id='.$orderId],
            'auto_return'=>'approved',
            'notification_url'=>$appUrl.'/api/webhooks/mercadopago/ipn',
            'external_reference'=>$order['order_number'],
        ],['Authorization: Bearer '.$account['mp_access_token']]);
        if ($res['status']!==201) Response::json(['error'=>'Error creando preferencia MP.','detail'=>$res['body']],400);
        $prefId = $res['body']['id'];
        $db->update('orders',['mp_preference_id'=>$prefId],'id=?',[$orderId]);
        $db->insert('payments',['order_id'=>$orderId,'vendor_id'=>$sellerId,'payment_method'=>'mercadopago','mp_preference_id'=>$prefId,'amount'=>$total,'commission_pct'=>$cfg['commission'],'commission_amount'=>$comm,'vendor_amount'=>$total-$comm,'status'=>'pending','raw_response'=>json_encode($res['body'])]);
        $isSandbox = $cfg['sandbox'];
        Response::json(['preference_id'=>$prefId,'init_point'=>$isSandbox?($res['body']['sandbox_init_point']??$res['body']['init_point']):$res['body']['init_point'],'amount'=>$total,'commission'=>$comm]);
    }

    public function webhookIPN(Request $req): void
    {
        $data  = $req->all();
        $topic = $data['topic']??$data['type']??'';
        $resId = $data['id']??$data['data']['id']??null;
        if ($topic!=='payment'||!$resId){http_response_code(200);exit;}
        $db  = DB::getInstance();
        $pay = $db->fetch("SELECT p.*,vpa.mp_access_token FROM payments p JOIN vendor_payment_accounts vpa ON vpa.vendor_id=p.vendor_id WHERE p.mp_preference_id IS NOT NULL ORDER BY p.created_at DESC LIMIT 1");
        if (!$pay){http_response_code(200);exit;}
        $cfg = $this->cfg();
        $res = $this->curl('GET',$cfg['base_url'].'/v1/payments/'.$resId,[],['Authorization: Bearer '.$pay['mp_access_token']]);
        if ($res['status']!==200){http_response_code(200);exit;}
        $mp = $res['body'];
        $status = match($mp['status']??''){'approved'=>'approved','rejected'=>'rejected','cancelled'=>'cancelled',default=>'in_process'};
        $db->update('payments',['mp_payment_id'=>(string)$resId,'status'=>$status,'status_detail'=>$mp['status_detail']??null,'payer_email'=>$mp['payer']['email']??null,'raw_response'=>json_encode($mp)],'id=?',[$pay['id']]);
        if ($status==='approved'){
            $db->update('orders',['status'=>'paid','payment_id'=>(string)$resId],'id=?',[$pay['order_id']]);
            $db->insert('order_tracking',['order_id'=>$pay['order_id'],'status'=>'paid','description'=>'Pago MP confirmado. ID:'.$resId]);
        }
        http_response_code(200);echo json_encode(['status'=>'ok']);exit;
    }

    public function disconnect(Request $req): void
    {
        DB::getInstance()->update('vendor_payment_accounts',['is_active'=>0],'vendor_id=?',[Auth::id()]);
        Response::json(['message'=>'Cuenta MP desconectada.']);
    }
}

// ============================================================
// BankTransferController — Khipu + Cuenta bancaria vendedor
// ============================================================
class BankTransferController
{
    private function cfg(): array
    {
        return [
            'receiver_id' => env('KHIPU_RECEIVER_ID',''),
            'secret'      => env('KHIPU_SECRET',''),
            'base_url'    => 'https://khipu.com/api/2.0',
            'commission'  => (float)env('BANK_COMMISSION',5.0),
            'app_url'     => env('APP_URL','http://localhost:8080'),
        ];
    }

    private function khipuRequest(string $method, string $endpoint, array $data=[]): array
    {
        $cfg  = $this->cfg();
        $url  = $cfg['base_url'].$endpoint;
        $body = http_build_query($data);
        $hash = hash_hmac('sha256', $method."
".hash('sha256',$body)."
".parse_url($url,PHP_URL_PATH), $cfg['secret']);
        $auth = 'Basic '.base64_encode($cfg['receiver_id'].':'.$hash);
        $ch   = curl_init();
        curl_setopt_array($ch,[
            CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>30,
            CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded','Authorization: '.$auth],
        ]);
        if ($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,$body);}
        $res  = curl_exec($ch);
        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['status'=>$code,'body'=>json_decode($res,true)??[]];
    }

    public function connectBankAccount(Request $req): void
    {
        $data = $req->validate([
            'bank_name'      => 'required',
            'account_number' => 'required',
            'account_type'   => 'required',
            'account_name'   => 'required',
        ]);
        $db      = DB::getInstance();
        $user    = $db->fetch("SELECT rut FROM users WHERE id=?",[Auth::id()]);
        if (empty($user['rut'])) Response::json(['error'=>'Debes registrar tu RUT antes de conectar cuenta bancaria.'],400);
        $existing = $db->fetch("SELECT id FROM vendor_bank_accounts WHERE vendor_id=?",[Auth::id()]);
        $record   = [
            'vendor_id'     =>Auth::id(),
            'bank_name'     =>trim($data['bank_name']),
            'account_type'  =>$data['account_type'],
            'account_number'=>trim($data['account_number']),
            'account_rut'   =>$user['rut'],
            'account_name'  =>trim($data['account_name']),
            'account_email' =>$req->input('account_email'),
            'is_active'     =>1,
        ];
        $existing ? $db->update('vendor_bank_accounts',$record,'vendor_id=?',[Auth::id()])
                  : $db->insert('vendor_bank_accounts',$record);
        Response::json(['message'=>'Cuenta bancaria registrada correctamente.']);
    }

    public function bankAccountStatus(Request $req): void
    {
        $acc = DB::getInstance()->fetch(
            "SELECT * FROM vendor_bank_accounts WHERE vendor_id=?",
            [Auth::id()]
        );
        Response::json(['connected'=>!empty($acc)&&$acc['is_active'],'account'=>$acc]);
    }

    public function savePaymentMethods(Request $req): void
    {
        $data = $req->all();
        $db   = DB::getInstance();
        $user = $db->fetch("SELECT rut FROM users WHERE id=?", [Auth::id()]);

        $existing = $db->fetch("SELECT id FROM vendor_bank_accounts WHERE vendor_id=?", [Auth::id()]);
        $record = [
            'vendor_id'           => Auth::id(),
            'mp_enabled'          => !empty($data['mercadopago']['enabled']) ? 1 : 0,
            'mp_link'             => $data['mercadopago']['link'] ?? null,
            'is_active'           => 1,
            'account_rut'         => $user['rut'] ?? '',
            'bank_name'           => $data['transfer']['bank'] ?? ($existing['bank_name'] ?? ''),
            'account_type'        => $data['transfer']['account_type'] ?? ($existing['account_type'] ?? 'cuenta_corriente'),
            'account_number'      => $data['transfer']['account_number'] ?? ($existing['account_number'] ?? ''),
            'account_name'        => $data['transfer']['account_name'] ?? ($existing['account_name'] ?? ''),
            'is_active'           => !empty($data['transfer']['enabled']) ? 1 : ($existing['is_active'] ?? 0),
            'wallet_enabled'      => !empty($data['wallet']['enabled']) ? 1 : 0,
            'wallet_provider'     => $data['wallet']['provider'] ?? null,
            'wallet_account'      => $data['wallet']['account'] ?? null,
            'wallet_instructions' => $data['wallet']['instructions'] ?? null,
            'custom_enabled'      => !empty($data['custom']['enabled']) ? 1 : 0,
            'custom_text'         => $data['custom']['text'] ?? null,
            'tax_rate'            => isset($data['tax_rate']) ? (float)$data['tax_rate'] : 0.00,
        ];

        if ($existing) {
            $db->update('vendor_bank_accounts', $record, 'vendor_id=?', [Auth::id()]);
        } else {
            $db->insert('vendor_bank_accounts', $record);
        }
        Response::json(['message' => 'Métodos de pago guardados.']);
    }

    public function getVendorPaymentMethods(Request $req): void
    {
        $vendorId = (int)$req->param('vendor_id');
        $acc = DB::getInstance()->fetch(
            "SELECT mp_enabled, mp_link, is_active AS transfer_enabled,
                    bank_name, account_type, account_number, account_name,
                    wallet_enabled, wallet_provider, wallet_account, wallet_instructions,
                    custom_enabled, custom_text
             FROM vendor_bank_accounts WHERE vendor_id=?",
            [$vendorId]
        );
        if (!$acc) Response::json(['methods' => []]);
        $methods = [];
        if (!empty($acc['mp_enabled'])) {
            $methods[] = [
                'key'   => 'mercadopago',
                'label' => 'Mercado Pago',
                'desc'  => 'Tarjeta de crédito, débito, cuotas',
                'icon'  => 'bi-credit-card',
                'color' => '#009ee3',
                'link'  => $acc['mp_link'] ?: 'https://link.mercadopago.cl/mercadosordo',
                'recommended' => true,
            ];
        }
        if (!empty($acc['transfer_enabled']) && $acc['bank_name']) {
            $types = ['cuenta_corriente'=>'Cta. Corriente','cuenta_ahorro'=>'Cta. Ahorro','cuenta_vista'=>'Cta. Vista','cuenta_rut'=>'Cta. RUT'];
            $methods[] = [
                'key'    => 'bank_transfer',
                'label'  => 'Transferencia Bancaria',
                'desc'   => $acc['bank_name'] . ' · ' . ($types[$acc['account_type']] ?? ''),
                'icon'   => 'bi-bank2',
                'color'  => '#198754',
                'detail' => $acc['bank_name'] . ' · ' . ($types[$acc['account_type']] ?? '') . ' N° ' . $acc['account_number'] . ' · ' . $acc['account_name'],
            ];
        }
        if (!empty($acc['wallet_enabled']) && $acc['wallet_provider']) {
            $methods[] = [
                'key'    => 'wallet',
                'label'  => $acc['wallet_provider'],
                'desc'   => $acc['wallet_account'] ?? 'Billetera digital',
                'icon'   => 'bi-phone-fill',
                'color'  => '#6f42c1',
                'detail' => $acc['wallet_instructions'],
            ];
        }
        if (!empty($acc['custom_enabled']) && $acc['custom_text']) {
            $methods[] = [
                'key'    => 'custom',
                'label'  => 'Otro método',
                'desc'   => substr($acc['custom_text'], 0, 60) . (strlen($acc['custom_text']) > 60 ? '...' : ''),
                'icon'   => 'bi-chat-text-fill',
                'color'  => '#fd7e14',
                'detail' => $acc['custom_text'],
            ];
        }
        Response::json(['methods' => $methods]);
    }

    public function createPayment(Request $req): void
    {
        $orderId = (int)$req->input('order_id');
        $db      = DB::getInstance();
        $order   = $db->fetch("SELECT * FROM orders WHERE id=? AND buyer_id=?",[$orderId,Auth::id()]);
        if (!$order) Response::json(['error'=>'Orden no encontrada.'],404);
        if ($order['status']!=='pending') Response::json(['error'=>'Orden ya procesada.'],400);
        $items    = $db->fetchAll("SELECT oi.*,p.seller_id FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?",[$orderId]);
        $sellerId = $items[0]['seller_id'];
        $bankAcc  = $db->fetch("SELECT * FROM vendor_bank_accounts WHERE vendor_id=? AND is_active=1",[$sellerId]);
        if (!$bankAcc) Response::json(['error'=>'El vendedor no tiene cuenta bancaria configurada.'],400);
        $cfg   = $this->cfg();
        $total = (float)$order['total'];
        $comm  = round($total*$cfg['commission']/100,2);
        $buyer = $db->fetch("SELECT name,email FROM users WHERE id=?",[Auth::id()]);
        // Crear pago en Khipu
        $res = $this->khipuRequest('POST','/payments',[
            'subject'          =>'Pago MercadoSordo - '.$order['order_number'],
            'currency'         =>'CLP',
            'amount'           =>$total,
            'transaction_id'   =>$order['order_number'],
            'payer_name'       =>$buyer['name'],
            'payer_email'      =>$buyer['email'],
            'return_url'       =>$cfg['app_url'].'/checkout/success?order_id='.$orderId,
            'cancel_url'       =>$cfg['app_url'].'/checkout/failure?order_id='.$orderId,
            'notify_url'       =>$cfg['app_url'].'/api/webhooks/bank-transfer/confirm',
            'bank_id'          =>$bankAcc['bank_name'],
        ]);
        if ($res['status']!==201) Response::json(['error'=>'Error creando pago Khipu.','detail'=>$res['body']],400);
        $khipuId  = $res['body']['payment_id'];
        $payUrl   = $res['body']['payment_url'];
        $simUrl   = $res['body']['simplified_transfer_url']??$payUrl;
        $db->insert('payments',['order_id'=>$orderId,'vendor_id'=>$sellerId,'payment_method'=>'bank_transfer','khipu_payment_id'=>$khipuId,'khipu_payment_url'=>$payUrl,'amount'=>$total,'commission_pct'=>$cfg['commission'],'commission_amount'=>$comm,'vendor_amount'=>$total-$comm,'status'=>'pending','raw_response'=>json_encode($res['body'])]);
        Response::json(['payment_id'=>$khipuId,'payment_url'=>$simUrl,'amount'=>$total,'commission'=>$comm,'vendor_amount'=>$total-$comm]);
    }

    public function webhookConfirm(Request $req): void
    {
        $data     = $req->all();
        $khipuId  = $data['payment_id']??null;
        if (!$khipuId){http_response_code(200);exit;}
        $db  = DB::getInstance();
        $pay = $db->fetch("SELECT * FROM payments WHERE khipu_payment_id=?",[$khipuId]);
        if (!$pay){http_response_code(200);exit;}
        $db->update('payments',['status'=>'approved','raw_response'=>json_encode($data)],'id=?',[$pay['id']]);
        $db->update('orders',['status'=>'paid','payment_id'=>$khipuId],'id=?',[$pay['order_id']]);
        $db->insert('order_tracking',['order_id'=>$pay['order_id'],'status'=>'paid','description'=>'Transferencia bancaria confirmada via Khipu. ID:'.$khipuId]);
        http_response_code(200);echo json_encode(['status'=>'ok']);exit;
    }
}

// ============================================================
// OrderManagementController — Panel vendedor + protocolo
// ============================================================
class OrderManagementController
{
    private const IVA_RATE        = 0.19;
    private const COMMISSION_RATE = 0.05;
    private const AUTO_COMPLETE_DAYS = 7;

    // ── Calcular desglose financiero ───────────────────────
    private function calcFinancials(float $total, ?float $taxRate = null): array
    {
        $rate         = ($taxRate !== null) ? $taxRate / 100 : 0.0;
        $ivaAmount    = $rate > 0 ? round($total - ($total / (1 + $rate)), 2) : 0.0;
        $subtotalNeto = round($total - $ivaAmount, 2);
        $commission   = round($total * self::COMMISSION_RATE, 2);
        $vendorNet    = round($total - $commission, 2);
        return [
            'total'            => $total,
            'iva_amount'       => $ivaAmount,
            'subtotal_neto'    => $subtotalNeto,
            'commission_amount'=> $commission,
            'vendor_net'       => $vendorNet,
            'iva_pct'          => $rate * 100,
            'commission_pct'   => self::COMMISSION_RATE * 100,
        ];
    }

    // ── Crear notificación ─────────────────────────────────
    private function notify(DB $db, int $userId, string $type, string $title, string $body, string $icon = 'bi-bell', string $color = 'primary', ?string $entityType = null, ?int $entityId = null): void
    {
        $db->insert('notifications', [
            'user_id'     => $userId,
            'type'        => $type,
            'title'       => $title,
            'body'        => $body,
            'icon'        => $icon,
            'color'       => $color,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'action_url'  => $entityType === 'order' ? '/orders/' . $entityId : null,
        ]);
    }

    // ── VENDEDOR: mis pedidos recibidos ────────────────────
    public function vendorOrders(Request $req): void
    {
        $db     = DB::getInstance();
        $page   = (int)$req->input('page', 1);
        $status = $req->input('status', '');
        // Buscar órdenes donde el usuario es vendedor
        $uid      = Auth::id();
        $bindings = [$uid, $uid];
        $where    = "(o.seller_id = ? OR EXISTS (SELECT 1 FROM order_items oi2 WHERE oi2.order_id = o.id AND oi2.seller_id = ?))";
        if ($status) { $where .= " AND o.status = ?"; $bindings[] = $status; }

        $totalRow = $db->fetch(
            "SELECT COUNT(DISTINCT o.id) AS c FROM orders o WHERE {$where}",
            $bindings
        );
        $total = (int)($totalRow['c'] ?? 0);
        $offset = ($page - 1) * 20;

        $rows = $db->fetchAll(
            "SELECT DISTINCT o.*,
                u.name AS buyer_name, u.email AS buyer_email, u.rut AS buyer_rut,
                (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS items_count
             FROM orders o
             JOIN users u ON u.id = o.buyer_id
             WHERE {$where}
             ORDER BY o.created_at DESC
             LIMIT 20 OFFSET {$offset}",
            $bindings
        );

        $result = [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => 20,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / 20),
        ];
        $vendor  = $db->fetch("SELECT tax_rate FROM vendor_bank_accounts WHERE vendor_id=?", [Auth::id()]);
        $taxRate = (float)($vendor['tax_rate'] ?? 0);
        foreach ($result['data'] as &$order) {
            $fin = $this->calcFinancials((float)$order['total'], $taxRate);
            $order['financials'] = $fin;
            $order['items'] = $db->fetchAll(
                "SELECT oi.*,
                  (SELECT url FROM product_images WHERE product_id=oi.product_id AND is_primary=1 LIMIT 1) AS image
                 FROM order_items oi
                 WHERE oi.order_id=? AND oi.seller_id=?",
                [$order['id'], Auth::id()]
            );
        }
        Response::json($result);
    }

    // ── VENDEDOR: detalle de una orden ────────────────────
    public function vendorOrderDetail(Request $req): void
    {
        $id = (int)$req->param('id');
        $db = DB::getInstance();
        $order = $db->fetch(
            "SELECT o.*, u.name AS buyer_name, u.email AS buyer_email,
                    u.rut AS buyer_rut, u.phone AS buyer_phone
             FROM orders o JOIN users u ON u.id=o.buyer_id
             WHERE o.id=? AND (o.seller_id=? OR EXISTS(
                 SELECT 1 FROM order_items oi WHERE oi.order_id=o.id AND oi.seller_id=?
             ))",
            [$id, Auth::id(), Auth::id()]
        );
        if (!$order) Response::json(['error' => 'Orden no encontrada.'], 404);
        $order['items']         = $db->fetchAll("SELECT oi.*, (SELECT url FROM product_images WHERE product_id=oi.product_id AND is_primary=1 LIMIT 1) AS image FROM order_items oi WHERE oi.order_id=? AND oi.seller_id=?", [$id, Auth::id()]);
        $order['tracking']      = $db->fetchAll("SELECT * FROM order_tracking WHERE order_id=? ORDER BY created_at ASC", [$id]);
        $order['confirmations'] = $db->fetchAll("SELECT oc.*, u.name AS confirmed_by_name FROM order_confirmations oc LEFT JOIN users u ON u.id=oc.confirmed_by WHERE oc.order_id=? ORDER BY oc.confirmed_at ASC", [$id]);
        $order['messages']      = $db->fetchAll("SELECT om.*, u.name AS sender_name, u.avatar AS sender_avatar FROM order_messages om JOIN users u ON u.id=om.sender_id WHERE om.order_id=? ORDER BY om.created_at ASC", [$id]);
        $vdAcct = $db->fetch("SELECT tax_rate FROM vendor_bank_accounts WHERE vendor_id=?", [$order['seller_id'] ?? Auth::id()]);
        $order['financials'] = $this->calcFinancials((float)$order['total'], (float)($vdAcct['tax_rate'] ?? 0));
        $order['dispute']       = $db->fetch("SELECT * FROM order_disputes WHERE order_id=?", [$id]);
        Response::json($order);
    }

    // ── PROTOCOLO PASO 1: Vendedor acepta orden ────────────
    public function vendorAccept(Request $req): void
    {
        $id = (int)$req->param('id');
        $db = DB::getInstance();
        $order = $db->fetch("SELECT * FROM orders WHERE id=? AND (seller_id=? OR EXISTS(SELECT 1 FROM order_items oi WHERE oi.order_id=orders.id AND oi.seller_id=?))", [$id, Auth::id(), Auth::id()]);
        if (!$order) Response::json(['error' => 'Orden no encontrada.'], 404);
        if (!in_array($order['status'], ['paid','pending'])) Response::json(['error' => 'Solo puedes aceptar órdenes en estado pagado o pendiente.'], 400);
        $db->beginTransaction();
        try {
            $db->update('orders', ['status' => 'processing', 'vendor_accepted_at' => date('Y-m-d H:i:s')], 'id=?', [$id]);
            $db->insert('order_tracking', ['order_id' => $id, 'status' => 'processing', 'description' => 'Vendedor aceptó la orden. Preparando envío.']);
            $db->insert('order_confirmations', ['order_id' => $id, 'step' => 'vendor_accept', 'confirmed_by' => Auth::id(), 'notes' => $req->input('notes')]);
            $this->notify($db, (int)$order['buyer_id'], 'order_accepted', '✅ Vendedor aceptó tu orden', 'Tu orden ' . $order['order_number'] . ' está siendo preparada.', 'bi-bag-check', 'success', 'order', $id);
            $db->commit();
            // Emails orden aceptada
            if (\MercadoSordo\Core\Mailer::isEnabled()) {
                try {
                    $mailer  = new \MercadoSordo\Core\Mailer();
                    $buyerU  = $db->fetch("SELECT name, email FROM users WHERE id=?", [(int)$order['buyer_id']]);
                    $vendorU = $db->fetch("SELECT u.name, u.email, COALESCE(vba.tax_rate,0) AS tax_rate FROM users u LEFT JOIN vendor_bank_accounts vba ON vba.vendor_id=u.id WHERE u.id=?", [Auth::id()]);
                    $fin2    = $this->calcFinancials((float)$order['total'], (float)$vendorU['tax_rate']);
                    $mailer->send($buyerU['email'], $buyerU['name'], '📦 Tu pedido fue aceptado', \MercadoSordo\Core\Mailer::orderAccepted($order));
                    $mailer->send($vendorU['email'], $vendorU['name'], '💰 Pago recibido — ' . $order['order_number'], \MercadoSordo\Core\Mailer::paymentReceived($order, $fin2));
                } catch (\Throwable $me) { error_log('[Mail] accept: ' . $me->getMessage()); }
            }
            Response::json(['message' => 'Orden aceptada. El comprador fue notificado.']);
        } catch (\Throwable $e) { $db->rollback(); Response::json(['error' => 'Error al aceptar orden.'], 500); }
    }

    // ── PROTOCOLO PASO 2: Vendedor despacha ───────────────
    public function vendorDispatch(Request $req): void
    {
        $id = (int)$req->param('id');
        $db = DB::getInstance();
        $order = $db->fetch("SELECT * FROM orders WHERE id=? AND (seller_id=? OR EXISTS(SELECT 1 FROM order_items oi WHERE oi.order_id=orders.id AND oi.seller_id=?))", [$id, Auth::id(), Auth::id()]);
        if (!$order) Response::json(['error' => 'Orden no encontrada.'], 404);
        if ($order['status'] !== 'processing') Response::json(['error' => 'Debes aceptar la orden primero.'], 400);
        $tracking   = $req->input('tracking_number');
        $carrier    = $req->input('carrier', 'Correos de Chile');
        $autoComplete = date('Y-m-d H:i:s', time() + self::AUTO_COMPLETE_DAYS * 86400);
        $db->beginTransaction();
        try {
            $db->update('orders', [
                'status'          => 'dispatched',
                'tracking_number' => $tracking,
                'tracking_carrier'=> $carrier,
                'dispatched_at'   => date('Y-m-d H:i:s'),
                'auto_complete_at'=> $autoComplete,
            ], 'id=?', [$id]);
            $db->insert('order_tracking', ['order_id' => $id, 'status' => 'dispatched', 'description' => "Despachado por {$carrier}" . ($tracking ? " · Nº seguimiento: {$tracking}" : '')]);
            $db->insert('order_confirmations', ['order_id' => $id, 'step' => 'vendor_dispatch', 'confirmed_by' => Auth::id(), 'metadata' => json_encode(['tracking_number' => $tracking, 'carrier' => $carrier])]);
            $this->notify($db, (int)$order['buyer_id'], 'order_dispatched', '🚚 Tu pedido fue despachado', "Orden {$order['order_number']} en camino. Carrier: {$carrier}" . ($tracking ? " · Tracking: {$tracking}" : ''), 'bi-truck', 'info', 'order', $id);
            $db->commit();
            // Email despachada → comprador
            if (\MercadoSordo\Core\Mailer::isEnabled()) {
                try {
                    $orderUp = $db->fetch("SELECT * FROM orders WHERE id=?", [$id]);
                    $buyerU  = $db->fetch("SELECT name, email FROM users WHERE id=?", [(int)$order['buyer_id']]);
                    (new \MercadoSordo\Core\Mailer())->send($buyerU['email'], $buyerU['name'], '🚚 Tu pedido está en camino', \MercadoSordo\Core\Mailer::orderDispatched($orderUp));
                } catch (\Throwable $me) { error_log('[Mail] dispatch: ' . $me->getMessage()); }
            }
            Response::json(['message' => 'Despacho confirmado. Comprador notificado.', 'auto_complete_at' => $autoComplete]);
        } catch (\Throwable $e) { $db->rollback(); Response::json(['error' => 'Error al confirmar despacho.'], 500); }
    }

    // ── PROTOCOLO PASO 3: Comprador confirma recepción ────
    public function buyerConfirm(Request $req): void
    {
        $id = (int)$req->param('id');
        $db = DB::getInstance();
        $order = $db->fetch("SELECT * FROM orders WHERE id=? AND buyer_id=?", [$id, Auth::id()]);
        if (!$order) Response::json(['error' => 'Orden no encontrada.'], 404);
        if (!in_array($order['status'], ['dispatched', 'in_transit'])) Response::json(['error' => 'La orden aún no fue despachada.'], 400);
        $db->beginTransaction();
        try {
            $db->update('orders', ['status' => 'completed', 'delivered_at' => date('Y-m-d H:i:s'), 'completed_at' => date('Y-m-d H:i:s')], 'id=?', [$id]);
            $db->insert('order_tracking', ['order_id' => $id, 'status' => 'completed', 'description' => 'Comprador confirmó recepción. Fondos liberados al vendedor.']);
            $db->insert('order_confirmations', ['order_id' => $id, 'step' => 'buyer_confirm', 'confirmed_by' => Auth::id(), 'notes' => $req->input('notes')]);
            $fin = $this->calcFinancials((float)$order['total']);
            $this->notify($db, (int)$order['seller_id'], 'order_completed', '💰 Fondos liberados', "Orden {$order['order_number']} completada. Recibirás \$" . number_format($fin['vendor_net'], 0, ',', '.') . " CLP.", 'bi-cash-coin', 'success', 'order', $id);
            $db->commit();
            // Email completada → vendedor
            if (\MercadoSordo\Core\Mailer::isEnabled()) {
                try {
                    $sId     = $order['seller_id'] ?? $db->fetch("SELECT seller_id FROM order_items WHERE order_id=? LIMIT 1", [$id])['seller_id'];
                    $vendorU = $db->fetch("SELECT u.name, u.email, COALESCE(vba.tax_rate,0) AS tax_rate FROM users u LEFT JOIN vendor_bank_accounts vba ON vba.vendor_id=u.id WHERE u.id=?", [(int)$sId]);
                    $fin2    = $this->calcFinancials((float)$order['total'], (float)$vendorU['tax_rate']);
                    (new \MercadoSordo\Core\Mailer())->send($vendorU['email'], $vendorU['name'], '🎉 Venta completada', \MercadoSordo\Core\Mailer::orderCompleted($order, $fin2));
                } catch (\Throwable $me) { error_log('[Mail] confirm: ' . $me->getMessage()); }
            }
            Response::json(['message' => 'Recepción confirmada. Fondos liberados al vendedor.']);
        } catch (\Throwable $e) { $db->rollback(); Response::json(['error' => 'Error al confirmar.'], 500); }
    }

    // ── PROTOCOLO: Cancelar orden ─────────────────────────
    public function cancelOrder(Request $req): void
    {
        $id     = (int)$req->param('id');
        $db     = DB::getInstance();
        $userId = Auth::id();
        $order  = $db->fetch(
            "SELECT * FROM orders WHERE id=? AND (
                buyer_id=? OR seller_id=? OR
                EXISTS(SELECT 1 FROM order_items oi WHERE oi.order_id=orders.id AND oi.seller_id=?)
            )", [$id, $userId, $userId, $userId]
        );
        if (!$order) Response::json(['error' => 'Orden no encontrada.'], 404);
        if (in_array($order['status'], ['completed','refunded','cancelled'])) Response::json(['error' => 'No se puede cancelar esta orden.'], 400);
        $db->beginTransaction();
        try {
            // Restaurar stock
            $items = $db->fetchAll("SELECT * FROM order_items WHERE order_id=?", [$id]);
            foreach ($items as $item) {
                $db->query("UPDATE products SET stock=stock+?, sales_count=GREATEST(0,sales_count-?) WHERE id=?", [$item['quantity'], $item['quantity'], $item['product_id']]);
            }
            $db->update('orders', ['status' => 'cancelled'], 'id=?', [$id]);
            $db->insert('order_tracking', ['order_id' => $id, 'status' => 'cancelled', 'description' => 'Orden cancelada. ' . ($req->input('reason') ?? '')]);
            // Notificar al otro participante
            $notifyUserId = ($userId == $order['buyer_id']) ? $order['seller_id'] : $order['buyer_id'];
            $this->notify($db, (int)$notifyUserId, 'order_cancelled', '❌ Orden cancelada', "La orden {$order['order_number']} fue cancelada.", 'bi-x-circle', 'danger', 'order', $id);
            $db->commit();
            Response::json(['message' => 'Orden cancelada.']);
        } catch (\Throwable $e) { $db->rollback(); Response::json(['error' => 'Error al cancelar.'], 500); }
    }

    // ── Abrir disputa ─────────────────────────────────────
    public function openDispute(Request $req): void
    {
        $id   = (int)$req->param('id');
        $db   = DB::getInstance();
        $order = $db->fetch("SELECT * FROM orders WHERE id=? AND buyer_id=?", [$id, Auth::id()]);
        if (!$order) Response::json(['error' => 'Orden no encontrada.'], 404);
        if (!in_array($order['status'], ['dispatched','in_transit','delivered'])) Response::json(['error' => 'No puedes abrir disputa en esta etapa.'], 400);
        $existing = $db->fetch("SELECT id FROM order_disputes WHERE order_id=?", [$id]);
        if ($existing) Response::json(['error' => 'Ya existe una disputa para esta orden.'], 409);
        $data = $req->validate(['reason' => 'required', 'description' => 'required|min:20']);
        $db->beginTransaction();
        try {
            $db->insert('order_disputes', ['order_id' => $id, 'opened_by' => Auth::id(), 'reason' => $data['reason'], 'description' => $data['description']]);
            $db->update('orders', ['status' => 'dispute'], 'id=?', [$id]);
            $db->insert('order_tracking', ['order_id' => $id, 'status' => 'dispute', 'description' => 'Disputa abierta: ' . $data['reason']]);
            $this->notify($db, (int)$order['seller_id'], 'dispute_opened', '⚠️ Disputa abierta', "El comprador abrió una disputa en la orden {$order['order_number']}.", 'bi-shield-exclamation', 'warning', 'order', $id);
            $db->commit();
            Response::json(['message' => 'Disputa abierta. El equipo revisará el caso.'], 201);
        } catch (\Throwable $e) { $db->rollback(); Response::json(['error' => 'Error al abrir disputa.'], 500); }
    }

    // ── Notificaciones ────────────────────────────────────
    public function getNotifications(Request $req): void
    {
        $db      = DB::getInstance();
        $page    = (int)$req->input('page', 1);
        $unread  = $req->input('unread');
        $perPage = 20;

        // Obtener user_id directo desde token — evita problema con Auth::$user estático
        $token   = $req->bearerToken() ?? ($_COOKIE['ms_token'] ?? null);
        $tokenRow = $token ? $db->fetch(
            "SELECT u.id FROM users u JOIN user_tokens t ON t.user_id=u.id
             WHERE t.token=? AND t.type='auth' AND t.expires_at>NOW() AND u.status='active'",
            [$token]
        ) : null;
        $userId = $tokenRow['id'] ?? Auth::id();

        $where = "WHERE user_id=?";
        $b     = [$userId];
        if ($unread) { $where .= " AND read_at IS NULL"; }

        $total        = (int)$db->fetch("SELECT COUNT(*) AS c FROM notifications {$where}", $b)['c'];
        $offset       = ($page - 1) * $perPage;
        $rows         = $db->fetchAll("SELECT * FROM notifications {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}", $b);
        $unread_count = (int)$db->fetch("SELECT COUNT(*) AS c FROM notifications WHERE user_id=? AND read_at IS NULL", [$userId])['c'];

        Response::json([
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
            'unread_count' => $unread_count,
        ]);
    }

    public function markNotificationRead(Request $req): void
    {
        $id = (int)$req->param('id');
        DB::getInstance()->update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'id=? AND user_id=?', [$id, Auth::id()]);
        Response::json(['message' => 'Marcada como leída.']);
    }

    public function markAllRead(Request $req): void
    {
        DB::getInstance()->query("UPDATE notifications SET read_at=NOW() WHERE user_id=? AND read_at IS NULL", [Auth::id()]);
        Response::json(['message' => 'Todas marcadas como leídas.']);
    }

    // ── Chat por orden ────────────────────────────────────
    public function getMessages(Request $req): void
    {
        $id    = (int)$req->param('id');
        $db    = DB::getInstance();
        $order = $db->fetch(
            "SELECT * FROM orders WHERE id=? AND (
                buyer_id=? OR seller_id=? OR
                EXISTS(SELECT 1 FROM order_items oi WHERE oi.order_id=orders.id AND oi.seller_id=?)
            )", [$id, Auth::id(), Auth::id(), Auth::id()]
        );
        if (!$order) Response::json(['error' => 'No autorizado.'], 403);
        $msgs = $db->fetchAll(
            "SELECT om.*, u.name AS sender_name, u.avatar AS sender_avatar
             FROM order_messages om JOIN users u ON u.id=om.sender_id
             WHERE om.order_id=? ORDER BY om.created_at ASC", [$id]
        );
        $db->query("UPDATE order_messages SET read_at=NOW() WHERE order_id=? AND sender_id != ? AND read_at IS NULL", [$id, Auth::id()]);
        Response::json(['messages' => $msgs, 'order_number' => $order['order_number']]);
    }

    public function sendMessage(Request $req): void
    {
        $id   = (int)$req->param('id');
        $db   = DB::getInstance();
        $data = $req->validate(['message' => 'required|min:1']);
        $order = $db->fetch(
            "SELECT * FROM orders WHERE id=? AND (
                buyer_id=? OR seller_id=? OR
                EXISTS(SELECT 1 FROM order_items oi WHERE oi.order_id=orders.id AND oi.seller_id=?)
            )", [$id, Auth::id(), Auth::id(), Auth::id()]
        );
        if (!$order) Response::json(['error' => 'No autorizado.'], 403);
        $msgId = $db->insert('order_messages', ['order_id' => $id, 'sender_id' => Auth::id(), 'message' => trim($data['message']), 'type' => 'text']);
        // Notificar al otro
        $receiver = (Auth::id() == $order['buyer_id']) ? $order['seller_id'] : $order['buyer_id'];
        $sender   = $db->fetch("SELECT name FROM users WHERE id=?", [Auth::id()]);
        $this->notify($db, (int)$receiver, 'new_message', '💬 Nuevo mensaje', $sender['name'] . ': ' . substr(trim($data['message']), 0, 80), 'bi-chat-dots', 'info', 'order', $id);
        $msg = $db->fetch("SELECT om.*, u.name AS sender_name, u.avatar AS sender_avatar FROM order_messages om JOIN users u ON u.id=om.sender_id WHERE om.id=?", [$msgId]);
        // Email nuevo mensaje → destinatario
        if (\MercadoSordo\Core\Mailer::isEnabled()) {
            try {
                $recipientU  = $db->fetch("SELECT name, email FROM users WHERE id=?", [(int)$receiver]);
                $senderU     = $db->fetch("SELECT name FROM users WHERE id=?", [Auth::id()]);
                (new \MercadoSordo\Core\Mailer())->send($recipientU['email'], $recipientU['name'], '💬 Nuevo mensaje de ' . $senderU['name'], \MercadoSordo\Core\Mailer::newChatMessage($order, $senderU['name'], trim($data['message'])));
            } catch (\Throwable $me) { error_log('[Mail] chat: ' . $me->getMessage()); }
        }
        Response::json(['message' => $msg], 201);
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

