<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\Contacto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\ContactoRepository;
use Buffalo\View\Consola;
use Buffalo\View\Template;

/**
 * The contact inbox.
 *
 * It opens on the requests nobody has answered yet, because that is the only
 * list anyone needs to act on; the other two filters are there to look
 * something up.
 */
final readonly class ContactosController implements Controller
{
    private const array FILTROS = [
        'nuevas' => Contacto::NUEVA,
        'atendidas' => Contacto::ATENDIDA,
        'todas' => null,
    ];

    public function __construct(
        private Template $template,
        private Consola $consola,
        private ContactoRepository $contactos,
    ) {
    }

    public function handle(Request $request): Response
    {
        $filtro = $request->queryString('estado', 'nuevas');

        if (!array_key_exists($filtro, self::FILTROS)) {
            $filtro = 'nuevas';
        }

        return Response::html($this->template->render('admin/contactos', $this->consola->pagina(
            'contactos',
            'Solicitudes de contacto',
            [
                'solicitudes' => $this->contactos->findAll(self::FILTROS[$filtro]),
                'filtro' => $filtro,
            ],
        )));
    }
}
