<?php

declare(strict_types=1);

namespace Buffalo\Panel;

/**
 * Everything the panel shows, in one object.
 *
 * Assembled from two grouped queries rather than counted in PHP from the
 * listing reads: the figures are all the panel needs, and loading every
 * product to count them would be the slowest way to answer "how many".
 */
final readonly class Resumen
{
    /** @param list<TipoConClases> $tipos */
    public function __construct(
        public array $tipos,
        public int $nuevas,
        public int $atendidas,
    ) {
    }

    public function totalModelos(): int
    {
        return array_sum(array_map(
            static fn (TipoConClases $tipo): int => $tipo->total(),
            $this->tipos,
        ));
    }

    public function totalClases(): int
    {
        return array_sum(array_map(
            static fn (TipoConClases $tipo): int => count($tipo->clases),
            $this->tipos,
        ));
    }

    public function totalSolicitudes(): int
    {
        return $this->nuevas + $this->atendidas;
    }
}
