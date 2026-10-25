<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Contacto;
use Buffalo\Panel\ClaseConteo;
use Buffalo\Panel\TipoConClases;
use PDO;

/**
 * The panel's figures.
 *
 * The first grouped queries in the application: everywhere else reads whole
 * rows and counts them in PHP, which is right when the rows are then
 * displayed and wrong here, where only the totals are wanted.
 */
final readonly class PdoResumenRepository implements ResumenRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function vehiculosPorTipo(): array
    {
        // LEFT JOIN on producto, so a class nobody has filled yet still
        // reports zero instead of disappearing from the chart.
        $filas = $this->pdo->query(
            'SELECT v.id_vehiculo, v.nombre AS tipo, m.clase, COUNT(p.id_producto) AS modelos
               FROM vehiculo v
               JOIN master m ON m.id_vehiculo = v.id_vehiculo
               LEFT JOIN producto p ON p.id_master = m.id_master
           GROUP BY v.id_vehiculo, v.nombre, m.id_master, m.clase
           ORDER BY v.id_vehiculo, m.id_master',
        )->fetchAll();

        $tipos = [];
        $clases = [];

        foreach ($filas as $fila) {
            $tipos[(int) $fila['id_vehiculo']] = (string) $fila['tipo'];
            $clases[(int) $fila['id_vehiculo']][] = new ClaseConteo(
                clase: (string) $fila['clase'],
                modelos: (int) $fila['modelos'],
            );
        }

        return array_values(array_map(
            static fn (int $id): TipoConClases => new TipoConClases($tipos[$id], $clases[$id]),
            array_keys($tipos),
        ));
    }

    public function contactosPorEstado(): array
    {
        $filas = $this->pdo->query(
            'SELECT estado, COUNT(*) AS total FROM contacto GROUP BY estado ORDER BY estado',
        )->fetchAll();

        $totales = array_column($filas, 'total', 'estado');

        return [
            'nuevas' => (int) ($totales[Contacto::NUEVA] ?? 0),
            'atendidas' => (int) ($totales[Contacto::ATENDIDA] ?? 0),
        ];
    }
}
