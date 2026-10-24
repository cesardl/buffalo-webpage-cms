<?php

declare(strict_types=1);

namespace Buffalo\Contacto;

/**
 * What the contact form accepts.
 *
 * Pure, like FileValidator and for the same reason: it takes the submitted
 * strings and answers with one message per field it rejects, so the rules
 * can be tested without a request, a session or a database.
 *
 * LIMITES is also the column widths, and the template reads it for its
 * maxlength attributes, so the form and the table cannot drift apart. The
 * browser is asked to enforce them, and then they are enforced again here,
 * because the browser is not the one that has to be right.
 */
final readonly class ContactoValidator
{
    public const array LIMITES = [
        'nombre' => 100,
        'email' => 150,
        'telefono' => 30,
        'direccion' => 150,
        'ciudad' => 80,
        'comentarios' => 1000,
    ];

    /** Without these three there is nobody to answer and nothing to answer about. */
    private const array OBLIGATORIOS = ['nombre', 'email', 'comentarios'];

    private const array FALTA = [
        'nombre' => 'Ingrese su nombre.',
        'email' => 'Ingrese su correo electrónico.',
        'comentarios' => 'Escriba su consulta o pedido.',
    ];

    private const array ETIQUETAS = [
        'nombre' => 'El nombre',
        'email' => 'El correo',
        'telefono' => 'El teléfono',
        'direccion' => 'La dirección',
        'ciudad' => 'La ciudad',
        'comentarios' => 'El comentario',
    ];

    /**
     * @param array<string, string> $datos
     *
     * @return array<string, string> a message per rejected field; empty when the message may be stored
     */
    public function errores(array $datos): array
    {
        $errores = [];

        foreach (self::LIMITES as $campo => $limite) {
            $valor = trim($datos[$campo] ?? '');

            if ($valor === '') {
                if (in_array($campo, self::OBLIGATORIOS, true)) {
                    $errores[$campo] = self::FALTA[$campo];
                }

                continue;
            }

            // mb_strlen, because the column counts characters and the text is
            // Spanish: strlen would count the bytes and reject "Ñandú" early.
            if (mb_strlen($valor) > $limite) {
                $errores[$campo] = sprintf(
                    '%s no puede pasar de %d caracteres.',
                    self::ETIQUETAS[$campo],
                    $limite,
                );

                continue;
            }

            if ($campo === 'email' && filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
                $errores[$campo] = 'El correo no parece válido.';
            }

            if ($campo === 'telefono' && preg_match('/^[0-9+().\s-]{6,}$/', $valor) !== 1) {
                $errores[$campo] = 'El teléfono solo admite números, espacios y los signos + ( ) - .';
            }
        }

        return $errores;
    }

    /** @return list<string> */
    public static function campos(): array
    {
        return array_keys(self::LIMITES);
    }
}
