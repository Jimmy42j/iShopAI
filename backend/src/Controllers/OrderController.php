<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

class OrderController
{
    private DatabaseService $db;
    private LoggerInterface $logger;

    public function __construct(DatabaseService $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get user's orders
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $request->getAttribute('user_id');
            $queryParams = $request->getQueryParams();
            
            $page = max(1, (int) ($queryParams['page'] ?? 1));
            $limit = min(50, max(1, (int) ($queryParams['limit'] ?? 10)));
            $status = $queryParams['status'] ?? null;

            // Build conditions
            $conditions = ['user_id = ?'];
            $params = [$userId];

            if ($status) {
                $conditions[] = 'status = ?';
                $params[] = $status;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $conditions);

            // Get total count
            $totalResult = $this->db->fetchOne("SELECT COUNT(*) as total FROM orders {$whereClause}", $params);
            $total = (int) $totalResult['total'];

            // Get orders
            $offset = ($page - 1) * $limit;
            $orders = $this->db->fetchAll("
                SELECT 
                    o.*,
                    a.line1, a.line2, a.city, a.state, a.postal_code, a.country
                FROM orders o
                LEFT JOIN addresses a ON o.shipping_address_id = a.id
                {$whereClause}
                ORDER BY o.created_at DESC
                LIMIT {$limit} OFFSET {$offset}
            ", $params);

            // Get order items for each order
            $orderIds = array_column($orders, 'id');
            $orderItems = [];
            
            if (!empty($orderIds)) {
                $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
                $items = $this->db->fetchAll("
                    SELECT 
                        oi.*,
                        p.name as product_name,
                        p.slug as product_slug,
                        v.color,
                        v.size,
                        pi.url as product_image
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    LEFT JOIN variants v ON oi.variant_id = v.id
                    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                    WHERE oi.order_id IN ({$placeholders})
                    ORDER BY oi.id
                ", $orderIds);

                foreach ($items as $item) {
                    $orderItems[$item['order_id']][] = $item;
                }
            }

            // Format orders
            foreach ($orders as &$order) {
                $order['total'] = (float) $order['total'];
                $order['items'] = $orderItems[$order['id']] ?? [];
                $order['item_count'] = count($order['items']);
                
                // Format items
                foreach ($order['items'] as &$item) {
                    $item['qty'] = (int) $item['qty'];
                    $item['unit_price'] = (float) $item['unit_price'];
                    $item['total'] = $item['qty'] * $item['unit_price'];
                }
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'orders' => $orders,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get orders failed', ['error' => $e->getMessage(), 'user_id' => $request->getAttribute('user_id')]);
            return $this->errorResponse($response, 'Failed to get orders', [], 500);
        }
    }

    /**
     * Get single order details
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $orderId = (int) $args['id'];
            $userId = $request->getAttribute('user_id');

            // Get order
            $order = $this->db->fetchOne("
                SELECT 
                    o.*,
                    a.line1, a.line2, a.city, a.state, a.postal_code, a.country
                FROM orders o
                LEFT JOIN addresses a ON o.shipping_address_id = a.id
                WHERE o.id = ? AND o.user_id = ?
            ", [$orderId, $userId]);

            if (!$order) {
                return $this->errorResponse($response, 'Order not found', [], 404);
            }

            // Get order items
            $items = $this->db->fetchAll("
                SELECT 
                    oi.*,
                    p.name as product_name,
                    p.slug as product_slug,
                    p.description as product_description,
                    v.color,
                    v.size,
                    pi.url as product_image
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN variants v ON oi.variant_id = v.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE oi.order_id = ?
                ORDER BY oi.id
            ", [$orderId]);

            // Format order
            $order['total'] = (float) $order['total'];
            $order['items'] = $items;
            $order['item_count'] = count($items);

            // Format items
            foreach ($order['items'] as &$item) {
                $item['qty'] = (int) $item['qty'];
                $item['unit_price'] = (float) $item['unit_price'];
                $item['total'] = $item['qty'] * $item['unit_price'];
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $order
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get order failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->getAttribute('user_id'),
                'order_id' => $args['id'] ?? 'unknown'
            ]);
            return $this->errorResponse($response, 'Failed to get order', [], 500);
        }
    }

    /**
     * Checkout - create order from cart
     */
    public function checkout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $userId = $request->getAttribute('user_id');

            // Validate input
            $this->validateCheckoutData($data);

            $cartId = (int) $data['cart_id'];
            $addressId = (int) $data['address_id'];
            $paymentToken = $data['payment_token'] ?? null; // Mock payment token

            // Verify cart belongs to user
            $cart = $this->db->fetchOne('SELECT * FROM carts WHERE id = ? AND user_id = ?', [$cartId, $userId]);
            if (!$cart) {
                return $this->errorResponse($response, 'Cart not found', [], 404);
            }

            // Verify address belongs to user
            $address = $this->db->fetchOne('SELECT * FROM addresses WHERE id = ? AND user_id = ?', [$addressId, $userId]);
            if (!$address) {
                return $this->errorResponse($response, 'Address not found', [], 404);
            }

            // Get cart items
            $cartItems = $this->db->fetchAll("
                SELECT 
                    ci.*,
                    p.name as product_name,
                    p.price as current_price,
                    v.stock,
                    v.extra_price
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN variants v ON ci.variant_id = v.id
                WHERE ci.cart_id = ?
            ", [$cartId]);

            if (empty($cartItems)) {
                return $this->errorResponse($response, 'Cart is empty', [], 400);
            }

            // Validate stock and calculate total
            $total = 0;
            foreach ($cartItems as $item) {
                if ($item['variant_id'] && $item['stock'] < $item['qty']) {
                    return $this->errorResponse($response, "Insufficient stock for {$item['product_name']}", [], 400);
                }
                
                $itemPrice = $item['price_at_add'];
                $total += $itemPrice * $item['qty'];
            }

            // Start transaction
            $this->db->beginTransaction();

            try {
                // Create order
                $orderId = $this->db->insert("
                    INSERT INTO orders (user_id, total, status, shipping_address_id, payment_method, payment_token, notes)
                    VALUES (?, ?, 'pending', ?, 'credit_card', ?, ?)
                ", [$userId, $total, $addressId, $paymentToken, $data['notes'] ?? null]);

                // Create order items and update stock
                foreach ($cartItems as $item) {
                    // Add order item
                    $this->db->insert("
                        INSERT INTO order_items (order_id, product_id, variant_id, qty, unit_price)
                        VALUES (?, ?, ?, ?, ?)
                    ", [$orderId, $item['product_id'], $item['variant_id'], $item['qty'], $item['price_at_add']]);

                    // Update variant stock if applicable
                    if ($item['variant_id']) {
                        $this->db->execute("
                            UPDATE variants 
                            SET stock = stock - ? 
                            WHERE id = ? AND stock >= ?
                        ", [$item['qty'], $item['variant_id'], $item['qty']]);
                    }
                }

                // Mock payment processing
                $paymentSuccess = $this->processPayment($paymentToken, $total);
                
                if ($paymentSuccess) {
                    // Update order status
                    $this->db->execute("UPDATE orders SET status = 'paid' WHERE id = ?", [$orderId]);
                    
                    // Clear cart
                    $this->db->execute("DELETE FROM cart_items WHERE cart_id = ?", [$cartId]);
                } else {
                    // Payment failed
                    $this->db->rollback();
                    return $this->errorResponse($response, 'Payment processing failed', [], 402);
                }

                $this->db->commit();

                // Get created order
                $order = $this->db->fetchOne("
                    SELECT o.*, a.line1, a.line2, a.city, a.state, a.postal_code, a.country
                    FROM orders o
                    LEFT JOIN addresses a ON o.shipping_address_id = a.id
                    WHERE o.id = ?
                ", [$orderId]);

                $response->getBody()->write(json_encode([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => [
                        'order_id' => $orderId,
                        'status' => $order['status'],
                        'total' => (float) $order['total']
                    ]
                ]));

                return $response->withStatus(201)->withHeader('Content-Type', 'application/json');

            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->errorResponse($response, 'Validation failed', $e->getMessages(), 422);
        } catch (\Exception $e) {
            $this->logger->error('Checkout failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->getAttribute('user_id')
            ]);
            return $this->errorResponse($response, 'Checkout failed', [], 500);
        }
    }

    /**
     * Mock payment processing
     */
    private function processPayment(?string $paymentToken, float $amount): bool
    {
        // This is a mock implementation
        // In a real application, you would integrate with a payment processor like Stripe, PayPal, etc.
        
        if (!$paymentToken) {
            return false;
        }

        // Simulate payment processing delay
        usleep(500000); // 0.5 seconds

        // Mock success rate (95% success for testing)
        return rand(1, 100) <= 95;
    }

    /**
     * Validate checkout data
     */
    private function validateCheckoutData(array $data): void
    {
        $validator = v::key('cart_id', v::intType()->positive())
                      ->key('address_id', v::intType()->positive())
                      ->key('payment_token', v::optional(v::stringType()))
                      ->key('notes', v::optional(v::stringType()));

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
