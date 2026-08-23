<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

class CartController
{
    private DatabaseService $db;
    private LoggerInterface $logger;

    public function __construct(DatabaseService $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get cart contents
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $request->getAttribute('user_id');
            $queryParams = $request->getQueryParams();
            $cartId = $queryParams['cart_id'] ?? null;

            $cart = $this->getOrCreateCart($userId, $cartId);
            $cartData = $this->getCartWithItems($cart['id']);

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $cartData
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get cart failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to get cart', [], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $userId = $request->getAttribute('user_id');

            // Validate input
            $this->validateAddToCartData($data);

            $productId = (int) $data['product_id'];
            $variantId = isset($data['variant_id']) ? (int) $data['variant_id'] : null;
            $qty = (int) ($data['qty'] ?? 1);
            $cartId = $data['cart_id'] ?? null;

            // Verify product exists and is active
            $product = $this->db->fetchOne(
                'SELECT id, name, price FROM products WHERE id = ? AND is_active = 1',
                [$productId]
            );

            if (!$product) {
                return $this->errorResponse($response, 'Product not found or inactive', [], 404);
            }

            // Verify variant if provided
            $variant = null;
            if ($variantId) {
                $variant = $this->db->fetchOne(
                    'SELECT id, stock, extra_price FROM variants WHERE id = ? AND product_id = ? AND is_active = 1',
                    [$variantId, $productId]
                );

                if (!$variant) {
                    return $this->errorResponse($response, 'Product variant not found', [], 404);
                }

                if ($variant['stock'] < $qty) {
                    return $this->errorResponse($response, 'Insufficient stock', [], 400);
                }
            }

            // Get or create cart
            $cart = $this->getOrCreateCart($userId, $cartId);

            // Calculate price
            $price = (float) $product['price'];
            if ($variant) {
                $price += (float) $variant['extra_price'];
            }

            // Check if item already exists in cart
            $existingItem = $this->db->fetchOne(
                'SELECT id, qty FROM cart_items WHERE cart_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))',
                [$cart['id'], $productId, $variantId, $variantId]
            );

            if ($existingItem) {
                // Update existing item
                $newQty = $existingItem['qty'] + $qty;
                $this->db->execute(
                    'UPDATE cart_items SET qty = ?, updated_at = NOW() WHERE id = ?',
                    [$newQty, $existingItem['id']]
                );
            } else {
                // Add new item
                $this->db->insert(
                    'INSERT INTO cart_items (cart_id, product_id, variant_id, qty, price_at_add) VALUES (?, ?, ?, ?, ?)',
                    [$cart['id'], $productId, $variantId, $qty, $price]
                );
            }

            // Update cart timestamp
            $this->db->execute('UPDATE carts SET updated_at = NOW() WHERE id = ?', [$cart['id']]);

            // Get updated cart
            $cartData = $this->getCartWithItems($cart['id']);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => $cartData
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (ValidationException $e) {
            return $this->errorResponse($response, 'Validation failed', $e->getMessages(), 422);
        } catch (\Exception $e) {
            $this->logger->error('Add to cart failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to add item to cart', [], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $itemId = (int) $args['itemId'];
            $data = $request->getParsedBody();
            $userId = $request->getAttribute('user_id');

            // Validate input
            $this->validateUpdateCartData($data);

            $qty = (int) $data['qty'];

            // Get cart item
            $item = $this->db->fetchOne("
                SELECT ci.*, c.user_id, c.session_id
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE ci.id = ?
            ", [$itemId]);

            if (!$item) {
                return $this->errorResponse($response, 'Cart item not found', [], 404);
            }

            // Verify cart ownership
            if (!$this->canAccessCart($item, $userId)) {
                return $this->errorResponse($response, 'Access denied', [], 403);
            }

            if ($qty <= 0) {
                // Remove item if quantity is 0 or negative
                $this->db->execute('DELETE FROM cart_items WHERE id = ?', [$itemId]);
            } else {
                // Update quantity
                $this->db->execute(
                    'UPDATE cart_items SET qty = ?, updated_at = NOW() WHERE id = ?',
                    [$qty, $itemId]
                );
            }

            // Update cart timestamp
            $this->db->execute('UPDATE carts SET updated_at = NOW() WHERE id = ?', [$item['cart_id']]);

            // Get updated cart
            $cartData = $this->getCartWithItems($item['cart_id']);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Cart updated',
                'data' => $cartData
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (ValidationException $e) {
            return $this->errorResponse($response, 'Validation failed', $e->getMessages(), 422);
        } catch (\Exception $e) {
            $this->logger->error('Update cart failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to update cart', [], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $itemId = (int) $args['itemId'];
            $userId = $request->getAttribute('user_id');

            // Get cart item
            $item = $this->db->fetchOne("
                SELECT ci.*, c.user_id, c.session_id
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE ci.id = ?
            ", [$itemId]);

            if (!$item) {
                return $this->errorResponse($response, 'Cart item not found', [], 404);
            }

            // Verify cart ownership
            if (!$this->canAccessCart($item, $userId)) {
                return $this->errorResponse($response, 'Access denied', [], 403);
            }

            // Remove item
            $this->db->execute('DELETE FROM cart_items WHERE id = ?', [$itemId]);

            // Update cart timestamp
            $this->db->execute('UPDATE carts SET updated_at = NOW() WHERE id = ?', [$item['cart_id']]);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Item removed from cart'
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Remove from cart failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to remove item from cart', [], 500);
        }
    }

    /**
     * Clear entire cart
     */
    public function clear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $request->getAttribute('user_id');
            $queryParams = $request->getQueryParams();
            $cartId = $queryParams['cart_id'] ?? null;

            $cart = $this->getOrCreateCart($userId, $cartId);

            // Clear all items
            $this->db->execute('DELETE FROM cart_items WHERE cart_id = ?', [$cart['id']]);

            // Update cart timestamp
            $this->db->execute('UPDATE carts SET updated_at = NOW() WHERE id = ?', [$cart['id']]);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Cart cleared'
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Clear cart failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to clear cart', [], 500);
        }
    }

    /**
     * Get or create cart for user/session
     */
    private function getOrCreateCart(?int $userId, ?string $sessionId): array
    {
        if ($userId) {
            // Logged in user
            $cart = $this->db->fetchOne('SELECT * FROM carts WHERE user_id = ?', [$userId]);
            
            if (!$cart) {
                $cartId = $this->db->insert('INSERT INTO carts (user_id) VALUES (?)', [$userId]);
                $cart = $this->db->fetchOne('SELECT * FROM carts WHERE id = ?', [$cartId]);
            }
        } else {
            // Guest user
            if ($sessionId) {
                $cart = $this->db->fetchOne('SELECT * FROM carts WHERE session_id = ?', [$sessionId]);
            }
            
            if (!isset($cart) || !$cart) {
                $sessionId = $sessionId ?: 'guest_' . bin2hex(random_bytes(16));
                $cartId = $this->db->insert('INSERT INTO carts (session_id) VALUES (?)', [$sessionId]);
                $cart = $this->db->fetchOne('SELECT * FROM carts WHERE id = ?', [$cartId]);
            }
        }

        return $cart;
    }

    /**
     * Get cart with items and calculate totals
     */
    private function getCartWithItems(int $cartId): array
    {
        $cart = $this->db->fetchOne('SELECT * FROM carts WHERE id = ?', [$cartId]);
        
        $items = $this->db->fetchAll("
            SELECT 
                ci.*,
                p.name as product_name,
                p.slug as product_slug,
                p.price as current_price,
                v.color,
                v.size,
                v.extra_price,
                pi.url as product_image
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN variants v ON ci.variant_id = v.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE ci.cart_id = ?
            ORDER BY ci.created_at ASC
        ", [$cartId]);

        $total = 0;
        $itemCount = 0;

        foreach ($items as &$item) {
            $item['qty'] = (int) $item['qty'];
            $item['price_at_add'] = (float) $item['price_at_add'];
            $item['current_price'] = (float) $item['current_price'];
            $item['extra_price'] = (float) ($item['extra_price'] ?? 0);
            
            $itemTotal = $item['price_at_add'] * $item['qty'];
            $item['total'] = $itemTotal;
            
            $total += $itemTotal;
            $itemCount += $item['qty'];
        }

        return [
            'cart_id' => $cart['id'],
            'session_id' => $cart['session_id'],
            'items' => $items,
            'total' => $total,
            'item_count' => $itemCount,
            'created_at' => $cart['created_at'],
            'updated_at' => $cart['updated_at']
        ];
    }

    /**
     * Check if user can access cart
     */
    private function canAccessCart(array $cartItem, ?int $userId): bool
    {
        if ($userId && $cartItem['user_id'] == $userId) {
            return true;
        }

        // For guest users, we'd need to check session ID
        // This is simplified - in production you'd want proper session management
        if (!$userId && $cartItem['session_id']) {
            return true;
        }

        return false;
    }

    /**
     * Validate add to cart data
     */
    private function validateAddToCartData(array $data): void
    {
        $validator = v::key('product_id', v::intType()->positive())
                      ->key('variant_id', v::optional(v::intType()->positive()))
                      ->key('qty', v::optional(v::intType()->positive()))
                      ->key('cart_id', v::optional(v::stringType()));

        $validator->assert($data);
    }

    /**
     * Validate update cart data
     */
    private function validateUpdateCartData(array $data): void
    {
        $validator = v::key('qty', v::intType()->min(0));
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
