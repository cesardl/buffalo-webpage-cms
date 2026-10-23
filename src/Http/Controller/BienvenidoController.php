<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Http\Session;
use Buffalo\Repository\MasterRepository;
use Buffalo\Repository\ProductoRepository;
use Buffalo\Security\Authenticator;
use Buffalo\View\Template;

/**
 * One endpoint for two jobs, as the legacy bienvenido.php had it: a POST is
 * the sign-in attempt, a GET is the dashboard. The routing table declares the
 * POST public and the GET admin-only, so the distinction lives there rather
 * than in a chain of isset() checks here.
 */
final readonly class BienvenidoController implements Controller
{
    public function __construct(
        private Template $template,
        private Session $session,
        private Authenticator $authenticator,
        private ProductoRepository $productos,
        private MasterRepository $masters,
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

        return Response::html($this->template->render('admin/dashboard', [
            'productos' => $this->productos->findAll(),
            'clases' => $this->clasesPorId(),
            'token' => $this->session->csrfToken(),
        ]));
    }

    /** @return array<int, string> */
    private function clasesPorId(): array
    {
        $clases = [];

        foreach ($this->masters->findAll() as $master) {
            $clases[$master->id] = $master->clase;
        }

        return $clases;
    }
}
