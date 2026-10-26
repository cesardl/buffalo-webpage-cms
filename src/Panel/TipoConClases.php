<?php

declare(strict_types=1);

namespace Buffalo\Panel;

/**
 * A vehicle type with its classes underneath it.
 *
 * This is the shape the panel's bar chart draws: one bar per type, divided
 * into its classes, so the hierarchy is the chart rather than a note beside
 * it. A class with no models still appears, because an empty class is
 * something the catalogue's owner wants to see.
 */
final readonly class TipoConClases
{
    /** @param list<ClaseConteo> $clases */
    public function __construct(
        public string $tipo,
        public array $clases,
    ) {
    }

    public function total(): int
    {
        return array_sum(array_map(
            static fn (ClaseConteo $conteo): int => $conteo->modelos,
            $this->clases,
        ));
    }
}
