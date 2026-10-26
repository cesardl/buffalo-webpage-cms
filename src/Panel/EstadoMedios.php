<?php

declare(strict_types=1);

namespace Buffalo\Panel;

/**
 * Whether a product has what the public listing needs to show it.
 *
 * Recorded and present are two different things: a row can name a photo
 * that is not on disk, which is exactly what four of the seeded products do
 * and what nothing in the administrator used to say out loud.
 */
final readonly class EstadoMedios
{
    public function __construct(
        public bool $principalRegistrada,
        public bool $principalPresente,
        public int $zoomsRegistrados,
        public int $zoomsPresentes,
        public int $combinaciones,
    ) {
    }

    public function principalRota(): bool
    {
        return $this->principalRegistrada && !$this->principalPresente;
    }

    public function zoomsRotos(): int
    {
        return $this->zoomsRegistrados - $this->zoomsPresentes;
    }
}
