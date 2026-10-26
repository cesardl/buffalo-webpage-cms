<?php

declare(strict_types=1);

namespace Buffalo\Panel;

use Buffalo\Domain\Producto;

/**
 * Reads a product's media situation off the disk.
 *
 * The database holds paths; whether anything is behind them is a question
 * only the filesystem can answer. Ten products and five paths each is fifty
 * stat calls, which is cheaper than the page that asks for it.
 */
final readonly class Medios
{
    public function __construct(private string $directorioPublico)
    {
    }

    public function de(Producto $producto, int $combinaciones): EstadoMedios
    {
        $zooms = $producto->fotosZoomPresentes();

        return new EstadoMedios(
            principalRegistrada: $producto->fotoPrincipal !== '',
            principalPresente: $this->existe($producto->fotoPrincipal),
            zoomsRegistrados: count($zooms),
            zoomsPresentes: count(array_filter($zooms, $this->existe(...))),
            combinaciones: $combinaciones,
        );
    }

    private function existe(string $ruta): bool
    {
        return $ruta !== '' && is_file($this->directorioPublico . '/' . $ruta);
    }
}
