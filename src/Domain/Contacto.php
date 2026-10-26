<?php

declare(strict_types=1);

namespace Buffalo\Domain;

/**
 * A message left on the public contact form.
 *
 * The fields the form does not insist on are held as empty strings rather
 * than NULL, which is how the rest of this schema says "not given".
 *
 * Everything after `comentarios` is read back rather than submitted: the
 * form fills the first seven and the defaults cover the rest, so
 * `desdeFormulario()` stays a seven-field constructor call. The dates are
 * strings as MySQL returns them, because they are the *database* server's
 * clock and parsing them into PHP's timezone would silently shift them.
 */
final readonly class Contacto
{
    public const string NUEVA = 'nueva';
    public const string ATENDIDA = 'atendida';

    public function __construct(
        public ?int $id,
        public string $nombre,
        public string $email,
        public string $telefono,
        public string $direccion,
        public string $ciudad,
        public string $comentarios,
        public string $estado = self::NUEVA,
        public ?string $fechaRegistro = null,
        public ?string $fechaAtencion = null,
        public ?string $atendidoPor = null,
    ) {
    }

    /** @param array<string, string> $datos */
    public static function desdeFormulario(array $datos): self
    {
        return new self(
            id: null,
            nombre: $datos['nombre'] ?? '',
            email: $datos['email'] ?? '',
            telefono: $datos['telefono'] ?? '',
            direccion: $datos['direccion'] ?? '',
            ciudad: $datos['ciudad'] ?? '',
            comentarios: $datos['comentarios'] ?? '',
        );
    }

    public function esNueva(): bool
    {
        return $this->estado === self::NUEVA;
    }
}
