<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use InvalidArgumentException;

class AuthService
{
    private DatabaseService $db;
    private LoggerInterface $logger;
    private string $jwtSecret;
    private string $jwtAlgorithm;
    private int $jwtExpiry;

    public function __construct(
        DatabaseService $db,
        LoggerInterface $logger,
        string $jwtSecret,
        string $jwtAlgorithm = 'HS256',
        int $jwtExpiry = 3600
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->jwtSecret = $jwtSecret;
        $this->jwtAlgorithm = $jwtAlgorithm;
        $this->jwtExpiry = $jwtExpiry;
    }

    /**
     * Register a new user
     */
    public function register(array $userData): array
    {
        // Validate required fields
        $required = ['name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                throw new InvalidArgumentException("Field '{$field}' is required");
            }
        }

        // Check if email already exists
        $existingUser = $this->db->fetchOne(
            'SELECT id FROM users WHERE email = ?',
            [$userData['email']]
        );

        if ($existingUser) {
            throw new RuntimeException('Email already exists');
        }

        // Hash password
        $passwordHash = password_hash($userData['password'], PASSWORD_ARGON2ID);

        // Insert user
        $userId = $this->db->insert(
            'INSERT INTO users (name, email, password_hash, gender, birthdate) VALUES (?, ?, ?, ?, ?)',
            [
                $userData['name'],
                $userData['email'],
                $passwordHash,
                $userData['gender'] ?? null,
                $userData['birthdate'] ?? null
            ]
        );

        // Create wishlist for the user
        $this->db->insert('INSERT INTO wishlists (user_id) VALUES (?)', [$userId]);

        // Get the created user
        $user = $this->getUserById($userId);
        
        // Generate JWT token
        $token = $this->generateToken($user);

        $this->logger->info('User registered', ['user_id' => $userId, 'email' => $userData['email']]);

        return [
            'user' => $this->sanitizeUser($user),
            'token' => $token
        ];
    }

    /**
     * Authenticate user login
     */
    public function login(string $email, string $password): array
    {
        $user = $this->db->fetchOne(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('Invalid credentials');
        }

        // Generate JWT token
        $token = $this->generateToken($user);

        $this->logger->info('User logged in', ['user_id' => $user['id'], 'email' => $email]);

        return [
            'user' => $this->sanitizeUser($user),
            'token' => $token
        ];
    }

    /**
     * Generate JWT token for user
     */
    public function generateToken(array $user): string
    {
        $now = time();
        $jti = bin2hex(random_bytes(16)); // Unique token ID

        $payload = [
            'iss' => $_ENV['APP_URL'] ?? 'clothing-ecommerce',
            'aud' => $_ENV['APP_URL'] ?? 'clothing-ecommerce',
            'iat' => $now,
            'exp' => $now + $this->jwtExpiry,
            'jti' => $jti,
            'sub' => (string) $user['id'],
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'gender' => $user['gender']
            ]
        ];

        // Store session in database
        $this->db->insert(
            'INSERT INTO sessions (user_id, jwt_jti, user_agent, ip_address, expires_at) VALUES (?, ?, ?, ?, ?)',
            [
                $user['id'],
                $jti,
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $_SERVER['REMOTE_ADDR'] ?? '',
                date('Y-m-d H:i:s', $now + $this->jwtExpiry)
            ]
        );

        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }

    /**
     * Verify and decode JWT token
     */
    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
            $payload = (array) $decoded;

            // Check if session is still valid
            $session = $this->db->fetchOne(
                'SELECT * FROM sessions WHERE jwt_jti = ? AND revoked = FALSE AND expires_at > NOW()',
                [$payload['jti']]
            );

            if (!$session) {
                return null;
            }

            return $payload;
        } catch (ExpiredException $e) {
            $this->logger->info('Token expired', ['token' => substr($token, 0, 20) . '...']);
            return null;
        } catch (SignatureInvalidException $e) {
            $this->logger->warning('Invalid token signature', ['token' => substr($token, 0, 20) . '...']);
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Token verification failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Revoke a token (logout)
     */
    public function revokeToken(string $jti): bool
    {
        $result = $this->db->execute(
            'UPDATE sessions SET revoked = TRUE WHERE jwt_jti = ?',
            [$jti]
        );

        if ($result) {
            $this->logger->info('Token revoked', ['jti' => $jti]);
        }

        return $result;
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $userId): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE id = ?',
            [$userId]
        );
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );
    }

    /**
     * Update user profile
     */
    public function updateUser(int $userId, array $data): array
    {
        $allowedFields = ['name', 'gender', 'birthdate'];
        $updateFields = [];
        $params = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            throw new InvalidArgumentException('No valid fields to update');
        }

        $params[] = $userId;
        $sql = 'UPDATE users SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?';
        
        $this->db->execute($sql, $params);

        return $this->getUserById($userId);
    }

    /**
     * Change user password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->getUserById($userId);
        
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            throw new RuntimeException('Current password is incorrect');
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        
        $result = $this->db->execute(
            'UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?',
            [$newPasswordHash, $userId]
        );

        if ($result) {
            // Revoke all existing sessions for security
            $this->db->execute(
                'UPDATE sessions SET revoked = TRUE WHERE user_id = ?',
                [$userId]
            );
            
            $this->logger->info('Password changed', ['user_id' => $userId]);
        }

        return $result;
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int
    {
        return $this->db->rowCount(
            'DELETE FROM sessions WHERE expires_at < NOW() OR revoked = TRUE'
        );
    }

    /**
     * Remove sensitive data from user array
     */
    private function sanitizeUser(array $user): array
    {
        unset($user['password_hash']);
        return $user;
    }

    /**
     * Extract user ID from JWT payload
     */
    public function getUserIdFromPayload(array $payload): int
    {
        return (int) $payload['sub'];
    }

    /**
     * Extract user data from JWT payload
     */
    public function getUserFromPayload(array $payload): array
    {
        return $payload['user'] ?? [];
    }
}
