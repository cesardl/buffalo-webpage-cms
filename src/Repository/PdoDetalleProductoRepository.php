<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\DetalleProducto;
use PDO;

final readonly class PdoDetalleProductoRepository implements DetalleProductoRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findForProducto(int $productoId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id_detalle_producto, id_producto, titulo, descripcion, foto_detalle
             FROM detalleproducto
             WHERE id_producto = :producto
             ORDER BY id_detalle_producto',
        );
        $statement->execute(['producto' => $productoId]);

        return array_map(
            static fn (array $row): DetalleProducto => new DetalleProducto(
                id: (int) $row['id_detalle_producto'],
                productoId: (int) $row['id_producto'],
                titulo: (string) $row['titulo'],
                descripcion: (string) $row['descripcion'],
                fotoDetalle: (string) $row['foto_detalle'],
            ),
            $statement->fetchAll(),
        );
    }

    public function save(DetalleProducto $detalle): int
    {
        $parameters = [
            'titulo' => $detalle->titulo,
            'descripcion' => $detalle->descripcion,
            'foto_detalle' => $detalle->fotoDetalle,
        ];

        if ($detalle->id === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO detalleproducto (titulo, descripcion, foto_detalle, id_producto)
                 VALUES (:titulo, :descripcion, :foto_detalle, :producto)',
            );
            $statement->execute($parameters + ['producto' => $detalle->productoId]);

            return (int) $this->pdo->lastInsertId();
        }

        $statement = $this->pdo->prepare(
            'UPDATE detalleproducto
                SET titulo = :titulo, descripcion = :descripcion, foto_detalle = :foto_detalle
             WHERE id_detalle_producto = :id',
        );
        $statement->execute($parameters + ['id' => $detalle->id]);

        return $detalle->id;
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM detalleproducto WHERE id_detalle_producto = :id',
        );
        $statement->execute(['id' => $id]);
    }
}
