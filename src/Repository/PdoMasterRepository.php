<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Master;
use PDO;

final readonly class PdoMasterRepository implements MasterRepository
{
    private const string COLUMNS = 'id_master, clase, id_vehiculo';

    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $id): ?Master
    {
        $statement = $this->pdo->prepare(
            'SELECT ' . self::COLUMNS . ' FROM master WHERE id_master = :id',
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : self::toMaster($row);
    }

    public function findAll(): array
    {
        $rows = $this->pdo
            ->query('SELECT ' . self::COLUMNS . ' FROM master ORDER BY id_master')
            ->fetchAll();

        return array_map(self::toMaster(...), $rows);
    }

    public function findByVehiculo(int $vehiculoId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT ' . self::COLUMNS . ' FROM master
             WHERE id_vehiculo = :vehiculo
             ORDER BY id_master',
        );
        $statement->execute(['vehiculo' => $vehiculoId]);

        return array_map(self::toMaster(...), $statement->fetchAll());
    }

    public function findConProductos(int $vehiculoId): array
    {
        // The legacy page used GROUP BY to the same end; DISTINCT says what
        // is actually meant, which is "no duplicate class per product".
        $statement = $this->pdo->prepare(
            'SELECT DISTINCT m.id_master, m.clase, m.id_vehiculo
             FROM master m
             INNER JOIN producto p ON p.id_master = m.id_master
             WHERE m.id_vehiculo = :vehiculo
             ORDER BY m.id_master',
        );
        $statement->execute(['vehiculo' => $vehiculoId]);

        return array_map(self::toMaster(...), $statement->fetchAll());
    }

    /** @param array<string, mixed> $row */
    private static function toMaster(array $row): Master
    {
        return new Master(
            id: (int) $row['id_master'],
            clase: (string) $row['clase'],
            vehiculoId: (int) $row['id_vehiculo'],
        );
    }
}
