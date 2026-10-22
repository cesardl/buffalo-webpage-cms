<?php

declare(strict_types=1);

use Buffalo\Config;
use Buffalo\Database\ConnectionFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

/*
 * Phase 4 replaces this with the router. For now it is a health check: it
 * proves the new runtime is wired up end to end -- autoloading, configuration
 * from the environment, and a working database handle -- without any of the
 * application having been ported yet.
 */

header('Content-Type: application/json');

try {
    $pdo = (new ConnectionFactory(Config::fromEnvironment()))->create();
    $productos = (int) $pdo->query('SELECT COUNT(*) FROM producto')->fetchColumn();
} catch (Throwable $e) {
    // The message can carry the host and the credentials, so it goes to the
    // log, not to the response.
    error_log('Health check failed: ' . $e->getMessage());

    http_response_code(503);
    echo json_encode(['php' => PHP_VERSION, 'database' => 'unavailable'], JSON_THROW_ON_ERROR);

    return;
}

echo json_encode([
    'php' => PHP_VERSION,
    'database' => 'ok',
    'productos' => $productos,
], JSON_THROW_ON_ERROR);
