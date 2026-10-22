<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\ProductoRepository;

final readonly class VehiculoDeleteController implements Controller
{
    public function __construct(private ProductoRepository $productos)
    {
    }

    public function handle(Request $request): Response
    {
        $this->productos->delete((int) $request->postString('id_producto'));

        return Response::html('<b>Se ha eliminado el vehiculo<b>');
    }
}
