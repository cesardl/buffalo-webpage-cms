<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Panel\EstadoMedios;
use Buffalo\Panel\Medios;
use Buffalo\Repository\MasterRepository;
use Buffalo\Repository\ProductoRepository;
use Buffalo\Repository\ResumenRepository;
use Buffalo\View\Consola;
use Buffalo\View\Template;

/**
 * The vehicle maintenance, which used to be what the administrator opened
 * on. The panel took that spot, so this has a URL of its own and a place in
 * the menu.
 */
final readonly class VehiculosController implements Controller
{
    public function __construct(
        private Template $template,
        private Consola $consola,
        private ProductoRepository $productos,
        private MasterRepository $masters,
        private ResumenRepository $resumen,
        private Medios $medios,
    ) {
    }

    public function handle(Request $request): Response
    {
        $productos = $this->productos->findAll();

        return Response::html($this->template->render('admin/vehiculos', $this->consola->pagina(
            'vehiculos',
            'Vehículos',
            [
                'productos' => $productos,
                'clases' => $this->clasesPorId(),
                'medios' => $this->mediosPorProducto($productos),
            ],
        )));
    }

    /**
     * The product read does not join master, so the class names are matched
     * up here rather than in the template.
     *
     * @return array<int, string>
     */
    private function clasesPorId(): array
    {
        $clases = [];

        foreach ($this->masters->findAll() as $master) {
            $clases[$master->id] = $master->clase;
        }

        return $clases;
    }

    /**
     * @param list<\Buffalo\Domain\Producto> $productos
     *
     * @return array<int, EstadoMedios>
     */
    private function mediosPorProducto(array $productos): array
    {
        // One grouped query for every product's colour combinations rather
        // than one query per row.
        $combinaciones = $this->resumen->coloresPorProducto();
        $estados = [];

        foreach ($productos as $producto) {
            $estados[(int) $producto->id] = $this->medios->de(
                $producto,
                $combinaciones[$producto->id] ?? 0,
            );
        }

        return $estados;
    }
}
