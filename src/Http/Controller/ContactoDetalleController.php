<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\ContactoRepository;
use Buffalo\View\Consola;
use Buffalo\View\Template;

/** One contact request, with its message in full. */
final readonly class ContactoDetalleController implements Controller
{
    public function __construct(
        private Template $template,
        private Consola $consola,
        private ContactoRepository $contactos,
    ) {
    }

    public function handle(Request $request): Response
    {
        $solicitud = $this->contactos->find($request->queryInt('id'));

        if ($solicitud === null) {
            return Response::notFound();
        }

        return Response::html($this->template->render('admin/contacto', $this->consola->pagina(
            'contactos',
            'Solicitud de ' . $solicitud->nombre,
            ['solicitud' => $solicitud],
        )));
    }
}
