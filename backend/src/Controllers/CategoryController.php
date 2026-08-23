<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class CategoryController
{
    private DatabaseService $db;
    private LoggerInterface $logger;

    public function __construct(DatabaseService $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get all categories
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $categories = $this->db->fetchAll("
                SELECT 
                    c.*,
                    COUNT(p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                GROUP BY c.id
                ORDER BY c.name ASC
            ");

            // Format the response
            foreach ($categories as &$category) {
                $category['product_count'] = (int) $category['product_count'];
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $categories
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get categories failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to get categories', [], 500);
        }
    }

    /**
     * Get single category by slug with products
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $slug = $args['slug'];
            $queryParams = $request->getQueryParams();
            
            // Get category
            $category = $this->db->fetchOne("
                SELECT * FROM categories WHERE slug = ?
            ", [$slug]);

            if (!$category) {
                return $this->errorResponse($response, 'Category not found', [], 404);
            }

            // Get category statistics
            $stats = $this->db->fetchOne("
                SELECT 
                    COUNT(*) as total_products,
                    AVG(price) as avg_price,
                    MIN(price) as min_price,
                    MAX(price) as max_price,
                    COUNT(DISTINCT gender_target) as gender_variants,
                    COUNT(DISTINCT season) as season_variants
                FROM products 
                WHERE category_id = ? AND is_active = 1
            ", [$category['id']]);

            // Get available filters for this category
            $genderTargets = $this->db->fetchAll("
                SELECT DISTINCT gender_target, COUNT(*) as count
                FROM products 
                WHERE category_id = ? AND is_active = 1
                GROUP BY gender_target
                ORDER BY count DESC
            ", [$category['id']]);

            $seasons = $this->db->fetchAll("
                SELECT DISTINCT season, COUNT(*) as count
                FROM products 
                WHERE category_id = ? AND is_active = 1
                GROUP BY season
                ORDER BY count DESC
            ", [$category['id']]);

            $brands = $this->db->fetchAll("
                SELECT DISTINCT brand, COUNT(*) as count
                FROM products 
                WHERE category_id = ? AND is_active = 1 AND brand IS NOT NULL
                GROUP BY brand
                ORDER BY count DESC
                LIMIT 20
            ", [$category['id']]);

            $materials = $this->db->fetchAll("
                SELECT DISTINCT material, COUNT(*) as count
                FROM products 
                WHERE category_id = ? AND is_active = 1 AND material IS NOT NULL
                GROUP BY material
                ORDER BY count DESC
                LIMIT 20
            ", [$category['id']]);

            // Get price ranges
            $priceRanges = [
                ['min' => 0, 'max' => 25, 'label' => 'Under $25'],
                ['min' => 25, 'max' => 50, 'label' => '$25 - $50'],
                ['min' => 50, 'max' => 100, 'label' => '$50 - $100'],
                ['min' => 100, 'max' => 200, 'label' => '$100 - $200'],
                ['min' => 200, 'max' => null, 'label' => 'Over $200']
            ];

            foreach ($priceRanges as &$range) {
                $whereClause = "category_id = ? AND is_active = 1 AND price >= ?";
                $params = [$category['id'], $range['min']];
                
                if ($range['max'] !== null) {
                    $whereClause .= " AND price <= ?";
                    $params[] = $range['max'];
                }

                $count = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE {$whereClause}", $params);
                $range['count'] = (int) $count['count'];
            }

            // Format stats
            $stats['total_products'] = (int) $stats['total_products'];
            $stats['avg_price'] = (float) $stats['avg_price'];
            $stats['min_price'] = (float) $stats['min_price'];
            $stats['max_price'] = (float) $stats['max_price'];

            $category['stats'] = $stats;
            $category['filters'] = [
                'gender_targets' => $genderTargets,
                'seasons' => $seasons,
                'brands' => $brands,
                'materials' => $materials,
                'price_ranges' => $priceRanges
            ];

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $category
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get category failed', ['error' => $e->getMessage(), 'slug' => $args['slug'] ?? 'unknown']);
            return $this->errorResponse($response, 'Failed to get category', [], 500);
        }
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
