<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Panel\TipoConClases;

interface ResumenRepository
{
    /** @return list<TipoConClases> every type, with its classes and their model counts */
    public function vehiculosPorTipo(): array;

    /** @return array{nuevas: int, atendidas: int} */
    public function contactosPorEstado(): array;

    /** @return array<int, int> colour combinations per product id, present only where there are any */
    public function coloresPorProducto(): array;
}
