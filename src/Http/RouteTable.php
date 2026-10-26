<?php

declare(strict_types=1);

namespace Buffalo\Http;

use Buffalo\Http\Controller\BienvenidoController;
use Buffalo\Http\Controller\ContactanosController;
use Buffalo\Http\Controller\ContactoDetalleController;
use Buffalo\Http\Controller\ContactosController;
use Buffalo\Http\Controller\FormColoresController;
use Buffalo\Http\Controller\FormDetallesController;
use Buffalo\Http\Controller\FormProductoController;
use Buffalo\Http\Controller\GuardarColoresController;
use Buffalo\Http\Controller\GuardarContactoController;
use Buffalo\Http\Controller\GuardarDetallesController;
use Buffalo\Http\Controller\GuardarEstadoContactoController;
use Buffalo\Http\Controller\GuardarProductoController;
use Buffalo\Http\Controller\HealthController;
use Buffalo\Http\Controller\LoginController;
use Buffalo\Http\Controller\ProductosController;
use Buffalo\Http\Controller\VehiculoDeleteController;
use Buffalo\Http\Controller\VehiculoSelectController;
use Buffalo\Http\Controller\VehiculosController;

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
            // Apache redirects / to index.html, so the probe has a path of
            // its own. It answers 503 when the database is unreachable, which
            // is what makes it usable as a readiness check.
            new Route('GET', '/health.php', Access::Public, HealthController::class),

            ...array_map(
                static fn (string $path): Route => new Route(
                    'GET',
                    $path,
                    Access::Public,
                    ProductosController::class,
                ),
                ProductosController::paths(),
            ),

            // The contact page carries a CSRF token, so it cannot be a file
            // on disk any more. The form posts to its own endpoint, which
            // answers JSON: the page reports the result without navigating.
            new Route('GET', '/contactanos.html', Access::Public, ContactanosController::class),
            new Route('POST', '/contactanos.php', Access::Public, GuardarContactoController::class),

            new Route('GET', self::LOGIN, Access::Public, LoginController::class),
            new Route('POST', '/administrator/bienvenido.php', Access::Public, BienvenidoController::class),
            new Route('GET', '/administrator/bienvenido.php', Access::Admin, BienvenidoController::class),

            // The panel is what bienvenido.php answers now; the vehicle table
            // it used to show has a URL of its own, and the inbox is beside it
            // in the menu.
            new Route('GET', '/administrator/vehiculos.php', Access::Admin, VehiculosController::class),
            new Route('GET', '/administrator/contactos.php', Access::Admin, ContactosController::class),
            new Route('GET', '/administrator/contacto.php', Access::Admin, ContactoDetalleController::class),
            new Route('POST', '/administrator/actionContacto.php', Access::Admin, GuardarEstadoContactoController::class),

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
