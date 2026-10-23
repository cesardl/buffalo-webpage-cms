<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Domain\Producto;
use Buffalo\Repository\PdoProductoRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PdoProductoRepository::class)]
final class PdoProductoRepositoryTest extends RepositoryTestCase
{
    private PdoProductoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoProductoRepository($this->pdo);
    }

    public function testFindsEverySeededProductInAStableOrder(): void
    {
        $modelos = array_map(
            static fn (Producto $p): string => $p->modelo,
            $this->repository->findAll(),
        );

        // Grouped by class, then id -- the order the legacy dashboard
        // showed. It used to depend on InnoDB returning an unordered join a
        // particular way; writing to the table was enough to change it.
        self::assertSame(
            ['B5', 'B3CH', 'B15', 'B12', 'B10', 'B9', 'BF300', 'BF500',
             'BF600', 'BF500 PASAJEROS'],
            $modelos,
        );
    }

    public function testMapsZoomSlotsPositionally(): void
    {
        $producto = $this->repository->find(5);

        self::assertNotNull($producto);
        self::assertCount(Producto::ZOOM_SLOTS, $producto->fotosZoom);
        self::assertSame('images/zoom/bf300_small1.jpg', $producto->fotosZoom[0]);
        self::assertSame('images/zoom/bf300_small3.jpg', $producto->fotosZoom[2]);
    }

    public function testEmptyZoomSlotsAreKeptForFormsButSkippedForGalleries(): void
    {
        $producto = $this->repository->find(1);

        self::assertNotNull($producto);
        self::assertCount(4, $producto->fotosZoom);
        self::assertSame(['', ''], array_slice($producto->fotosZoom, 2));
        self::assertCount(2, $producto->fotosZoomPresentes());
    }

    public function testReturnsNullForAnUnknownId(): void
    {
        self::assertNull($this->repository->find(99999));
    }

    public function testFindsOnlyTheProductsOfOneClass(): void
    {
        $modelos = array_map(
            static fn (Producto $p): string => $p->modelo,
            $this->repository->findByMaster(4),
        );

        self::assertSame(['B10', 'B9'], $modelos);
    }

    public function testInsertsAndThenUpdatesTheSameRow(): void
    {
        $id = $this->repository->save(new Producto(
            id: null,
            masterId: 1,
            modelo: 'TEST-1',
            descripcion: 'original',
            fotoPrincipal: 'images/principal/x.jpg',
            fotosZoom: ['a.jpg', '', '', ''],
            fichaTecnica: 'ficha_tecnica/ficha.pdf',
        ));

        $stored = $this->repository->find($id);
        self::assertNotNull($stored);
        self::assertSame('original', $stored->descripcion);

        $returned = $this->repository->save(new Producto(
            id: $id,
            masterId: 2,
            modelo: 'TEST-1',
            descripcion: 'cambiada',
            fotoPrincipal: 'images/principal/x.jpg',
            fotosZoom: ['a.jpg', 'b.jpg', '', ''],
            fichaTecnica: 'ficha_tecnica/ficha.pdf',
        ));

        self::assertSame($id, $returned, 'An update must not create a second row.');

        $updated = $this->repository->find($id);
        self::assertNotNull($updated);
        self::assertSame('cambiada', $updated->descripcion);
        self::assertSame(2, $updated->masterId);
        self::assertSame('b.jpg', $updated->fotosZoom[1]);
    }

    public function testDeletesOnlyTheRowAsked(): void
    {
        $id = $this->repository->save(new Producto(
            id: null,
            masterId: 1,
            modelo: 'TEST-DELETE',
            descripcion: 'x',
            fotoPrincipal: '',
            fotosZoom: ['', '', '', ''],
            fichaTecnica: '',
        ));

        $this->repository->delete($id);

        self::assertNull($this->repository->find($id));
        self::assertCount(10, $this->repository->findAll());
    }

    public function testStoresTextThatWouldBreakAnInterpolatedQuery(): void
    {
        $hostile = "O'Brien \"quoted\"; DROP TABLE producto; -- ñ";

        $id = $this->repository->save(new Producto(
            id: null,
            masterId: 1,
            modelo: 'TEST-SQLI',
            descripcion: $hostile,
            fotoPrincipal: '',
            fotosZoom: ['', '', '', ''],
            fichaTecnica: '',
        ));

        $stored = $this->repository->find($id);

        self::assertNotNull($stored, 'The table should still exist.');
        self::assertSame($hostile, $stored->descripcion);
        self::assertCount(11, $this->repository->findAll());
    }
}
