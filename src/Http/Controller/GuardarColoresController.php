<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\Color;
use Buffalo\Domain\ColorProducto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\ColorRepository;
use Buffalo\View\Consola;
use Buffalo\View\Template;

final readonly class GuardarColoresController implements Controller
{
    public function __construct(
        private Template $template,
        private Consola $consola,
        private ColorRepository $colores,
    ) {
    }

    public function handle(Request $request): Response
    {
        $productoId = (int) $request->postString('id_producto');

        /** @var array<int, Color> $palette */
        $palette = [];
        foreach ($this->colores->findAll() as $color) {
            $palette[$color->id] = $color;
        }

        $guardados = 0;

        for ($i = 0; $i < FormColoresController::FILAS; $i++) {
            $id = (int) $request->postString("id{$i}");
            $principal = $palette[(int) $request->postString("color_1_{$i}")] ?? null;
            $secundario = $palette[(int) $request->postString("color_2_{$i}")] ?? null;

            // A row with no primary colour and no id is an empty slot, not a
            // deletion. That is how the legacy handler treated it.
            if ($principal === null && $id === 0) {
                continue;
            }

            $this->colores->save(new ColorProducto(
                id: $id === 0 ? null : $id,
                productoId: $productoId,
                principal: $principal,
                secundario: $secundario,
            ));
            ++$guardados;
        }

        return Response::html($this->template->render('admin/resultado', $this->consola->pagina(
            'vehiculos',
            'Colores guardados',
            [
                'encabezado' => 'Colores guardados',
                'mensajes' => ["Se guardaron {$guardados} combinación(es)."],
                'volver' => 'vehiculos.php',
            ],
        )));
    }
}
