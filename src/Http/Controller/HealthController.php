<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use PDO;
use Throwable;

final readonly class HealthController implements Controller
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $productos = (int) $this->pdo->query('SELECT COUNT(*) FROM producto')->fetchColumn();
        } catch (Throwable $e) {
            // The message can carry host and credentials, so it is logged
            // rather than returned.
            error_log('Health check failed: ' . $e->getMessage());

            return Response::text(
                json_encode(['php' => PHP_VERSION, 'database' => 'unavailable'], JSON_THROW_ON_ERROR),
                503,
            );
        }

        return Response::text(json_encode([
            'php' => PHP_VERSION,
            'database' => 'ok',
            'productos' => $productos,
        ], JSON_THROW_ON_ERROR));
    }
}
