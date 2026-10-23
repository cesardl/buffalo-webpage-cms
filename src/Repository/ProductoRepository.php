<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Producto;

interface ProductoRepository
{
    /** @return list<Producto> */
    public function findAll(): array;

    public function find(int $id): ?Producto;

    /** @return list<Producto> */
    public function findByMaster(int $masterId): array;

    /** @return int The id of the stored product, new or existing. */
    public function save(Producto $producto): int;

    public function delete(int $id): void;
}
