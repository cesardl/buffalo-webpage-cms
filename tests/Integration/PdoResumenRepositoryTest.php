<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Domain\Contacto;
use Buffalo\Panel\ClaseConteo;
use Buffalo\Panel\TipoConClases;
use Buffalo\Repository\PdoContactoRepository;
use Buffalo\Repository\PdoResumenRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PdoResumenRepository::class)]
final class PdoResumenRepositoryTest extends RepositoryTestCase
{
    private PdoResumenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoResumenRepository($this->pdo);
    }

    public function testCountsTheModelsOfEveryTypeAndClass(): void
    {
        $tipos = $this->repository->vehiculosPorTipo();

        self::assertSame(
            ['Motocicletas', 'Mototaxis', 'Furgon'],
            array_map(static fn (TipoConClases $tipo): string => $tipo->tipo, $tipos),
            'the types come back in the order the chart draws them',
        );

        self::assertSame([6, 1, 3], array_map(
            static fn (TipoConClases $tipo): int => $tipo->total(),
            $tipos,
        ));
    }

    public function testBreaksEachTypeDownIntoItsClasses(): void
    {
        $motocicletas = $this->repository->vehiculosPorTipo()[0];

        $porClase = [];
        foreach ($motocicletas->clases as $clase) {
            $porClase[$clase->clase] = $clase->modelos;
        }

        self::assertSame(
            ['DE PASEO' => 1, 'DE TRABAJO' => 1, 'TODO TERRENO' => 2, 'PISTERAS' => 2],
            $porClase,
        );
    }

    public function testAClassWithNoModelsStillAppears(): void
    {
        $this->pdo->exec("INSERT INTO master (clase, id_vehiculo) VALUES ('SIN MODELOS', 1)");

        $clases = $this->repository->vehiculosPorTipo()[0]->clases;
        $vacia = array_filter(
            $clases,
            static fn (ClaseConteo $clase): bool => $clase->clase === 'SIN MODELOS',
        );

        // The LEFT JOIN is the point: an empty class is something the
        // catalogue's owner wants to see, not something to hide.
        self::assertCount(1, $vacia);
        self::assertSame(0, array_values($vacia)[0]->modelos);
    }

    public function testCountsContactRequestsByState(): void
    {
        $antes = $this->repository->contactosPorEstado();

        $contactos = new PdoContactoRepository($this->pdo);
        $id = $contactos->save(new Contacto(
            id: null,
            nombre: 'Ana',
            email: 'ana@example.pe',
            telefono: '',
            direccion: '',
            ciudad: '',
            comentarios: 'Precio del furgón.',
        ));

        self::assertSame($antes['nuevas'] + 1, $this->repository->contactosPorEstado()['nuevas']);

        $contactos->cambiarEstado($id, Contacto::ATENDIDA, null);
        $despues = $this->repository->contactosPorEstado();

        self::assertSame($antes['nuevas'], $despues['nuevas']);
        self::assertSame($antes['atendidas'] + 1, $despues['atendidas']);
    }

    public function testBothStatesAreReportedEvenWhenNoneExist(): void
    {
        $this->pdo->exec('DELETE FROM contacto');

        self::assertSame(['nuevas' => 0, 'atendidas' => 0], $this->repository->contactosPorEstado());
    }
}
