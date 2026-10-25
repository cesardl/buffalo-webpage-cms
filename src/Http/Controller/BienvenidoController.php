<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Http\Session;
use Buffalo\Panel\Resumen;
use Buffalo\Repository\ResumenRepository;
use Buffalo\Security\Authenticator;
use Buffalo\View\Consola;
use Buffalo\View\Template;

/**
 * One endpoint for two jobs, as the legacy bienvenido.php had it: a POST is
 * the sign-in attempt, a GET is what you land on. The routing table declares
 * the POST public and the GET admin-only, so the distinction lives there
 * rather than in a chain of isset() checks here.
 *
 * What you land on is the panel now, not the product table; that moved to
 * vehiculos.php and into the menu.
 */
final readonly class BienvenidoController implements Controller
{
    public function __construct(
        private Template $template,
        private Session $session,
        private Authenticator $authenticator,
        private Consola $consola,
        private ResumenRepository $resumen,
    ) {
    }

    public function handle(Request $request): Response
    {
        if ($request->method === 'POST' && $this->session->usuario() === null) {
            $usuario = $this->authenticator->attempt(
                $request->postString('user'),
                $request->postString('passwd'),
            );

            if ($usuario === null) {
                return Response::redirect('/administrator/index.php?error=1');
            }

            $this->session->logIn($usuario->id, $usuario->usuario);
        }

        // The same counts the menu badge uses, asked for once.
        $solicitudes = $this->consola->solicitudes();

        return Response::html($this->template->render('admin/panel', $this->consola->pagina(
            '',
            'Panel',
            [
                'resumen' => new Resumen(
                    tipos: $this->resumen->vehiculosPorTipo(),
                    nuevas: $solicitudes['nuevas'],
                    atendidas: $solicitudes['atendidas'],
                ),
            ],
        )));
    }
}
