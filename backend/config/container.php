<?php

declare(strict_types=1);

use App\Services\DatabaseService;
use App\Services\AuthService;
use App\Services\RecommendationService;
use App\Middleware\CorsMiddleware;
use App\Middleware\JwtMiddleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    // Database service
    DatabaseService::class => function (ContainerInterface $container) {
        return new DatabaseService(
            $_ENV['DB_HOST'],
            $_ENV['DB_NAME'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            (int) $_ENV['DB_PORT']
        );
    },

    // Logger
    LoggerInterface::class => function (ContainerInterface $container) {
        $logger = new Logger('app');
        $handler = new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG);
        $logger->pushHandler($handler);
        return $logger;
    },

    // Auth service
    AuthService::class => function (ContainerInterface $container) {
        return new AuthService(
            $container->get(DatabaseService::class),
            $container->get(LoggerInterface::class),
            $_ENV['JWT_SECRET'],
            $_ENV['JWT_ALGORITHM'],
            (int) $_ENV['JWT_EXPIRY']
        );
    },

    // Recommendation service
    RecommendationService::class => function (ContainerInterface $container) {
        return new RecommendationService(
            $container->get(DatabaseService::class),
            $container->get(LoggerInterface::class),
            $_ENV['AI_SERVICE_URL'],
            (int) $_ENV['AI_SERVICE_TIMEOUT']
        );
    },

    // CORS middleware
    'cors' => function (ContainerInterface $container) {
        return new CorsMiddleware([
            'origin' => explode(',', $_ENV['CORS_ORIGIN']),
            'methods' => explode(',', $_ENV['CORS_METHODS']),
            'headers.allow' => explode(',', $_ENV['CORS_HEADERS']),
            'headers.expose' => [],
            'credentials' => true,
            'cache' => 86400,
        ]);
    },

    // JWT middleware
    'jwt' => function (ContainerInterface $container) {
        return new JwtMiddleware(
            $container->get(AuthService::class),
            $container->get(LoggerInterface::class)
        );
    },
];
