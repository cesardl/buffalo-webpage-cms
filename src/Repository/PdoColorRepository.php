<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Color;
use Buffalo\Domain\ColorProducto;
use PDO;

final readonly class PdoColorRepository implements ColorRepository
{
    /**
     * The columns are NOT NULL, so "no colour" is stored as 0. Nothing above
     * this class should have to know that, so it is translated here.
     */
    private const int SIN_COLOR = 0;

    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $rows = $this->pdo
            ->query('SELECT id_color, nombre, codigo FROM color ORDER BY id_color')
            ->fetchAll();

        return array_map(
            static fn (array $row): Color => new Color(
                id: (int) $row['id_color'],
                nombre: (string) $row['nombre'],
                codigo: (string) $row['codigo'],
            ),
            $rows,
        );
    }

    public function findForProducto(int $productoId): array
    {
        // LEFT JOIN rather than INNER: a combination with no second colour
        // stores 0, which matches no row in color, and an INNER JOIN would
        // drop the whole combination instead of just its second colour.
        $statement = $this->pdo->prepare(
            'SELECT cp.id, cp.id_producto,
                    c1.id_color AS principal_id, c1.nombre AS principal_nombre,
                    c1.codigo AS principal_codigo,
                    c2.id_color AS secundario_id, c2.nombre AS secundario_nombre,
                    c2.codigo AS secundario_codigo
             FROM colorproducto cp
             LEFT JOIN color c1 ON c1.id_color = cp.id_color_1
             LEFT JOIN color c2 ON c2.id_color = cp.id_color_2
             WHERE cp.id_producto = :producto
             ORDER BY cp.id',
        );
        $statement->execute(['producto' => $productoId]);

        return array_map(
            static fn (array $row): ColorProducto => new ColorProducto(
                id: (int) $row['id'],
                productoId: (int) $row['id_producto'],
                principal: self::toColor($row, 'principal'),
                secundario: self::toColor($row, 'secundario'),
            ),
            $statement->fetchAll(),
        );
    }

    public function save(ColorProducto $combinacion): int
    {
        $parameters = [
            'color1' => $combinacion->principal?->id ?? self::SIN_COLOR,
            'color2' => $combinacion->secundario?->id ?? self::SIN_COLOR,
        ];

        if ($combinacion->id === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO colorproducto (id_producto, id_color_1, id_color_2)
                 VALUES (:producto, :color1, :color2)',
            );
            $statement->execute($parameters + ['producto' => $combinacion->productoId]);

            return (int) $this->pdo->lastInsertId();
        }

        $statement = $this->pdo->prepare(
            'UPDATE colorproducto SET id_color_1 = :color1, id_color_2 = :color2
             WHERE id = :id',
        );
        $statement->execute($parameters + ['id' => $combinacion->id]);

        return $combinacion->id;
    }

    /** @param array<string, mixed> $row */
    private static function toColor(array $row, string $prefix): ?Color
    {
        if ($row["{$prefix}_id"] === null) {
            return null;
        }

        return new Color(
            id: (int) $row["{$prefix}_id"],
            nombre: (string) $row["{$prefix}_nombre"],
            codigo: (string) $row["{$prefix}_codigo"],
        );
    }
}
