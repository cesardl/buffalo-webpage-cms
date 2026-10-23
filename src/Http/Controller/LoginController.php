<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Http\Session;
use Buffalo\View\Template;

/**
 * The login screen, which the legacy index.php also used as its logout
 * endpoint via ?param=1. That is kept so existing links still work.
 */
final readonly class LoginController implements Controller
{
    public function __construct(
        private Template $template,
        private Session $session,
    ) {
    }

    public function handle(Request $request): Response
    {
        $despedida = false;

        if ($request->queryString('param') !== '' && $this->session->usuario() !== null) {
            $this->session->logOut();
            $despedida = true;
        }

        return Response::html($this->template->render('admin/login', [
            'error' => $request->queryInt('error'),
            'despedida' => $despedida,
            'token' => $this->session->csrfToken(),
        ]));
    }
}
