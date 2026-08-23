<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;

class JwtMiddleware implements MiddlewareInterface
{
    private AuthService $authService;
    private LoggerInterface $logger;

    public function __construct(AuthService $authService, LoggerInterface $logger)
    {
        $this->authService = $authService;
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Skip authentication for certain routes
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        if ($this->shouldSkipAuth($path, $method)) {
            return $handler->handle($request);
        }

        // Extract token from Authorization header or cookie
        $token = $this->extractToken($request);

        if (!$token) {
            return $this->unauthorizedResponse('Missing authentication token');
        }

        // Verify token
        $payload = $this->authService->verifyToken($token);

        if (!$payload) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        // Add user data to request attributes
        $request = $request
            ->withAttribute('jwt_payload', $payload)
            ->withAttribute('user_id', $this->authService->getUserIdFromPayload($payload))
            ->withAttribute('user', $this->authService->getUserFromPayload($payload));

        return $handler->handle($request);
    }

    private function extractToken(ServerRequestInterface $request): ?string
    {
        // Try Authorization header first
        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // Try cookie as fallback
        $cookies = $request->getCookieParams();
        if (isset($cookies['auth_token'])) {
            return $cookies['auth_token'];
        }

        return null;
    }

    private function shouldSkipAuth(string $path, string $method): bool
    {
        $publicRoutes = [
            // Auth routes
            'POST:/api/auth/register',
            'POST:/api/auth/login',
            'POST:/api/auth/refresh',
            
            // Public catalog routes
            'GET:/api/categories',
            'GET:/api/products',
            'GET:/api/health',
            
            // Cart routes (support guest users)
            'GET:/api/cart',
            'POST:/api/cart',
            'PATCH:/api/cart',
            'DELETE:/api/cart',
            
            // AI recommendations (can work without auth)
            'POST:/api/ai/recommend',
            'GET:/api/ai/explain',
        ];

        $currentRoute = $method . ':' . $path;
        
        foreach ($publicRoutes as $route) {
            if ($this->matchesRoute($currentRoute, $route)) {
                return true;
            }
        }

        return false;
    }

    private function matchesRoute(string $currentRoute, string $pattern): bool
    {
        // Exact match
        if ($currentRoute === $pattern) {
            return true;
        }

        // Pattern matching for routes with parameters
        $patternRegex = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
        $patternRegex = str_replace('/', '\/', $patternRegex);
        
        return preg_match('/^' . $patternRegex . '$/', $currentRoute) === 1;
    }

    private function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => 'Unauthorized',
            'message' => $message,
            'status' => 401
        ]));

        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
}
