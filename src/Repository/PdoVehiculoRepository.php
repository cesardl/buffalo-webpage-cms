<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Vehiculo;
use PDO;

final readonly class PdoVehiculoRepository implements VehiculoRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $rows = $this->pdo
            ->query('SELECT id_vehiculo, nombre FROM vehiculo ORDER BY id_vehiculo')
            ->fetchAll();

        return array_map(
            static fn (array $row): Vehiculo => new Vehiculo(
                id: (int) $row['id_vehiculo'],
                nombre: $row['nombre'],
            ),
            $rows,
        );
    }
}
