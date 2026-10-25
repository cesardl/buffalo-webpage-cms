<?php

declare(strict_types=1);

namespace Buffalo\Domain;

/**
 * A message left on the public contact form.
 *
 * Write-only so far: nothing in the application reads these rows back, and
 * the administrator has no screen for them yet. The fields the form does not
 * insist on are held as empty strings rather than NULL, which is how the
 * rest of this schema says "not given".
 */
final readonly class Contacto
{
    public function __construct(
        public ?int $id,
        public string $nombre,
        public string $email,
        public string $telefono,
        public string $direccion,
        public string $ciudad,
        public string $comentarios,
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
}
