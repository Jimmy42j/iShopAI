<?php

declare(strict_types=1);

namespace App\Services;

use Psr\Log\LoggerInterface;
use RuntimeException;

class RecommendationService
{
    private DatabaseService $db;
    private LoggerInterface $logger;
    private string $aiServiceUrl;
    private int $timeout;

    public function __construct(
        DatabaseService $db,
        LoggerInterface $logger,
        string $aiServiceUrl,
        int $timeout = 30
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->aiServiceUrl = $aiServiceUrl;
        $this->timeout = $timeout;
    }

    /**
     * Get personalized product recommendations
     */
    public function getRecommendations(array $params): array
    {
        $startTime = microtime(true);
        
        // Extract parameters
        $genderContext = $params['gender_context'] ?? 'detect';
        $season = $params['season'] ?? 'auto';
        $userSignals = $params['user_signals'] ?? [];
        $topk = (int) ($params['topk'] ?? 8);
        $userId = $params['user_id'] ?? null;

        // Auto-detect season if needed
        if ($season === 'auto') {
            $season = $this->detectCurrentSeason();
        }

        // Auto-detect gender if needed and user is logged in
        if ($genderContext === 'detect' && $userId) {
            $user = $this->db->fetchOne('SELECT gender FROM users WHERE id = ?', [$userId]);
            if ($user && $user['gender']) {
                $genderContext = $user['gender'] === 'male' ? 'men' : 
                                ($user['gender'] === 'female' ? 'women' : 'unisex');
            }
        }

        // Get user context if logged in
        $userContext = [];
        if ($userId) {
            $userContext = $this->getUserContext($userId);
        }

        // Try AI service first, fallback to rule-based recommendations
        try {
            $recommendations = $this->getAIRecommendations([
                'gender_context' => $genderContext,
                'season' => $season,
                'user_signals' => array_merge($userSignals, $userContext),
                'topk' => $topk
            ]);
        } catch (\Exception $e) {
            $this->logger->warning('AI service failed, using fallback', ['error' => $e->getMessage()]);
            $recommendations = $this->getFallbackRecommendations($genderContext, $season, $topk);
        }

        $endTime = microtime(true);
        $responseTime = (int) (($endTime - $startTime) * 1000);

        // Log the recommendation request
        $this->logRecommendation([
            'user_id' => $userId,
            'gender_context' => $genderContext,
            'season_context' => $season,
            'topk' => $topk,
            'model_version' => $recommendations['model_version'] ?? 'fallback-v1',
            'response_ms' => $responseTime,
            'products' => json_encode($recommendations['products']),
            'reason' => $recommendations['reason'] ?? 'Rule-based recommendations'
        ]);

        return [
            'products' => $recommendations['products'],
            'model_version' => $recommendations['model_version'] ?? 'fallback-v1',
            'latency_ms' => $responseTime
        ];
    }

    /**
     * Get AI-powered recommendations from external service
     */
    private function getAIRecommendations(array $params): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->aiServiceUrl . '/recommend',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new RuntimeException("AI service request failed: {$error}");
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("AI service returned HTTP {$httpCode}");
        }

        $data = json_decode($response, true);
        if (!$data) {
            throw new RuntimeException("Invalid response from AI service");
        }

        return $data;
    }

    /**
     * Fallback rule-based recommendations
     */
    private function getFallbackRecommendations(string $genderContext, string $season, int $topk): array
    {
        // Build base query
        $conditions = ['p.is_active = 1'];
        $params = [];

        // Gender filter
        if ($genderContext !== 'unisex' && $genderContext !== 'detect') {
            $conditions[] = '(p.gender_target = ? OR p.gender_target = "unisex")';
            $params[] = $genderContext;
        }

        // Season filter
        if ($season !== 'all') {
            $conditions[] = '(p.season = ? OR p.season = "all")';
            $params[] = $season;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $conditions);

        // Get recommendations with weighted scoring
        $sql = "
            SELECT 
                p.id as product_id,
                p.name,
                p.price,
                p.rating_avg,
                p.rating_count,
                p.season,
                p.gender_target,
                p.material,
                p.brand,
                (
                    (p.rating_avg * 0.3) + 
                    (LEAST(p.rating_count / 100, 1) * 0.2) +
                    (CASE WHEN p.season = ? THEN 0.3 ELSE 0.1 END) +
                    (CASE WHEN p.gender_target = ? THEN 0.2 ELSE 0.1 END)
                ) as score
            FROM products p
            {$whereClause}
            AND p.rating_avg >= 4.0
            ORDER BY score DESC, p.rating_avg DESC, p.rating_count DESC
            LIMIT ?
        ";

        $params[] = $season;
        $params[] = $genderContext;
        $params[] = $topk;

        $products = $this->db->fetchAll($sql, $params);

        // Generate reasons for each product
        $recommendations = [];
        foreach ($products as $product) {
            $reason = $this->generateReason($product, $genderContext, $season);
            $recommendations[] = [
                'product_id' => (int) $product['product_id'],
                'score' => (float) $product['score'],
                'reason' => $reason
            ];
        }

        return [
            'products' => $recommendations,
            'model_version' => 'fallback-v1',
            'reason' => 'Rule-based recommendations using rating and seasonal matching'
        ];
    }

    /**
     * Generate reason text for a product recommendation
     */
    private function generateReason(array $product, string $genderContext, string $season): string
    {
        $templates = [
            'seasonal_material' => "{material} {name} perfect for {gender} in {season}",
            'rating_seasonal' => "Highly rated {name} ideal for {season} {gender}",
            'brand_seasonal' => "{brand} {name} suits {gender} {season} style",
            'material_comfort' => "Comfortable {material} {name} for {gender}",
            'versatile' => "Versatile {name} great for {gender} year-round"
        ];

        // Choose template based on available data
        $template = 'versatile';
        if ($product['material'] && $season !== 'all') {
            $template = 'seasonal_material';
        } elseif ($product['rating_avg'] >= 4.5 && $season !== 'all') {
            $template = 'rating_seasonal';
        } elseif ($product['brand'] && $season !== 'all') {
            $template = 'brand_seasonal';
        } elseif ($product['material']) {
            $template = 'material_comfort';
        }

        $reason = $templates[$template];
        
        // Replace placeholders
        $replacements = [
            '{material}' => $product['material'] ?? 'quality',
            '{name}' => strtolower($this->getProductType($product['name'])),
            '{gender}' => $genderContext,
            '{season}' => $season,
            '{brand}' => $product['brand'] ?? 'quality'
        ];

        $reason = str_replace(array_keys($replacements), array_values($replacements), $reason);
        
        // Capitalize first letter and ensure it's under 22 words
        $reason = ucfirst($reason);
        $words = explode(' ', $reason);
        if (count($words) > 22) {
            $reason = implode(' ', array_slice($words, 0, 22));
        }

        return $reason;
    }

    /**
     * Extract product type from product name
     */
    private function getProductType(string $productName): string
    {
        $types = [
            'shirt' => ['shirt', 'blouse', 'top'],
            'pants' => ['pants', 'jeans', 'trousers', 'chinos'],
            'dress' => ['dress'],
            'jacket' => ['jacket', 'coat', 'blazer'],
            'shoes' => ['shoes', 'boots', 'sneakers', 'sandals'],
            'sweater' => ['sweater', 'cardigan', 'hoodie'],
            'shorts' => ['shorts'],
            'skirt' => ['skirt']
        ];

        $lowerName = strtolower($productName);
        foreach ($types as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($lowerName, $keyword) !== false) {
                    return $type;
                }
            }
        }

        return 'item';
    }

    /**
     * Detect current season based on date
     */
    private function detectCurrentSeason(): string
    {
        $month = (int) date('n');
        
        if ($month >= 3 && $month <= 5) {
            return 'spring';
        } elseif ($month >= 6 && $month <= 8) {
            return 'summer';
        } elseif ($month >= 9 && $month <= 11) {
            return 'autumn';
        } else {
            return 'winter';
        }
    }

    /**
     * Get user context for personalization
     */
    private function getUserContext(int $userId): array
    {
        // Get recently viewed products (from session or tracking table if implemented)
        $viewed = [];

        // Get wishlist items
        $wishlist = $this->db->fetchAll(
            'SELECT wi.product_id FROM wishlist_items wi 
             JOIN wishlists w ON wi.wishlist_id = w.id 
             WHERE w.user_id = ? 
             ORDER BY wi.created_at DESC LIMIT 10',
            [$userId]
        );
        $wishlisted = array_column($wishlist, 'product_id');

        // Get cart items
        $cart = $this->db->fetchAll(
            'SELECT ci.product_id FROM cart_items ci 
             JOIN carts c ON ci.cart_id = c.id 
             WHERE c.user_id = ?',
            [$userId]
        );
        $carted = array_column($cart, 'product_id');

        // Get order history
        $orders = $this->db->fetchAll(
            'SELECT oi.product_id FROM order_items oi 
             JOIN orders o ON oi.order_id = o.id 
             WHERE o.user_id = ? 
             ORDER BY o.created_at DESC LIMIT 20',
            [$userId]
        );
        $purchased = array_column($orders, 'product_id');

        return [
            'viewed' => $viewed,
            'wishlisted' => $wishlisted,
            'carted' => $carted,
            'purchased' => $purchased
        ];
    }

    /**
     * Log recommendation request for analytics
     */
    private function logRecommendation(array $data): void
    {
        $this->db->insert(
            'INSERT INTO recommendation_logs 
             (user_id, gender_context, season_context, topk, model_version, response_ms, products, reason) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['user_id'],
                $data['gender_context'],
                $data['season_context'],
                $data['topk'],
                $data['model_version'],
                $data['response_ms'],
                $data['products'],
                $data['reason']
            ]
        );
    }

    /**
     * Get explanation for a specific product recommendation
     */
    public function explainProduct(int $productId, ?int $userId = null): array
    {
        $product = $this->db->fetchOne(
            'SELECT * FROM products WHERE id = ? AND is_active = 1',
            [$productId]
        );

        if (!$product) {
            throw new RuntimeException('Product not found');
        }

        // Generate explanation
        $features = [
            ['key' => 'rating', 'weight' => $product['rating_avg'] / 5],
            ['key' => 'popularity', 'weight' => min($product['rating_count'] / 100, 1)],
            ['key' => 'seasonal_match', 'weight' => 0.8], // Mock weight
            ['key' => 'gender_match', 'weight' => 0.9], // Mock weight
        ];

        $reason = $this->generateReason($product, 'unisex', $this->detectCurrentSeason());

        return [
            'reason' => $reason,
            'features' => $features,
            'model_version' => 'fallback-v1'
        ];
    }
}
