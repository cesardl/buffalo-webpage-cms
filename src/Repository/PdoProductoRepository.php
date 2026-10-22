<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Producto;
use PDO;

final readonly class PdoProductoRepository implements ProductoRepository
{
    private const string COLUMNS = 'id_producto, id_master, modelo, descripcion,
             foto_principal, foto_zoom_1, foto_zoom_2, foto_zoom_3, foto_zoom_4,
             ficha_tecnica';

    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $rows = $this->pdo
            // Grouped by class, then by id. That is the order the admin
            // dashboard has always shown -- it just used to come out that way
            // by accident, from an unordered join driven off master.
            ->query('SELECT ' . self::COLUMNS . ' FROM producto
                     ORDER BY id_master, id_producto')
            ->fetchAll();

        return array_map(self::toProducto(...), $rows);
    }

    public function find(int $id): ?Producto
    {
        $statement = $this->pdo->prepare(
            'SELECT ' . self::COLUMNS . ' FROM producto WHERE id_producto = :id',
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : self::toProducto($row);
    }

    public function findByMaster(int $masterId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT ' . self::COLUMNS . ' FROM producto
             WHERE id_master = :master
             ORDER BY id_producto',
        );
        $statement->execute(['master' => $masterId]);

        return array_map(self::toProducto(...), $statement->fetchAll());
    }

    public function save(Producto $producto): int
    {
        return $producto->id === null
            ? $this->insert($producto)
            : $this->update($producto);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM producto WHERE id_producto = :id');
        $statement->execute(['id' => $id]);
    }

    private function insert(Producto $producto): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO producto
                (modelo, descripcion, foto_principal, foto_zoom_1, foto_zoom_2,
                 foto_zoom_3, foto_zoom_4, ficha_tecnica, id_master)
             VALUES
                (:modelo, :descripcion, :foto_principal, :zoom1, :zoom2,
                 :zoom3, :zoom4, :ficha_tecnica, :master)',
        );
        $statement->execute(self::toParameters($producto));

        return (int) $this->pdo->lastInsertId();
    }

    private function update(Producto $producto): int
    {
        $statement = $this->pdo->prepare(
            'UPDATE producto SET
                modelo = :modelo, descripcion = :descripcion,
                foto_principal = :foto_principal,
                foto_zoom_1 = :zoom1, foto_zoom_2 = :zoom2,
                foto_zoom_3 = :zoom3, foto_zoom_4 = :zoom4,
                ficha_tecnica = :ficha_tecnica, id_master = :master
             WHERE id_producto = :id',
        );
        $statement->execute(self::toParameters($producto) + ['id' => $producto->id]);

        return $producto->id;
    }

    /** @return array<string, mixed> */
    private static function toParameters(Producto $producto): array
    {
        return [
            'modelo' => $producto->modelo,
            'descripcion' => $producto->descripcion,
            'foto_principal' => $producto->fotoPrincipal,
            'zoom1' => $producto->fotosZoom[0],
            'zoom2' => $producto->fotosZoom[1],
            'zoom3' => $producto->fotosZoom[2],
            'zoom4' => $producto->fotosZoom[3],
            'ficha_tecnica' => $producto->fichaTecnica,
            'master' => $producto->masterId,
        ];
    }

    /** @param array<string, mixed> $row */
    private static function toProducto(array $row): Producto
    {
        return new Producto(
            id: (int) $row['id_producto'],
            masterId: (int) $row['id_master'],
            modelo: (string) $row['modelo'],
            descripcion: (string) $row['descripcion'],
            fotoPrincipal: (string) $row['foto_principal'],
            fotosZoom: [
                (string) $row['foto_zoom_1'],
                (string) $row['foto_zoom_2'],
                (string) $row['foto_zoom_3'],
                (string) $row['foto_zoom_4'],
            ],
            fichaTecnica: (string) $row['ficha_tecnica'],
        );
    }
}
