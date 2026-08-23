<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

class AuthController
{
    private AuthService $authService;
    private LoggerInterface $logger;

    public function __construct(AuthService $authService, LoggerInterface $logger)
    {
        $this->authService = $authService;
        $this->logger = $logger;
    }

    /**
     * Register a new user
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            // Validate input
            $this->validateRegistrationData($data);

            // Register user
            $result = $this->authService->register($data);

            // Set JWT cookie
            $response = $this->setAuthCookie($response, $result['token']);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $result
            ]));

            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');

        } catch (ValidationException $e) {
            return $this->errorResponse($response, 'Validation failed', $e->getMessages(), 422);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($response, $e->getMessage(), [], 400);
        } catch (\Exception $e) {
            $this->logger->error('Registration failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Registration failed', [], 500);
        }
    }

    /**
     * Login user
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            // Validate input
            $this->validateLoginData($data);

            // Authenticate user
            $result = $this->authService->login($data['email'], $data['password']);

            // Set JWT cookie
            $response = $this->setAuthCookie($response, $result['token']);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => $result
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (ValidationException $e) {
            return $this->errorResponse($response, 'Validation failed', $e->getMessages(), 422);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($response, $e->getMessage(), [], 401);
        } catch (\Exception $e) {
            $this->logger->error('Login failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Login failed', [], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $payload = $request->getAttribute('jwt_payload');
            
            if ($payload && isset($payload['jti'])) {
                $this->authService->revokeToken($payload['jti']);
            }

            // Clear auth cookie
            $response = $response->withHeader('Set-Cookie', 'auth_token=; Path=/; HttpOnly; Max-Age=0');

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Logout successful'
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Logout failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Logout failed', [], 500);
        }
    }

    /**
     * Get current user info
     */
    public function me(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $request->getAttribute('user_id');
            $user = $this->authService->getUserById($userId);

            if (!$user) {
                return $this->errorResponse($response, 'User not found', [], 404);
            }

            // Remove sensitive data
            unset($user['password_hash']);

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => ['user' => $user]
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Get user info failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Failed to get user info', [], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // Extract current token
            $authHeader = $request->getHeaderLine('Authorization');
            $token = null;
            
            if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }

            if (!$token) {
                return $this->errorResponse($response, 'Missing token', [], 401);
            }

            // Verify current token (even if expired, we allow refresh)
            $payload = $this->authService->verifyToken($token);
            
            if (!$payload) {
                return $this->errorResponse($response, 'Invalid token', [], 401);
            }

            // Get user and generate new token
            $userId = $this->authService->getUserIdFromPayload($payload);
            $user = $this->authService->getUserById($userId);

            if (!$user) {
                return $this->errorResponse($response, 'User not found', [], 404);
            }

            // Revoke old token
            if (isset($payload['jti'])) {
                $this->authService->revokeToken($payload['jti']);
            }

            // Generate new token
            $newToken = $this->authService->generateToken($user);

            // Set new JWT cookie
            $response = $this->setAuthCookie($response, $newToken);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'user' => array_diff_key($user, ['password_hash' => '']),
                    'token' => $newToken
                ]
            ]));

            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error('Token refresh failed', ['error' => $e->getMessage()]);
            return $this->errorResponse($response, 'Token refresh failed', [], 500);
        }
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData(array $data): void
    {
        $validator = v::key('name', v::stringType()->notEmpty()->length(2, 255))
                      ->key('email', v::email())
                      ->key('password', v::stringType()->length(8, null))
                      ->key('gender', v::optional(v::in(['male', 'female', 'other'])))
                      ->key('birthdate', v::optional(v::date()));

        $validator->assert($data);
    }

    /**
     * Validate login data
     */
    private function validateLoginData(array $data): void
    {
        $validator = v::key('email', v::email())
                      ->key('password', v::stringType()->notEmpty());

        $validator->assert($data);
    }

    /**
     * Set authentication cookie
     */
    private function setAuthCookie(ResponseInterface $response, string $token): ResponseInterface
    {
        $secure = $_ENV['SESSION_SECURE'] === 'true';
        $httpOnly = $_ENV['SESSION_HTTP_ONLY'] !== 'false';
        $maxAge = (int) ($_ENV['SESSION_LIFETIME'] ?? 86400);

        $cookieValue = "auth_token={$token}; Path=/; HttpOnly; Max-Age={$maxAge}";
        
        if ($secure) {
            $cookieValue .= '; Secure';
        }

        return $response->withHeader('Set-Cookie', $cookieValue);
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
