<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\WishlistController;
use App\Controllers\OrderController;
use App\Controllers\RecommendationController;
use App\Middleware\JwtMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // API prefix group
    $app->group('/api', function (RouteCollectorProxy $group) {
        
        // Health check
        $group->get('/health', function ($request, $response) {
            $response->getBody()->write(json_encode([
                'status' => 'ok',
                'timestamp' => date('c'),
                'version' => '1.0.0'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });

        // Auth routes (public)
        $group->group('/auth', function (RouteCollectorProxy $auth) {
            $auth->post('/register', [AuthController::class, 'register']);
            $auth->post('/login', [AuthController::class, 'login']);
            $auth->post('/logout', [AuthController::class, 'logout']);
            $auth->get('/me', [AuthController::class, 'me'])->add(JwtMiddleware::class);
            $auth->post('/refresh', [AuthController::class, 'refresh']);
        });

        // Categories routes (public)
        $group->group('/categories', function (RouteCollectorProxy $categories) {
            $categories->get('', [CategoryController::class, 'index']);
            $categories->get('/{slug}', [CategoryController::class, 'show']);
        });

        // Products routes (public)
        $group->group('/products', function (RouteCollectorProxy $products) {
            $products->get('', [ProductController::class, 'index']);
            $products->get('/{slug}', [ProductController::class, 'show']);
            $products->get('/{id}/related', [ProductController::class, 'related']);
            $products->get('/search', [ProductController::class, 'search']);
        });

        // Cart routes (guest + authenticated)
        $group->group('/cart', function (RouteCollectorProxy $cart) {
            $cart->get('', [CartController::class, 'index']);
            $cart->post('', [CartController::class, 'add']);
            $cart->patch('/{itemId}', [CartController::class, 'update']);
            $cart->delete('/{itemId}', [CartController::class, 'remove']);
            $cart->delete('', [CartController::class, 'clear']);
        });

        // Wishlist routes (authenticated)
        $group->group('/wishlist', function (RouteCollectorProxy $wishlist) {
            $wishlist->get('', [WishlistController::class, 'index']);
            $wishlist->post('', [WishlistController::class, 'add']);
            $wishlist->delete('/{productId}', [WishlistController::class, 'remove']);
        })->add(JwtMiddleware::class);

        // Orders routes (authenticated)
        $group->group('/orders', function (RouteCollectorProxy $orders) {
            $orders->get('', [OrderController::class, 'index']);
            $orders->get('/{id}', [OrderController::class, 'show']);
            $orders->post('/checkout', [OrderController::class, 'checkout']);
        })->add(JwtMiddleware::class);

        // AI Recommendations routes
        $group->group('/ai', function (RouteCollectorProxy $ai) {
            $ai->post('/recommend', [RecommendationController::class, 'recommend']);
            $ai->get('/explain/{productId}', [RecommendationController::class, 'explain']);
        });

        // Admin routes (future implementation)
        $group->group('/admin', function (RouteCollectorProxy $admin) {
            // Admin routes will be added here
        })->add(JwtMiddleware::class);
    });

    // Catch-all route for 404s
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'error' => 'Not Found',
            'message' => 'The requested resource was not found.',
            'status' => 404
        ]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    });
};
