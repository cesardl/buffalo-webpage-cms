<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\MasterRepository;
use Buffalo\Repository\ProductoRepository;
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
    ) {
    }

    public function handle(Request $request): Response
    {
        return Response::html($this->template->render('admin/vehiculos', $this->consola->pagina(
            'vehiculos',
            'Vehículos',
            [
                'productos' => $this->productos->findAll(),
                'clases' => $this->clasesPorId(),
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
}
