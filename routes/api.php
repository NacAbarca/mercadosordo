<?php
declare(strict_types=1);

use MercadoSordo\Core\Router;
use MercadoSordo\Core\AuthMiddleware;
use MercadoSordo\Core\AdminMiddleware;
use MercadoSordo\Core\RateLimitMiddleware;
use MercadoSordo\Core\Response;

$router = new Router();

$router->group(['prefix' => '/api'], function (Router $r) {

    // ── Auth ─────────────────────────────────────────────────────────────
    $r->group(['prefix' => '/auth', 'middleware' => [RateLimitMiddleware::class]], function (Router $r) {
        $r->post('/register',        'MercadoSordo\Controllers\AuthController@register');
        $r->post('/login',           'MercadoSordo\Controllers\AuthController@login');
        $r->post('/forgot-password', 'MercadoSordo\Controllers\AuthController@forgotPassword');
        $r->post('/reset-password',  'MercadoSordo\Controllers\AuthController@resetPassword');
        $r->get('/me',               'MercadoSordo\Controllers\AuthController@me',    [AuthMiddleware::class]);
        $r->post('/logout',          'MercadoSordo\Controllers\AuthController@logout', [AuthMiddleware::class]);
    });

    // ── Productos — público ───────────────────────────────────────────────
    $r->get('/products',           'MercadoSordo\Controllers\ProductController@index');
    $r->get('/products/{slug}',    'MercadoSordo\Controllers\ProductController@show');
    $r->get('/categories',         'MercadoSordo\Controllers\AdminController@categories');

    // ── Carrito — guest + auth ────────────────────────────────────────────
    $r->get('/cart',               'MercadoSordo\Controllers\CartController@index');
    $r->post('/cart/items',        'MercadoSordo\Controllers\CartController@addItem');
    $r->patch('/cart/items/{id}',  'MercadoSordo\Controllers\CartController@updateItem');
    $r->delete('/cart/items/{id}', 'MercadoSordo\Controllers\CartController@removeItem');
    $r->delete('/cart',            'MercadoSordo\Controllers\CartController@clear');

    // ── Webhooks — públicos (sin auth) ────────────────────────────────────
    $r->post('/webhooks/mercadopago/ipn',      'MercadoSordo\Controllers\MercadoPagoController@webhookIPN');
    $r->post('/webhooks/bank-transfer/confirm','MercadoSordo\Controllers\BankTransferController@webhookConfirm');

    // ── Rutas autenticadas ────────────────────────────────────────────────
    $r->group(['middleware' => [AuthMiddleware::class]], function (Router $r) {

        // Productos (vendedor)
        $r->get('/my/products',              'MercadoSordo\Controllers\ProductController@myProducts');
        $r->post('/products',                'MercadoSordo\Controllers\ProductController@store');
        $r->put('/products/{id}',            'MercadoSordo\Controllers\ProductController@update');
        $r->delete('/products/{id}',         'MercadoSordo\Controllers\ProductController@destroy');

        // Imágenes de productos
        $r->post('/products/{id}/images',        'MercadoSordo\Controllers\ProductImageController@store');
        $r->patch('/products/{id}/images/order', 'MercadoSordo\Controllers\ProductImageController@updateOrder');
        $r->delete('/products/images/{imageId}', 'MercadoSordo\Controllers\ProductImageController@destroy');

        // Órdenes
        $r->get('/orders',                   'MercadoSordo\Controllers\OrderController@index');
        $r->get('/orders/{id}',              'MercadoSordo\Controllers\OrderController@show');
        $r->post('/orders/checkout',         'MercadoSordo\Controllers\OrderController@checkout');

        // Pagos — Mercado Pago
        $r->get('/vendor/mp/status',             'MercadoSordo\Controllers\MercadoPagoController@accountStatus');
        $r->get('/vendor/mp/authorize',          'MercadoSordo\Controllers\MercadoPagoController@authorize');
        $r->get('/vendor/mp/callback',           'MercadoSordo\Controllers\MercadoPagoController@oauthCallback');
        $r->post('/vendor/mp/disconnect',        'MercadoSordo\Controllers\MercadoPagoController@disconnect');
        $r->post('/payments/mercadopago/create', 'MercadoSordo\Controllers\MercadoPagoController@createPreference');

        // Pagos — Transferencia Bancaria
        $r->get('/vendor/bank/status',            'MercadoSordo\Controllers\BankTransferController@bankAccountStatus');
        $r->post('/vendor/bank-account/connect',  'MercadoSordo\Controllers\BankTransferController@connectBankAccount');
        $r->post('/payments/bank-transfer/create','MercadoSordo\Controllers\BankTransferController@createPayment');

        // Reseñas
        $r->post('/reviews',                 'MercadoSordo\Controllers\ReviewController@store');

        // Perfil
        $r->get('/profile',                          'MercadoSordo\Controllers\ProfileController@show');
        $r->patch('/profile',                        'MercadoSordo\Controllers\ProfileController@update');
        $r->post('/profile/password',                'MercadoSordo\Controllers\ProfileController@changePassword');
        $r->delete('/profile',                       'MercadoSordo\Controllers\ProfileController@deleteAccount');
        $r->get('/profile/addresses',                'MercadoSordo\Controllers\ProfileController@getAddresses');
        $r->post('/profile/addresses',               'MercadoSordo\Controllers\ProfileController@storeAddress');
        $r->put('/profile/addresses/{id}',           'MercadoSordo\Controllers\ProfileController@updateAddress');
        $r->patch('/profile/addresses/{id}/default', 'MercadoSordo\Controllers\ProfileController@setDefault');
        $r->delete('/profile/addresses/{id}',        'MercadoSordo\Controllers\ProfileController@deleteAddress');
        $r->post('/profile/avatar',                  'MercadoSordo\Controllers\ProfileController@uploadAvatar');
        $r->delete('/profile/avatar',                'MercadoSordo\Controllers\ProfileController@deleteAvatar');
    });

    // ── Admin ─────────────────────────────────────────────────────────────
    $r->group(['prefix' => '/admin', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (Router $r) {
        $r->get('/dashboard',            'MercadoSordo\Controllers\AdminController@dashboard');
        $r->get('/users',                'MercadoSordo\Controllers\AdminController@users');
        $r->patch('/users/{id}',         'MercadoSordo\Controllers\AdminController@updateUser');
        $r->get('/products',             'MercadoSordo\Controllers\AdminController@products');
        $r->get('/orders',               'MercadoSordo\Controllers\AdminController@orders');
        $r->patch('/orders/{id}/status', 'MercadoSordo\Controllers\AdminController@updateOrderStatus');
        $r->get('/audit-log',            'MercadoSordo\Controllers\AdminController@auditLog');
    });
});

// ── SPA fallback ──────────────────────────────────────────────────────────
$webView = fn() => Response::view('app');
$router->get('/',                $webView);
$router->get('/login',           $webView);
$router->get('/register',        $webView);
$router->get('/products/{slug}', $webView);
$router->get('/cart',            $webView);
$router->get('/checkout',        $webView);
$router->get('/admin/{any}',     $webView);
$router->get('/profile/{any}',   $webView);

return $router;
