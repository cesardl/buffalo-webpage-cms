<?php

declare(strict_types=1);

namespace Buffalo\Domain;

/**
 * One colour combination offered for a product: either a single colour or a
 * two-tone pair.
 *
 * The table stores 0 rather than NULL for "no colour", because both columns
 * are NOT NULL. That translation happens in the repository, so nothing above
 * it has to know 0 is a sentinel.
 */
final readonly class ColorProducto
{
    public function __construct(
        public ?int $id,
        public int $productoId,
        public ?Color $principal,
        public ?Color $secundario,
    ) {
    }

    public function esBicolor(): bool
    {
        return $this->principal !== null && $this->secundario !== null;
    }
}
