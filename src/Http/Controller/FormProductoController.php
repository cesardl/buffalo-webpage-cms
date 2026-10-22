<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\Producto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Http\Session;
use Buffalo\Repository\MasterRepository;
use Buffalo\Repository\ProductoRepository;
use Buffalo\Repository\VehiculoRepository;
use Buffalo\View\Template;

final readonly class FormProductoController implements Controller
{
    public function __construct(
        private Template $template,
        private Session $session,
        private ProductoRepository $productos,
        private MasterRepository $masters,
        private VehiculoRepository $vehiculos,
    ) {
    }

    public function handle(Request $request): Response
    {
        // The dashboard links here with ?accion=E to edit, while the "Nuevo
        // producto" link spells it ?action=N. The legacy page read 'accion'
        // only, so the new-product link worked by falling through to the
        // insert branch. Both spellings are honoured rather than silently
        // relied upon.
        $accion = $request->queryString('accion') ?: $request->queryString('action');
        $modelo = $request->queryString('mod_prod');
        $masterId = $request->queryInt('id_master');

        $producto = $accion === 'E'
            ? $this->productos->find($request->queryInt('id_producto'))
            : null;

        $master = $masterId !== 0 ? $this->masters->find($masterId) : null;

        return Response::html($this->template->render('admin/producto', [
            'titulo' => ($accion === 'E' ? 'Editar producto ' : 'Registrar producto ') . $modelo,
            'producto' => $producto,
            'masterId' => $masterId,
            'vehiculoId' => $master?->vehiculoId ?? 0,
            'vehiculos' => $this->vehiculos->findAll(),
            'masters' => $master !== null ? $this->masters->findByVehiculo($master->vehiculoId) : [],
            'zoomSlots' => Producto::ZOOM_SLOTS,
            'token' => $this->session->csrfToken(),
        ]));
    }
}
