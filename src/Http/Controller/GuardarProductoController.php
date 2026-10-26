<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\Producto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\ProductoRepository;
use Buffalo\Upload\Uploader;
use Buffalo\View\Consola;
use Buffalo\View\Template;

final readonly class GuardarProductoController implements Controller
{
    public function __construct(
        private Template $template,
        private Consola $consola,
        private ProductoRepository $productos,
        private Uploader $uploader,
    ) {
    }

    public function handle(Request $request): Response
    {
        $errores = [];

        // An upload that was not submitted keeps the path already stored,
        // which is what the hidden v_* fields carry.
        $principal = $this->resolve($request, 'imagen', 'v_imagen', 'images/principal', $errores);

        $zooms = [];
        for ($slot = 1; $slot <= Producto::ZOOM_SLOTS; $slot++) {
            $zooms[] = $this->resolve($request, "zoom{$slot}", "v_zoom{$slot}", 'images/zoom', $errores);
        }

        $ficha = $request->postString('v_fichtec');
        $subida = $this->uploader->pdf($request->files['fichtec'] ?? null, 'ficha_tecnica');

        if ($subida->hasFailed()) {
            $errores[] = $subida->error;
        } elseif ($subida->path !== null) {
            $ficha = $subida->path;
        }

        if ($errores !== []) {
            return Response::html($this->template->render('admin/resultado', $this->consola->pagina(
                'vehiculos',
                'No se guardó el producto',
                [
                    'encabezado' => 'No se guardó el producto',
                    'mensajes' => $errores,
                    'volver' => 'vehiculos.php',
                ],
            )));
        }

        $id = $request->postString('id_producto');
        $nuevo = $id === '' || $id === '0';

        $this->productos->save(new Producto(
            id: $nuevo ? null : (int) $id,
            masterId: (int) $request->postString('master'),
            modelo: trim($request->postString('modelo')),
            descripcion: trim($request->postString('descripcion')),
            fotoPrincipal: $principal,
            fotosZoom: $zooms,
            fichaTecnica: $ficha,
        ));

        return Response::html($this->template->render('admin/resultado', $this->consola->pagina(
            'vehiculos',
            'Producto guardado',
            [
                'encabezado' => 'Producto guardado',
                'mensajes' => [$nuevo ? 'Se registró 1 producto.' : 'Se actualizó 1 producto.'],
                'volver' => 'vehiculos.php',
            ],
        )));
    }

    /** @param list<string> $errores */
    private function resolve(
        Request $request,
        string $fileField,
        string $currentField,
        string $subdirectory,
        array &$errores,
    ): string {
        $resultado = $this->uploader->image($request->files[$fileField] ?? null, $subdirectory);

        if ($resultado->hasFailed()) {
            $errores[] = $resultado->error;

            return $request->postString($currentField);
        }

        return $resultado->path ?? $request->postString($currentField);
    }
}
