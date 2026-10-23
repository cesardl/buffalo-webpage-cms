<?php

declare(strict_types=1);

namespace Buffalo\Domain;

/**
 * A class of vehicle within a type -- "DE PASEO", "TODO TERRENO", "DE CARGA".
 * The table is called master; clase is what it is actually called in the UI.
 */
final readonly class Master
{
    public function __construct(
        public int $id,
        public string $clase,
        public int $vehiculoId,
    ) {
    }
}
