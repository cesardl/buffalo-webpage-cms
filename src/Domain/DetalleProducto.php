<?php

declare(strict_types=1);

namespace Buffalo\Domain;

/** One selling-point bullet shown on a product's detail modal. */
final readonly class DetalleProducto
{
    public function __construct(
        public ?int $id,
        public int $productoId,
        public string $titulo,
        public string $descripcion,
        public string $fotoDetalle,
    ) {
    }
}
