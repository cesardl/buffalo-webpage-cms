<?php

declare(strict_types=1);

namespace Buffalo\Catalogo;

use Buffalo\Repository\ColorRepository;
use Buffalo\Repository\DetalleProductoRepository;
use Buffalo\Repository\MasterRepository;
use Buffalo\Repository\ProductoRepository;

/**
 * Assembles what a public listing needs.
 *
 * The legacy pages did this with nested queries inside the markup: a loop
 * over classes ran a query per class, which ran a query per product, which
 * ran another for its colours, and the detail modal ran two more. The shape
 * is kept -- it is only ever a handful of rows -- but it lives here rather
 * than in a template, and the three near-identical pages share it instead of
 * carrying a copy each.
 */
final readonly class Catalogo
{
    public function __construct(
        private MasterRepository $masters,
        private ProductoRepository $productos,
        private ColorRepository $colores,
        private DetalleProductoRepository $detalles,
    ) {
    }

    /** @return list<Clase> */
    public function porVehiculo(int $vehiculoId): array
    {
        $clases = [];

        foreach ($this->masters->findConProductos($vehiculoId) as $master) {
            $entradas = [];

            foreach ($this->productos->findByMaster($master->id) as $producto) {
                $entradas[] = new Entrada(
                    producto: $producto,
                    combinaciones: $this->colores->findForProducto((int) $producto->id),
                    detalles: $this->detalles->findForProducto((int) $producto->id),
                );
            }

            $clases[] = new Clase($master, $entradas);
        }

        return $clases;
    }
}
