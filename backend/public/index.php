<?php
// Simple PHP Backend for Clothing E-commerce (No Dependencies)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load configuration
$config = [
    'db_host' => '127.0.0.1',
    'db_name' => 'clothing_ecommerce',
    'db_user' => 'root',
    'db_pass' => '',
    'jwt_secret' => 'your-super-secret-jwt-key'
];

// Database connection
function getDatabase($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

// Simple JWT functions (basic implementation)
function createJWT($payload, $secret) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);
    
    $headerEncoded = base64url_encode($header);
    $payloadEncoded = base64url_encode($payload);
    
    $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $secret, true);
    $signatureEncoded = base64url_encode($signature);
    
    return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

// Get request path and method
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove /api prefix if present
$path = preg_replace('#^/api#', '', $path);

// Simple routing
switch ($path) {
    case '/health':
        if ($method === 'GET') {
            echo json_encode([
                'success' => true,
                'message' => 'API is working!',
                'data' => [
                    'status' => 'healthy',
                    'timestamp' => date('c'),
                    'version' => '1.0.0'
                ]
            ]);
        }
        break;
        
    case '/categories':
        if ($method === 'GET') {
            $db = getDatabase($config);
            if ($db) {
                try {
                    $stmt = $db->query("SELECT * FROM categories ORDER BY name");
                    $categories = $stmt->fetchAll();
                    echo json_encode([
                        'success' => true,
                        'data' => $categories
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database error: ' . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection failed'
                ]);
            }
        }
        break;
        
    case '/products':
        if ($method === 'GET') {
            $db = getDatabase($config);
            if ($db) {
                try {
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;
                    
                        $stmt = $db->prepare("
                            SELECT p.*, c.name as category_name, c.slug as category_slug,
                                   pi.url as image_url, pi.alt_text as image_alt
                            FROM products p
                            LEFT JOIN categories c ON p.category_id = c.id
                            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                            WHERE p.is_active = 1
                            ORDER BY p.created_at DESC
                            LIMIT $limit OFFSET $offset
                        ");
                    $stmt->execute();
                    $products = $stmt->fetchAll();
                    
                    // Get total count
                    $countStmt = $db->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
                    $total = $countStmt->fetch()['total'];
                    
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'items' => $products,
                            'pagination' => [
                                'page' => $page,
                                'limit' => $limit,
                                'total' => (int)$total,
                                'pages' => ceil($total / $limit)
                            ]
                        ]
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database error: ' . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection failed'
                ]);
            }
        }
        break;
        
    case '/auth/register':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['name']) || !isset($input['email']) || !isset($input['password'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields'
                ]);
                break;
            }
            
            $db = getDatabase($config);
            if ($db) {
                try {
                    // Check if email exists
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$input['email']]);
                    if ($stmt->fetch()) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Email already exists'
                        ]);
                        break;
                    }
                    
                    // Create user
                    $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, gender) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $input['name'],
                        $input['email'],
                        $passwordHash,
                        $input['gender'] ?? null
                    ]);
                    
                    $userId = $db->lastInsertId();
                    
                    // Create wishlist
                    $stmt = $db->prepare("INSERT INTO wishlists (user_id) VALUES (?)");
                    $stmt->execute([$userId]);
                    
                    // Get user data
                    $stmt = $db->prepare("SELECT id, name, email, gender, created_at FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                    
                    // Create JWT
                    $token = createJWT([
                        'user_id' => $userId,
                        'email' => $input['email'],
                        'exp' => time() + 3600
                    ], $config['jwt_secret']);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'User registered successfully',
                        'data' => [
                            'user' => $user,
                            'token' => $token
                        ]
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Registration failed: ' . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection failed'
                ]);
            }
        }
        break;
        
    case '/auth/login':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['email']) || !isset($input['password'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email and password required'
                ]);
                break;
            }
            
            $db = getDatabase($config);
            if ($db) {
                try {
                    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                    $stmt->execute([$input['email']]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($input['password'], $user['password_hash'])) {
                        // Remove password from response
                        unset($user['password_hash']);
                        
                        // Create JWT
                        $token = createJWT([
                            'user_id' => $user['id'],
                            'email' => $user['email'],
                            'exp' => time() + 3600
                        ], $config['jwt_secret']);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Login successful',
                            'data' => [
                                'user' => $user,
                                'token' => $token
                            ]
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid credentials'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Login failed: ' . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection failed'
                ]);
            }
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'path' => $path,
            'method' => $method
        ]);
        break;
}
?>
