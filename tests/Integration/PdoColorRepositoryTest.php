<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Domain\Color;
use Buffalo\Domain\ColorProducto;
use Buffalo\Repository\PdoColorRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PdoColorRepository::class)]
final class PdoColorRepositoryTest extends RepositoryTestCase
{
    private PdoColorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoColorRepository($this->pdo);
    }

    public function testReturnsThePaletteWithHexCodes(): void
    {
        $colores = $this->repository->findAll();

        self::assertCount(5, $colores);
        self::assertSame('rojo', $colores[0]->nombre);
        self::assertSame('ff001e', $colores[0]->codigo);
    }

    public function testHydratesBothColoursOfATwoToneCombination(): void
    {
        $combinaciones = $this->repository->findForProducto(5);

        self::assertCount(4, $combinaciones);
        self::assertSame('azul', $combinaciones[0]->principal?->nombre);
        self::assertSame('gris', $combinaciones[0]->secundario?->nombre);
        self::assertTrue($combinaciones[0]->esBicolor());
    }

    public function testASingleColourCombinationHasNoSecondColourRatherThanColourZero(): void
    {
        $combinaciones = $this->repository->findForProducto(1);

        self::assertCount(1, $combinaciones);
        self::assertSame('rojo', $combinaciones[0]->principal?->nombre);
        // The row stores 0, which is a sentinel, not a colour id.
        self::assertNull($combinaciones[0]->secundario);
        self::assertFalse($combinaciones[0]->esBicolor());
    }

    public function testRoundTripsACombinationWithoutASecondColour(): void
    {
        $palette = $this->repository->findAll();

        $id = $this->repository->save(new ColorProducto(
            id: null,
            productoId: 1,
            principal: $palette[3],
            secundario: null,
        ));

        $stored = array_values(array_filter(
            $this->repository->findForProducto(1),
            static fn (ColorProducto $c): bool => $c->id === $id,
        ));

        self::assertCount(1, $stored);
        self::assertInstanceOf(Color::class, $stored[0]->principal);
        self::assertSame('negro', $stored[0]->principal->nombre);
        self::assertNull($stored[0]->secundario);
    }

    public function testUpdatingKeepsTheSameRow(): void
    {
        $palette = $this->repository->findAll();
        $existing = $this->repository->findForProducto(1)[0];

        $returned = $this->repository->save(new ColorProducto(
            id: $existing->id,
            productoId: 1,
            principal: $palette[1],
            secundario: $palette[4],
        ));

        self::assertSame($existing->id, $returned);

        $combinaciones = $this->repository->findForProducto(1);
        self::assertCount(1, $combinaciones);
        self::assertSame('azul', $combinaciones[0]->principal?->nombre);
        self::assertSame('gris', $combinaciones[0]->secundario?->nombre);
    }
}
