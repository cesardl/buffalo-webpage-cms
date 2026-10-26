<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\DetalleProducto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\DetalleProductoRepository;
use Buffalo\View\Consola;
use Buffalo\View\Template;

final readonly class FormDetallesController implements Controller
{
    /** The legacy form and its handler each carried their own literal 6. */
    public const int FILAS = 6;

    public function __construct(
        private Template $template,
        private Consola $consola,
        private DetalleProductoRepository $detalles,
    ) {
    }

    public function handle(Request $request): Response
    {
        $productoId = $request->queryInt('id_producto');
        $existentes = $this->detalles->findForProducto($productoId);

        $filas = [];
        for ($i = 0; $i < self::FILAS; $i++) {
            $filas[] = $existentes[$i] ?? new DetalleProducto(
                id: null,
                productoId: $productoId,
                titulo: '',
                descripcion: '',
                fotoDetalle: '',
            );
        }

        $modelo = $request->queryString('mod_prod');

        return Response::html($this->template->render('admin/detalles', $this->consola->pagina(
            'vehiculos',
            'Detalles de ' . $modelo,
            [
                'modelo' => $modelo,
                'productoId' => $productoId,
                'filas' => $filas,
            ],
        )));
    }
}
