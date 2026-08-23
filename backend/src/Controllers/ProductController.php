<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ProductController
{
    private DatabaseService $db;
    private LoggerInterface $logger;

    public function __construct(DatabaseService $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get products with filtering, sorting, and pagination
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $queryParams = $request->getQueryParams();
            
            // Extract parameters
            $page = max(1, (int) ($queryParams['page'] ?? 1));
            $limit = min(50, max(1, (int) ($queryParams['limit'] ?? 20)));
            $category = $queryParams['category'] ?? null;
            $season = $queryParams['season'] ?? null;
            $genderTarget = $queryParams['gender_target'] ?? null;
            $minPrice = $queryParams['min_price'] ?? null;
            $maxPrice = $queryParams['max_price'] ?? null;
            $sort = $queryParams['sort'] ?? 'newest';
            $search = $queryParams['q'] ?? null;

            // Build base query
            $baseQuery = "
                SELECT 
                    p.*,
                    c.name as category_name,
                    c.slug as category_slug,
                    pi.url as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            ";

            // Build conditions
            $conditions = ['p.is_active = 1'];
            $params = [];

            if ($category) {
                $conditions[] = 'c.slug = ?';
                $params[] = $category;
            }

            if ($season && $season !== 'all') {
                $conditions[] = '(p.season = ? OR p.season = "all")';
                $params[] = $season;
            }

            if ($genderTarget && $genderTarget !== 'unisex') {
                $conditions[] = '(p.gender_target = ? OR p.gender_target = "unisex")';
                $params[] = $genderTarget;
            }

            if ($minPrice !== null) {
                $conditions[] = 'p.price >= ?';
                $params[] = (float) $minPrice;
            }

            if ($maxPrice !== null) {
                $conditions[] = 'p.price <= ?';
                $params[] = (float) $maxPrice;
            }

            if ($search) {
                $conditions[] = '(MATCH(p.name, p.description, p.brand) AGAINST(? IN NATURAL LANGUAGE MODE) OR p.name LIKE ? OR p.brand LIKE ?)';
                $params[] = $search;
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            $whereClause = 'WHERE ' . implode(' AND ', $conditions);

            // Build order clause
            $orderClause = $this->buildOrderClause($sort);

            // Get total count
            $countQuery = "SELECT COUNT(DISTINCT p.id) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id {$whereClause}";
            $totalResult = $this->db->fetchOne($countQuery, $params);
            $total = (int) $totalResult['total'];

            // Build pagination
            $offset = ($page - 1) * $limit;
            $paginationClause = "LIMIT {$limit} OFFSET {$offset}";

            // Get products
            $query = "{$baseQuery} {$whereClause} {$orderClause} {$paginationClause}";
            $products = $this->db->fetchAll($query, $params);

            // Get additional data for each product
            $productIds = array_column($products, 'id');
            $variants = $this->getProductVariants($productIds);
            $images = $this->getProductImages($productIds);

            // Organize data
            $productsWithDetails = [];
            foreach ($products as $product) {
                $productId = $product['id'];
                $product['variants'] = $variants[$productId] ?? [];
                $product['images'] = $images[$productId] ?? [];
                $product['price'] = (float) $product['price'];
                $product['rating_avg'] = (float) $product['rating_avg'];
                $product['rating_count'] = (int) $product['rating_count'];
                $productsWithDetails[] = $product;
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'items' => $productsWithDetails,
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
            $this->logger->error('Get products failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to get products', [], 500);
        }
    }

    /**
     * Get single product by slug
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $slug = $args['slug'];

            $product = $this->db->fetchOne("
                SELECT 
                    p.*,
                    c.name as category_name,
                    c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = ? AND p.is_active = 1
            ", [$slug]);

            if (!$product) {
                return $this->errorResponse($response, 'Product not found', [], 404);
            }

            // Get product images
            $images = $this->db->fetchAll("
                SELECT url, alt_text, is_primary, sort_order
                FROM product_images
                WHERE product_id = ?
                ORDER BY is_primary DESC, sort_order ASC
            ", [$product['id']]);

            // Get product variants
            $variants = $this->db->fetchAll("
                SELECT id, sku, color, size, stock, extra_price, is_active
                FROM variants
                WHERE product_id = ? AND is_active = 1
                ORDER BY color, size
            ", [$product['id']]);

            $product['images'] = $images;
            $product['variants'] = $variants;
            $product['price'] = (float) $product['price'];
            $product['rating_avg'] = (float) $product['rating_avg'];
            $product['rating_count'] = (int) $product['rating_count'];

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $product
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get product failed', ['error' => $e->getMessage(), 'slug' => $args['slug'] ?? 'unknown']);
            return $this->errorResponse($response, 'Failed to get product', [], 500);
        }
    }

    /**
     * Get related products
     */
    public function related(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $productId = (int) $args['id'];
            $limit = min(12, max(1, (int) ($request->getQueryParams()['limit'] ?? 8)));

            // Get the current product to find related items
            $currentProduct = $this->db->fetchOne("
                SELECT category_id, gender_target, season, price
                FROM products
                WHERE id = ? AND is_active = 1
            ", [$productId]);

            if (!$currentProduct) {
                return $this->errorResponse($response, 'Product not found', [], 404);
            }

            // Find related products
            $relatedProducts = $this->db->fetchAll("
                SELECT 
                    p.*,
                    c.name as category_name,
                    c.slug as category_slug,
                    pi.url as primary_image,
                    (
                        CASE WHEN p.category_id = ? THEN 3 ELSE 0 END +
                        CASE WHEN p.gender_target = ? THEN 2 ELSE 0 END +
                        CASE WHEN p.season = ? OR p.season = 'all' THEN 1 ELSE 0 END +
                        CASE WHEN ABS(p.price - ?) < 50 THEN 1 ELSE 0 END
                    ) as relevance_score
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE p.id != ? AND p.is_active = 1
                ORDER BY relevance_score DESC, p.rating_avg DESC, p.rating_count DESC
                LIMIT ?
            ", [
                $currentProduct['category_id'],
                $currentProduct['gender_target'],
                $currentProduct['season'],
                $currentProduct['price'],
                $productId,
                $limit
            ]);

            // Format products
            foreach ($relatedProducts as &$product) {
                $product['price'] = (float) $product['price'];
                $product['rating_avg'] = (float) $product['rating_avg'];
                $product['rating_count'] = (int) $product['rating_count'];
                unset($product['relevance_score']);
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $relatedProducts
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get related products failed', ['error' => $e->getMessage(), 'product_id' => $args['id'] ?? 'unknown']);
            return $this->errorResponse($response, 'Failed to get related products', [], 500);
        }
    }

    /**
     * Search products
     */
    public function search(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = $queryParams['q'] ?? '';
            $limit = min(50, max(1, (int) ($queryParams['limit'] ?? 20)));

            if (empty($query)) {
                return $this->errorResponse($response, 'Search query is required', [], 400);
            }

            $products = $this->db->fetchAll("
                SELECT 
                    p.*,
                    c.name as category_name,
                    c.slug as category_slug,
                    pi.url as primary_image,
                    MATCH(p.name, p.description, p.brand) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE p.is_active = 1 AND (
                    MATCH(p.name, p.description, p.brand) AGAINST(? IN NATURAL LANGUAGE MODE) OR
                    p.name LIKE ? OR
                    p.brand LIKE ? OR
                    p.description LIKE ?
                )
                ORDER BY relevance DESC, p.rating_avg DESC
                LIMIT ?
            ", [$query, $query, "%{$query}%", "%{$query}%", "%{$query}%", $limit]);

            // Format products
            foreach ($products as &$product) {
                $product['price'] = (float) $product['price'];
                $product['rating_avg'] = (float) $product['rating_avg'];
                $product['rating_count'] = (int) $product['rating_count'];
                unset($product['relevance']);
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $products
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Product search failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Search failed', [], 500);
        }
    }

    /**
     * Build ORDER BY clause based on sort parameter
     */
    private function buildOrderClause(string $sort): string
    {
        switch ($sort) {
            case 'price_asc':
                return 'ORDER BY p.price ASC';
            case 'price_desc':
                return 'ORDER BY p.price DESC';
            case 'rating':
                return 'ORDER BY p.rating_avg DESC, p.rating_count DESC';
            case 'popularity':
                return 'ORDER BY p.rating_count DESC, p.rating_avg DESC';
            case 'name':
                return 'ORDER BY p.name ASC';
            case 'newest':
            default:
                return 'ORDER BY p.created_at DESC';
        }
    }

    /**
     * Get variants for multiple products
     */
    private function getProductVariants(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $variants = $this->db->fetchAll("
            SELECT product_id, id, sku, color, size, stock, extra_price
            FROM variants
            WHERE product_id IN ({$placeholders}) AND is_active = 1
            ORDER BY color, size
        ", $productIds);

        $grouped = [];
        foreach ($variants as $variant) {
            $grouped[$variant['product_id']][] = $variant;
        }

        return $grouped;
    }

    /**
     * Get images for multiple products
     */
    private function getProductImages(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $images = $this->db->fetchAll("
            SELECT product_id, url, alt_text, is_primary, sort_order
            FROM product_images
            WHERE product_id IN ({$placeholders})
            ORDER BY product_id, is_primary DESC, sort_order ASC
        ", $productIds);

        $grouped = [];
        foreach ($images as $image) {
            $grouped[$image['product_id']][] = $image;
        }

        return $grouped;
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
