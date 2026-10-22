<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Color;
use Buffalo\Domain\ColorProducto;

interface ColorRepository
{
    /** @return list<Color> The palette offered in the admin dropdowns. */
    public function findAll(): array;

    /**
     * Combinations are returned fully hydrated, so the admin form can read
     * ids off them and the public listing can read hex codes, without two
     * different queries.
     *
     * @return list<ColorProducto>
     */
    public function findForProducto(int $productoId): array;

    public function save(ColorProducto $combinacion): int;
}
