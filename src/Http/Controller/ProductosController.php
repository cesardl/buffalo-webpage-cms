<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Catalogo\Catalogo;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\View\Template;

/**
 * The three public listings.
 *
 * They were three files differing by one hardcoded vehicle id and a heading,
 * which meant every change had to be made three times. The ids were spread
 * across those files with nothing saying what they meant; here they are in
 * one table.
 */
final readonly class ProductosController implements Controller
{
    private const array PAGINAS = [
        '/productos_motocicleta.php' => [1, 'Motocicletas'],
        '/productos_mototaxis.php' => [2, 'Mototaxis'],
        '/productos_furgon.php' => [3, 'Furgones'],
    ];

    public function __construct(
        private Template $template,
        private Catalogo $catalogo,
    ) {
    }

    public function handle(Request $request): Response
    {
        [$vehiculoId, $titulo] = self::PAGINAS[$request->path] ?? [0, ''];

        if ($vehiculoId === 0) {
            return Response::notFound();
        }

        return Response::html($this->template->render('publico/productos', [
            'titulo' => $titulo,
            'clases' => $this->catalogo->porVehiculo($vehiculoId),
        ]));
    }

    /** @return list<string> */
    public static function paths(): array
    {
        return array_keys(self::PAGINAS);
    }
}
