<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

class WishlistController
{
    private DatabaseService $db;
    private LoggerInterface $logger;

    public function __construct(DatabaseService $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get user's wishlist
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $request->getAttribute('user_id');
            
            // Get or create wishlist
            $wishlist = $this->getOrCreateWishlist($userId);

            // Get wishlist items with product details
            $items = $this->db->fetchAll("
                SELECT 
                    wi.*,
                    p.id as product_id,
                    p.name as product_name,
                    p.slug as product_slug,
                    p.price,
                    p.rating_avg,
                    p.rating_count,
                    p.season,
                    p.gender_target,
                    p.brand,
                    c.name as category_name,
                    c.slug as category_slug,
                    pi.url as product_image
                FROM wishlist_items wi
                JOIN products p ON wi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE wi.wishlist_id = ? AND p.is_active = 1
                ORDER BY wi.created_at DESC
            ", [$wishlist['id']]);

            // Format items
            foreach ($items as &$item) {
                $item['price'] = (float) $item['price'];
                $item['rating_avg'] = (float) $item['rating_avg'];
                $item['rating_count'] = (int) $item['rating_count'];
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'wishlist_id' => $wishlist['id'],
                    'items' => $items,
                    'total_items' => count($items)
                ]
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get wishlist failed', ['error' => $e->getMessage(), 'user_id' => $request->getAttribute('user_id')]);
            return $this->errorResponse($response, 'Failed to get wishlist', [], 500);
        }
    }

    /**
     * Add product to wishlist
     */
    public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $userId = $request->getAttribute('user_id');

            // Validate input
            $this->validateAddToWishlistData($data);

            $productId = (int) $data['product_id'];

            // Verify product exists and is active
            $product = $this->db->fetchOne(
                'SELECT id, name FROM products WHERE id = ? AND is_active = 1',
                [$productId]
            );

            if (!$product) {
                return $this->errorResponse($response, 'Product not found or inactive', [], 404);
            }

            // Get or create wishlist
            $wishlist = $this->getOrCreateWishlist($userId);

            // Check if product is already in wishlist
            $existingItem = $this->db->fetchOne(
                'SELECT id FROM wishlist_items WHERE wishlist_id = ? AND product_id = ?',
                [$wishlist['id'], $productId]
            );

            if ($existingItem) {
                return $this->errorResponse($response, 'Product already in wishlist', [], 409);
            }

            // Add to wishlist
            $this->db->insert(
                'INSERT INTO wishlist_items (wishlist_id, product_id) VALUES (?, ?)',
                [$wishlist['id'], $productId]
            );

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Product added to wishlist',
                'data' => [
                    'product_id' => $productId,
                    'product_name' => $product['name']
                ]
            ]));

            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');

        } catch (ValidationException $e) {
            return $this->errorResponse($response, 'Validation failed', $e->getMessages(), 422);
        } catch (\Exception $e) {
            $this->logger->error('Add to wishlist failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->getAttribute('user_id'),
                'product_id' => $data['product_id'] ?? 'unknown'
            ]);
            return $this->errorResponse($response, 'Failed to add product to wishlist', [], 500);
        }
    }

    /**
     * Remove product from wishlist
     */
    public function remove(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $productId = (int) $args['productId'];
            $userId = $request->getAttribute('user_id');

            // Get user's wishlist
            $wishlist = $this->db->fetchOne(
                'SELECT id FROM wishlists WHERE user_id = ?',
                [$userId]
            );

            if (!$wishlist) {
                return $this->errorResponse($response, 'Wishlist not found', [], 404);
            }

            // Remove item from wishlist
            $deleted = $this->db->execute(
                'DELETE FROM wishlist_items WHERE wishlist_id = ? AND product_id = ?',
                [$wishlist['id'], $productId]
            );

            if (!$deleted) {
                return $this->errorResponse($response, 'Product not found in wishlist', [], 404);
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Product removed from wishlist'
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Remove from wishlist failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->getAttribute('user_id'),
                'product_id' => $args['productId'] ?? 'unknown'
            ]);
            return $this->errorResponse($response, 'Failed to remove product from wishlist', [], 500);
        }
    }

    /**
     * Check if product is in user's wishlist
     */
    public function check(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $productId = (int) $args['productId'];
            $userId = $request->getAttribute('user_id');

            // Get user's wishlist
            $wishlist = $this->db->fetchOne(
                'SELECT id FROM wishlists WHERE user_id = ?',
                [$userId]
            );

            $inWishlist = false;
            if ($wishlist) {
                $item = $this->db->fetchOne(
                    'SELECT id FROM wishlist_items WHERE wishlist_id = ? AND product_id = ?',
                    [$wishlist['id'], $productId]
                );
                $inWishlist = (bool) $item;
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'product_id' => $productId,
                    'in_wishlist' => $inWishlist
                ]
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Check wishlist failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->getAttribute('user_id'),
                'product_id' => $args['productId'] ?? 'unknown'
            ]);
            return $this->errorResponse($response, 'Failed to check wishlist', [], 500);
        }
    }

    /**
     * Get wishlist statistics
     */
    public function stats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $request->getAttribute('user_id');

            // Get user's wishlist
            $wishlist = $this->db->fetchOne(
                'SELECT id FROM wishlists WHERE user_id = ?',
                [$userId]
            );

            if (!$wishlist) {
                $stats = [
                    'total_items' => 0,
                    'total_value' => 0,
                    'categories' => [],
                    'price_range' => ['min' => 0, 'max' => 0]
                ];
            } else {
                // Get statistics
                $totalStats = $this->db->fetchOne("
                    SELECT 
                        COUNT(*) as total_items,
                        COALESCE(SUM(p.price), 0) as total_value,
                        COALESCE(MIN(p.price), 0) as min_price,
                        COALESCE(MAX(p.price), 0) as max_price
                    FROM wishlist_items wi
                    JOIN products p ON wi.product_id = p.id
                    WHERE wi.wishlist_id = ? AND p.is_active = 1
                ", [$wishlist['id']]);

                $categoryStats = $this->db->fetchAll("
                    SELECT 
                        c.name as category_name,
                        c.slug as category_slug,
                        COUNT(*) as item_count,
                        SUM(p.price) as category_value
                    FROM wishlist_items wi
                    JOIN products p ON wi.product_id = p.id
                    JOIN categories c ON p.category_id = c.id
                    WHERE wi.wishlist_id = ? AND p.is_active = 1
                    GROUP BY c.id, c.name, c.slug
                    ORDER BY item_count DESC
                ", [$wishlist['id']]);

                $stats = [
                    'total_items' => (int) $totalStats['total_items'],
                    'total_value' => (float) $totalStats['total_value'],
                    'categories' => $categoryStats,
                    'price_range' => [
                        'min' => (float) $totalStats['min_price'],
                        'max' => (float) $totalStats['max_price']
                    ]
                ];
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $stats
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get wishlist stats failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->getAttribute('user_id')
            ]);
            return $this->errorResponse($response, 'Failed to get wishlist statistics', [], 500);
        }
    }

    /**
     * Get or create wishlist for user
     */
    private function getOrCreateWishlist(int $userId): array
    {
        $wishlist = $this->db->fetchOne('SELECT * FROM wishlists WHERE user_id = ?', [$userId]);
        
        if (!$wishlist) {
            $wishlistId = $this->db->insert('INSERT INTO wishlists (user_id) VALUES (?)', [$userId]);
            $wishlist = $this->db->fetchOne('SELECT * FROM wishlists WHERE id = ?', [$wishlistId]);
        }

        return $wishlist;
    }

    /**
     * Validate add to wishlist data
     */
    private function validateAddToWishlistData(array $data): void
    {
        $validator = v::key('product_id', v::intType()->positive());
        $validator->assert($data);
    }

    /**
     * Create error response
     */
    private function errorResponse(ResponseInterface $response, string $message, array $errors = [], int $status = 400): ResponseInterface
    {
        $body = [
            'success' => false,
            'message' => $message,
            'status' => $status
        ];

        if (!empty($errors)) {
            $body['errors'] = $errors;
        }

        $response->getBody()->write(json_encode($body));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
