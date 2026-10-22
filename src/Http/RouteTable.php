<?php

declare(strict_types=1);

namespace Buffalo\Http;

use Buffalo\Http\Controller\BienvenidoController;
use Buffalo\Http\Controller\HealthController;
use Buffalo\Http\Controller\LoginController;

/**
 * Paths deliberately match the legacy URLs. They are not pretty, but they are
 * what is linked and bookmarked, and keeping them means the golden master can
 * compare the two applications route for route at cutover.
 */
final class RouteTable
{
    public const string LOGIN = '/administrator/index.php';

    /** @return list<Route> */
    public static function all(): array
    {
        return [
            new Route('GET', '/', Access::Public, HealthController::class),
            new Route('GET', self::LOGIN, Access::Public, LoginController::class),
            new Route('POST', '/administrator/bienvenido.php', Access::Public, BienvenidoController::class),
            new Route('GET', '/administrator/bienvenido.php', Access::Admin, BienvenidoController::class),
        ];
    }
}
