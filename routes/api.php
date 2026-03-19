<?php
declare(strict_types=1);
use MercadoSordo\Core\{Router, Request};
use MercadoSordo\Controllers\{
    AuthController, ProductController, CartController,
    OrderController, AdminController, ReviewController
};
use MercadoSordo\Core\{AuthMiddleware, AdminMiddleware, RateLimitMiddleware};

$router = new Router();

// ============================================================
// PUBLIC API
// ============================================================
$router->group(['prefix' => '/api'], function(Router $r) {

    // Auth
    $r->group(['prefix' => '/auth', 'middleware' => [RateLimitMiddleware::class]], function(Router $r) {
        $r->post('/register',        'AuthController@register');
        $r->post('/login',           'AuthController@login');
        $r->post('/forgot-password', 'AuthController@forgotPassword');
        $r->post('/reset-password',  'AuthController@resetPassword');
        $r->get('/me',               'AuthController@me',    [AuthMiddleware::class]);
        $r->post('/logout',          'AuthController@logout', [AuthMiddleware::class]);
    });

    // Products — public read
    $r->get('/products',            'ProductController@index');
    $r->get('/products/{slug}',     'ProductController@show');
    $r->get('/categories',          'AdminController@categories');

    // Cart — guest + auth
    $r->get('/cart',                'CartController@index');
    $r->post('/cart/items',         'CartController@addItem');
    $r->patch('/cart/items/{id}',   'CartController@updateItem');
    $r->delete('/cart/items/{id}',  'CartController@removeItem');
    $r->delete('/cart',             'CartController@clear');

    // Authenticated
    $r->group(['middleware' => [AuthMiddleware::class]], function(Router $r) {

        // My products (seller)
        $r->get('/my/products',             'ProductController@myProducts');
        $r->post('/products',               'ProductController@store');
        $r->put('/products/{id}',           'ProductController@update');
        $r->delete('/products/{id}',        'ProductController@destroy');

        // Orders
        $r->get('/orders',                  'OrderController@index');
        $r->get('/orders/{id}',             'OrderController@show');
        $r->post('/orders/checkout',        'OrderController@checkout');

        // Reviews
        $r->post('/reviews',                'ReviewController@store');
    });

    // Admin (auth + admin role)
    $r->group(['prefix' => '/admin', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function(Router $r) {
        $r->get('/dashboard',               'AdminController@dashboard');
        $r->get('/users',                   'AdminController@users');
        $r->patch('/users/{id}',            'AdminController@updateUser');
        $r->get('/products',                'AdminController@products');
        $r->get('/orders',                  'AdminController@orders');
        $r->patch('/orders/{id}/status',    'AdminController@updateOrderStatus');
        $r->get('/audit-log',               'AdminController@auditLog');
    });
});

// ============================================================
// WEB ROUTES (SPA fallback — Vue.js handles routing)
// ============================================================
$router->get('/', fn() => \MercadoSordo\Core\Response::view('app'));
$router->get('/login', fn() => \MercadoSordo\Core\Response::view('app'));
$router->get('/register', fn() => \MercadoSordo\Core\Response::view('app'));
$router->get('/products/{slug}', fn() => \MercadoSordo\Core\Response::view('app'));
$router->get('/cart', fn() => \MercadoSordo\Core\Response::view('app'));
$router->get('/checkout', fn() => \MercadoSordo\Core\Response::view('app'));
$router->get('/admin/{any}', fn() => \MercadoSordo\Core\Response::view('app'));
$router->get('/profile/{any}', fn() => \MercadoSordo\Core\Response::view('app'));

return $router;
