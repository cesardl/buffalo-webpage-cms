<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\DetalleProducto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\DetalleProductoRepository;
use Buffalo\Upload\Uploader;
use Buffalo\View\Consola;
use Buffalo\View\Template;

final readonly class GuardarDetallesController implements Controller
{
    public function __construct(
        private Template $template,
        private Consola $consola,
        private DetalleProductoRepository $detalles,
        private Uploader $uploader,
    ) {
    }

    public function handle(Request $request): Response
    {
        $productoId = (int) $request->postString('id_producto');
        $mensajes = [];
        $guardados = 0;

        // One source for the row count, shared with the form that posts here.
        // The legacy form and handler each had their own literal 6.
        for ($i = 0; $i < FormDetallesController::FILAS; $i++) {
            $id = (int) $request->postString("id_det_prod{$i}");

            if (isset($request->post["delete{$i}"])) {
                if ($id !== 0) {
                    $this->detalles->delete($id);
                }

                continue;
            }

            $descripcion = trim($request->postString("descripcion{$i}"));
            if ($descripcion === '') {
                continue;
            }

            $subida = $this->uploader->image($request->files["imagen{$i}"] ?? null, 'images/detalle');
            if ($subida->hasFailed()) {
                $mensajes[] = $subida->error;

                continue;
            }

            $this->detalles->save(new DetalleProducto(
                id: $id === 0 ? null : $id,
                productoId: $productoId,
                titulo: strtoupper(trim($request->postString("titulo{$i}"))),
                descripcion: $descripcion,
                fotoDetalle: $subida->path ?? $request->postString("v_imagen{$i}"),
            ));
            ++$guardados;
        }

        $mensajes[] = "Se guardaron {$guardados} detalle(s).";

        return Response::html($this->template->render('admin/resultado', $this->consola->pagina(
            'vehiculos',
            'Detalles guardados',
            [
                'encabezado' => 'Detalles guardados',
                'mensajes' => $mensajes,
                'volver' => 'vehiculos.php',
            ],
        )));
    }
}
