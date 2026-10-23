<?php

declare(strict_types=1);

namespace Buffalo\Tests\Unit;

use Buffalo\Domain\Producto;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Producto::class)]
final class ProductoTest extends TestCase
{
    public function testRejectsAWrongNumberOfZoomSlots(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected 4 zoom slots, got 2');

        self::make(['a.jpg', 'b.jpg']);
    }

    public function testKeepsEmptySlotsSoFormsStayPositional(): void
    {
        $producto = self::make(['a.jpg', '', 'c.jpg', '']);

        self::assertSame(['a.jpg', '', 'c.jpg', ''], $producto->fotosZoom);
    }

    public function testGalleriesSeeOnlyTheFilledSlotsReindexed(): void
    {
        $producto = self::make(['a.jpg', '', 'c.jpg', '']);

        self::assertSame(['a.jpg', 'c.jpg'], $producto->fotosZoomPresentes());
    }

    public function testAProductWithNoZoomImagesHasAnEmptyGallery(): void
    {
        self::assertSame([], self::make(['', '', '', ''])->fotosZoomPresentes());
    }

    /** @param list<string> $fotosZoom */
    private static function make(array $fotosZoom): Producto
    {
        return new Producto(
            id: null,
            masterId: 1,
            modelo: 'B5',
            descripcion: 'x',
            fotoPrincipal: 'p.jpg',
            fotosZoom: $fotosZoom,
            fichaTecnica: 'f.pdf',
        );
    }
}
