<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Contacto\ContactoValidator;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Http\Session;
use Buffalo\View\Template;

/**
 * The contact page.
 *
 * It used to be a static file, and had to stop being one: every POST is
 * checked against the session's CSRF token, and a file served off disk has
 * no way to carry one. The URL is unchanged, so what is linked and
 * bookmarked still works.
 */
final readonly class ContactanosController implements Controller
{
    public function __construct(
        private Template $template,
        private Session $session,
    ) {
    }

    public function handle(Request $request): Response
    {
        return Response::html($this->template->render('publico/contactanos', [
            'token' => $this->session->csrfToken(),
            'limites' => ContactoValidator::LIMITES,
        ]));
    }
}
