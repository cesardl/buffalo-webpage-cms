<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\DetalleProducto;

interface DetalleProductoRepository
{
    /** @return list<DetalleProducto> */
    public function findForProducto(int $productoId): array;

    public function save(DetalleProducto $detalle): int;

    public function delete(int $id): void;
}
