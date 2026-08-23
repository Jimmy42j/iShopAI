<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'origin' => ['*'],
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'headers.allow' => ['Content-Type', 'Authorization', 'X-Requested-With'],
            'headers.expose' => [],
            'credentials' => false,
            'cache' => 86400,
        ], $options);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        $method = $request->getMethod();

        // Handle preflight requests
        if ($method === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
        } else {
            $response = $handler->handle($request);
        }

        // Set CORS headers
        if ($this->isOriginAllowed($origin)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
        } elseif (in_array('*', $this->options['origin'])) {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        }

        if ($this->options['credentials']) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        $response = $response
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->options['methods']))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->options['headers.allow']));

        if (!empty($this->options['headers.expose'])) {
            $response = $response->withHeader('Access-Control-Expose-Headers', implode(', ', $this->options['headers.expose']));
        }

        if ($method === 'OPTIONS') {
            $response = $response
                ->withHeader('Access-Control-Max-Age', (string) $this->options['cache'])
                ->withStatus(204);
        }

        return $response;
    }

    private function isOriginAllowed(string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }

        foreach ($this->options['origin'] as $allowedOrigin) {
            if ($allowedOrigin === '*' || $allowedOrigin === $origin) {
                return true;
            }

            // Support wildcard subdomains
            if (strpos($allowedOrigin, '*.') === 0) {
                $pattern = str_replace('*.', '', $allowedOrigin);
                if (strpos($origin, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
