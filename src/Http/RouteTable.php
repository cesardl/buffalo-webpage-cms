<?php

declare(strict_types=1);

namespace Buffalo\Http;

use Buffalo\Http\Controller\BienvenidoController;
use Buffalo\Http\Controller\FormColoresController;
use Buffalo\Http\Controller\FormDetallesController;
use Buffalo\Http\Controller\FormProductoController;
use Buffalo\Http\Controller\GuardarColoresController;
use Buffalo\Http\Controller\GuardarDetallesController;
use Buffalo\Http\Controller\GuardarProductoController;
use Buffalo\Http\Controller\HealthController;
use Buffalo\Http\Controller\LoginController;
use Buffalo\Http\Controller\ProductosController;
use Buffalo\Http\Controller\VehiculoDeleteController;
use Buffalo\Http\Controller\VehiculoSelectController;

/**
 * Paths deliberately match the legacy URLs. They are not pretty, but they are
 * what is linked and bookmarked, and keeping them means the golden master can
 * compare the two applications route for route at cutover.
 *
 * Every route states its Access, because Route requires it. The legacy app
 * made that decision per file and got it wrong by omission on five of them.
 */
final class RouteTable
{
    public const string LOGIN = '/administrator/index.php';

    /** @return list<Route> */
    public static function all(): array
    {
        return [
            new Route('GET', '/', Access::Public, HealthController::class),

            ...array_map(
                static fn (string $path): Route => new Route(
                    'GET',
                    $path,
                    Access::Public,
                    ProductosController::class,
                ),
                ProductosController::paths(),
            ),

            new Route('GET', self::LOGIN, Access::Public, LoginController::class),
            new Route('POST', '/administrator/bienvenido.php', Access::Public, BienvenidoController::class),
            new Route('GET', '/administrator/bienvenido.php', Access::Admin, BienvenidoController::class),

            new Route('GET', '/administrator/formProducto.php', Access::Admin, FormProductoController::class),
            new Route('GET', '/administrator/formDetalles.php', Access::Admin, FormDetallesController::class),
            new Route('GET', '/administrator/formColores.php', Access::Admin, FormColoresController::class),

            new Route('POST', '/administrator/actionProductoCtrl.php', Access::Admin, GuardarProductoController::class),
            new Route('POST', '/administrator/actionDetalleProductoCtrl.php', Access::Admin, GuardarDetallesController::class),
            new Route('POST', '/administrator/actionColor.php', Access::Admin, GuardarColoresController::class),

            new Route('POST', '/administrator/VehiculoSelect.php', Access::AdminApi, VehiculoSelectController::class),
            new Route('POST', '/administrator/VehiculoDelete.php', Access::AdminApi, VehiculoDeleteController::class),
        ];
    }
}
