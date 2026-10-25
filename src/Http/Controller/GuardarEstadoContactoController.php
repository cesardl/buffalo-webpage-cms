<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\Contacto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Http\Session;
use Buffalo\Repository\ContactoRepository;
use Buffalo\View\Consola;
use Buffalo\View\Template;

/**
 * Moves one request between waiting and handled.
 *
 * The only write the inbox makes: a request is never edited and never
 * deleted, because it is somebody's message and the record of it is the
 * point.
 */
final readonly class GuardarEstadoContactoController implements Controller
{
    public function __construct(
        private Template $template,
        private Consola $consola,
        private Session $session,
        private ContactoRepository $contactos,
    ) {
    }

    public function handle(Request $request): Response
    {
        $id = (int) $request->postString('id_contacto');
        $estado = $request->postString('estado');

        if (!in_array($estado, [Contacto::NUEVA, Contacto::ATENDIDA], true) || $this->contactos->find($id) === null) {
            return Response::notFound();
        }

        $this->contactos->cambiarEstado($id, $estado, $this->session->usuarioId());

        $atendida = $estado === Contacto::ATENDIDA;

        return Response::html($this->template->render('admin/resultado', $this->consola->pagina(
            'contactos',
            $atendida ? 'Solicitud atendida' : 'Solicitud reabierta',
            [
                'encabezado' => $atendida ? 'Solicitud atendida' : 'Solicitud reabierta',
                'mensajes' => [
                    $atendida
                        ? 'Queda registrada como atendida, con la fecha y quién la atendió.'
                        : 'Vuelve a la lista de solicitudes que esperan respuesta.',
                ],
                'volver' => 'contactos.php',
            ],
        )));
    }
}
