<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\ColorProducto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Http\Session;
use Buffalo\Repository\ColorRepository;
use Buffalo\View\Template;

final readonly class FormColoresController implements Controller
{
    /**
     * How many combinations the form offers. The legacy form and its handler
     * each had their own literal 4, so changing it meant remembering both.
     */
    public const int FILAS = 4;

    public function __construct(
        private Template $template,
        private Session $session,
        private ColorRepository $colores,
    ) {
    }

    public function handle(Request $request): Response
    {
        $productoId = $request->queryInt('id_producto');
        $existentes = $this->colores->findForProducto($productoId);

        $filas = [];
        for ($i = 0; $i < self::FILAS; $i++) {
            $filas[] = $existentes[$i] ?? new ColorProducto(
                id: null,
                productoId: $productoId,
                principal: null,
                secundario: null,
            );
        }

        return Response::html($this->template->render('admin/colores', [
            'modelo' => $request->queryString('mod_prod'),
            'productoId' => $productoId,
            'filas' => $filas,
            'palette' => $this->colores->findAll(),
            'token' => $this->session->csrfToken(),
        ]));
    }
}
