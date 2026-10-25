<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Contacto\ContactoValidator;
use Buffalo\Domain\Contacto;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\ContactoRepository;

/**
 * Receives the contact form.
 *
 * Answers JSON, because the page shows the result in a dialog rather than
 * navigating away. The session check does not apply -- anyone may write to
 * this form -- but the CSRF check at the router does, so the request has to
 * come from a page this site served.
 */
final readonly class GuardarContactoController implements Controller
{
    /** A field no person sees, and therefore no person fills in. */
    private const string TRAMPA = 'web';

    public function __construct(
        private ContactoValidator $validator,
        private ContactoRepository $contactos,
    ) {
    }

    public function handle(Request $request): Response
    {
        $datos = [];

        foreach (ContactoValidator::campos() as $campo) {
            $datos[$campo] = trim($request->postString($campo));
        }

        // Whatever fills the hidden field is not a person. It is answered as
        // though it had been stored, because telling a bot what gave it away
        // is how it learns to stop doing that. Nothing is written.
        if ($request->postString(self::TRAMPA) !== '') {
            return Response::json(['ok' => true, 'datos' => $datos]);
        }

        $errores = $this->validator->errores($datos);

        if ($errores !== []) {
            return Response::json(['ok' => false, 'errores' => $errores], 422);
        }

        return Response::json([
            'ok' => true,
            'id' => $this->contactos->save(Contacto::desdeFormulario($datos)),
            'datos' => $datos,
        ]);
    }
}
