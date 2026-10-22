<?php

declare(strict_types=1);

namespace Buffalo\Catalogo;

use Buffalo\Domain\ColorProducto;
use Buffalo\Domain\DetalleProducto;
use Buffalo\Domain\Producto;

/** One product on a public listing, with everything that page draws for it. */
final readonly class Entrada
{
    /**
     * @param list<ColorProducto> $combinaciones
     * @param list<DetalleProducto> $detalles
     */
    public function __construct(
        public Producto $producto,
        public array $combinaciones,
        public array $detalles,
    ) {
    }
}
