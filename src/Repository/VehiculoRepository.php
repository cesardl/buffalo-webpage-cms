<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Vehiculo;

interface VehiculoRepository
{
    /** @return list<Vehiculo> */
    public function findAll(): array;
}
