<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\RecommendationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

class RecommendationController
{
    private RecommendationService $recommendationService;
    private LoggerInterface $logger;

    public function __construct(RecommendationService $recommendationService, LoggerInterface $logger)
    {
        $this->recommendationService = $recommendationService;
        $this->logger = $logger;
    }

    /**
     * Get personalized product recommendations
     */
    public function recommend(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $userId = $request->getAttribute('user_id'); // May be null for guest users

            // Validate input
            $this->validateRecommendationRequest($data);

            // Prepare parameters
            $params = [
                'gender_context' => $data['gender_context'] ?? 'detect',
                'season' => $data['season'] ?? 'auto',
                'user_signals' => $data['user_signals'] ?? [],
                'topk' => min(20, max(1, (int) ($data['topk'] ?? 8))),
                'user_id' => $userId
            ];

            // Get recommendations
            $recommendations = $this->recommendationService->getRecommendations($params);

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $recommendations
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (ValidationException $e) {
            return $this->errorResponse($response, 'Validation failed', $e->getMessages(), 422);
        } catch (\Exception $e) {
            $this->logger->error('Get recommendations failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to get recommendations', [], 500);
        }
    }

    /**
     * Get explanation for a specific product recommendation
     */
    public function explain(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $productId = (int) $args['productId'];
            $userId = $request->getAttribute('user_id'); // May be null for guest users

            if ($productId <= 0) {
                return $this->errorResponse($response, 'Invalid product ID', [], 400);
            }

            // Get explanation
            $explanation = $this->recommendationService->explainProduct($productId, $userId);

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $explanation
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\RuntimeException $e) {
            return $this->errorResponse($response, $e->getMessage(), [], 404);
        } catch (\Exception $e) {
            $this->logger->error('Get product explanation failed', [
                'error' => $e->getMessage(),
                'product_id' => $args['productId'] ?? 'unknown'
            ]);
            return $this->errorResponse($response, 'Failed to get explanation', [], 500);
        }
    }

    /**
     * Validate recommendation request data
     */
    private function validateRecommendationRequest(array $data): void
    {
        $validator = v::key('gender_context', v::optional(v::in(['men', 'women', 'kids', 'unisex', 'detect'])))
                      ->key('season', v::optional(v::in(['spring', 'summer', 'autumn', 'winter', 'all', 'auto'])))
                      ->key('topk', v::optional(v::intType()->between(1, 20)))
                      ->key('user_signals', v::optional(v::arrayType()));

        $validator->assert($data);

        // Validate user_signals structure if provided
        if (isset($data['user_signals']) && is_array($data['user_signals'])) {
            $signalsValidator = v::key('viewed', v::optional(v::arrayType()))
                                 ->key('wishlisted', v::optional(v::arrayType()))
                                 ->key('carted', v::optional(v::arrayType()))
                                 ->key('purchased', v::optional(v::arrayType()));

            $signalsValidator->assert($data['user_signals']);
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
