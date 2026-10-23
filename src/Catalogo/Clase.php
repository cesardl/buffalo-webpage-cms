<?php

declare(strict_types=1);

namespace Buffalo\Catalogo;

use Buffalo\Domain\Master;

/** A heading on a public listing and the products filed under it. */
final readonly class Clase
{
    /** @param list<Entrada> $entradas */
    public function __construct(
        public Master $master,
        public array $entradas,
    ) {
    }
}
