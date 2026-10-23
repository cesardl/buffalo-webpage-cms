<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Domain\Master;
use Buffalo\Repository\PdoMasterRepository;
use Buffalo\Repository\PdoVehiculoRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PdoMasterRepository::class)]
#[CoversClass(PdoVehiculoRepository::class)]
final class PdoMasterRepositoryTest extends RepositoryTestCase
{
    private PdoMasterRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoMasterRepository($this->pdo);
    }

    public function testListsTheThreeVehicleTypes(): void
    {
        $nombres = array_map(
            static fn (object $v): string => $v->nombre,
            (new PdoVehiculoRepository($this->pdo))->findAll(),
        );

        self::assertSame(['Motocicletas', 'Mototaxis', 'Furgon'], $nombres);
    }

    public function testFindsTheClassesOfOneVehicleType(): void
    {
        $clases = array_map(
            static fn (Master $m): string => $m->clase,
            $this->repository->findByVehiculo(1),
        );

        self::assertSame(['DE PASEO', 'DE TRABAJO', 'TODO TERRENO', 'PISTERAS'], $clases);
    }

    public function testReturnsNullForAnUnknownClass(): void
    {
        self::assertNull($this->repository->find(99999));
    }

    public function testAClassWithNoProductsIsListedForAdminButNotForTheSite(): void
    {
        // Every seeded class has products, so the distinction only shows up
        // once an empty one exists. Rolled back with the transaction.
        $this->pdo
            ->prepare('INSERT INTO master (clase, id_vehiculo) VALUES (:clase, 1)')
            ->execute(['clase' => 'CLASE VACIA']);

        $todas = array_map(static fn (Master $m): string => $m->clase, $this->repository->findByVehiculo(1));
        $conProductos = array_map(static fn (Master $m): string => $m->clase, $this->repository->findConProductos(1));

        self::assertContains('CLASE VACIA', $todas);
        self::assertNotContains('CLASE VACIA', $conProductos);
    }

    public function testEachClassAppearsOnceEvenWhenItHasSeveralProducts(): void
    {
        $clases = array_map(
            static fn (Master $m): string => $m->clase,
            $this->repository->findConProductos(3),
        );

        // DE CARGA holds BF500 and BF600; a plain join would list it twice.
        self::assertSame(['DE CARGA', 'DE PASAJEROS'], $clases);
    }
}
