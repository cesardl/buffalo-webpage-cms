<?php

declare(strict_types=1);

namespace Buffalo\Domain;

/** A vehicle type: Motocicletas, Mototaxis, Furgon. */
final readonly class Vehiculo
{
    public function __construct(
        public int $id,
        public string $nombre,
    ) {
    }
}
