<?php
declare(strict_types=1);

use MercadoSordo\Core\Router;
use MercadoSordo\Core\AuthMiddleware;
use MercadoSordo\Core\AdminMiddleware;
use MercadoSordo\Core\RateLimitMiddleware;
use MercadoSordo\Core\Response;

$router = new Router();

$router->group(['prefix' => '/api'], function (Router $r) {

    $r->group(['prefix' => '/auth', 'middleware' => [RateLimitMiddleware::class]], function (Router $r) {
        $r->post('/register',        'MercadoSordo\Controllers\AuthController@register');
        $r->post('/login',           'MercadoSordo\Controllers\AuthController@login');
        $r->post('/forgot-password', 'MercadoSordo\Controllers\AuthController@forgotPassword');
        $r->post('/reset-password',  'MercadoSordo\Controllers\AuthController@resetPassword');
        $r->get('/me',               'MercadoSordo\Controllers\AuthController@me',    [AuthMiddleware::class]);
        $r->post('/logout',          'MercadoSordo\Controllers\AuthController@logout', [AuthMiddleware::class]);
    });

    $r->get('/products',           'MercadoSordo\Controllers\ProductController@index');
    $r->get('/products/{slug}',    'MercadoSordo\Controllers\ProductController@show');
    $r->get('/categories',         'MercadoSordo\Controllers\AdminController@categories');

    $r->get('/cart',               'MercadoSordo\Controllers\CartController@index');
    $r->post('/cart/items',        'MercadoSordo\Controllers\CartController@addItem');
    $r->patch('/cart/items/{id}',  'MercadoSordo\Controllers\CartController@updateItem');
    $r->delete('/cart/items/{id}', 'MercadoSordo\Controllers\CartController@removeItem');
    $r->delete('/cart',            'MercadoSordo\Controllers\CartController@clear');

    $r->group(['middleware' => [AuthMiddleware::class]], function (Router $r) {
        $r->get('/my/products',         'MercadoSordo\Controllers\ProductController@myProducts');
        $r->post('/products',           'MercadoSordo\Controllers\ProductController@store');
        $r->put('/products/{id}',       'MercadoSordo\Controllers\ProductController@update');
        $r->delete('/products/{id}',    'MercadoSordo\Controllers\ProductController@destroy');
        $r->get('/orders',              'MercadoSordo\Controllers\OrderController@index');
        $r->get('/orders/{id}',         'MercadoSordo\Controllers\OrderController@show');
        $r->post('/orders/checkout',    'MercadoSordo\Controllers\OrderController@checkout');
        $r->post('/reviews',            'MercadoSordo\Controllers\ReviewController@store');
    });

    $r->group(['prefix' => '/admin', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (Router $r) {
        $r->get('/dashboard',              'MercadoSordo\Controllers\AdminController@dashboard');
        $r->get('/users',                  'MercadoSordo\Controllers\AdminController@users');
        $r->patch('/users/{id}',           'MercadoSordo\Controllers\AdminController@updateUser');
        $r->get('/products',               'MercadoSordo\Controllers\AdminController@products');
        $r->get('/orders',                 'MercadoSordo\Controllers\AdminController@orders');
        $r->patch('/orders/{id}/status',   'MercadoSordo\Controllers\AdminController@updateOrderStatus');
        $r->get('/audit-log',              'MercadoSordo\Controllers\AdminController@auditLog');
    });
});

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
