<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Domain\DetalleProducto;
use Buffalo\Repository\PdoDetalleProductoRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PdoDetalleProductoRepository::class)]
final class PdoDetalleProductoRepositoryTest extends RepositoryTestCase
{
    private PdoDetalleProductoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoDetalleProductoRepository($this->pdo);
    }

    public function testFindsTheBulletsOfOneProductInOrder(): void
    {
        $detalles = $this->repository->findForProducto(3);

        self::assertCount(3, $detalles);
        self::assertSame('TANQUE DE CARBONO', $detalles[0]->titulo);
        self::assertSame('images/detalle/productos_img1_small.jpg', $detalles[0]->fotoDetalle);
    }

    public function testAProductWithNoBulletsReturnsAnEmptyList(): void
    {
        // B12. The legacy DAO returned an undefined variable here rather
        // than an empty array, which is why the callers all used count().
        self::assertSame([], $this->repository->findForProducto(4));
    }

    public function testBulletsMayHaveNoTitle(): void
    {
        $detalles = $this->repository->findForProducto(1);

        self::assertCount(3, $detalles);
        self::assertSame('', $detalles[0]->titulo);
        self::assertStringContainsString('Chapa', $detalles[0]->descripcion);
    }

    public function testInsertsUpdatesAndDeletes(): void
    {
        $id = $this->repository->save(new DetalleProducto(
            id: null,
            productoId: 4,
            titulo: 'NUEVO',
            descripcion: 'descripcion original',
            fotoDetalle: 'images/detalle/x.jpg',
        ));

        self::assertCount(1, $this->repository->findForProducto(4));

        $returned = $this->repository->save(new DetalleProducto(
            id: $id,
            productoId: 4,
            titulo: 'EDITADO',
            descripcion: 'descripcion nueva',
            fotoDetalle: 'images/detalle/y.jpg',
        ));

        self::assertSame($id, $returned);

        $detalles = $this->repository->findForProducto(4);
        self::assertCount(1, $detalles, 'An update must not create a second row.');
        self::assertSame('EDITADO', $detalles[0]->titulo);

        $this->repository->delete($id);
        self::assertSame([], $this->repository->findForProducto(4));
    }

    public function testAccentedDescriptionsRoundTripIntact(): void
    {
        $texto = 'Diseño ergonómico con suspensión hidráulica';

        $id = $this->repository->save(new DetalleProducto(
            id: null,
            productoId: 4,
            titulo: 'ACENTOS',
            descripcion: $texto,
            fotoDetalle: '',
        ));

        $stored = $this->repository->findForProducto(4);

        self::assertCount(1, $stored);
        self::assertSame($id, $stored[0]->id);
        self::assertSame($texto, $stored[0]->descripcion);
    }
}
