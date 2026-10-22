<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Master;

interface MasterRepository
{
    public function find(int $id): ?Master;

    /** @return list<Master> Every class, for callers that need to index them by id. */
    public function findAll(): array;

    /** @return list<Master> */
    public function findByVehiculo(int $vehiculoId): array;

    /**
     * Only the classes that actually have products, which is what the public
     * listings show -- an empty class would render as a heading with nothing
     * under it.
     *
     * @return list<Master>
     */
    public function findConProductos(int $vehiculoId): array;
}
