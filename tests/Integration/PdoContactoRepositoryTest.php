<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Domain\Contacto;
use Buffalo\Repository\PdoContactoRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PdoContactoRepository::class)]
final class PdoContactoRepositoryTest extends RepositoryTestCase
{
    private PdoContactoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoContactoRepository($this->pdo);
    }

    private function fila(int $id): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM contacto WHERE id_contacto = :id');
        $statement->execute(['id' => $id]);

        return (array) $statement->fetch();
    }

    public function testStoresAMessageAndReturnsItsId(): void
    {
        $id = $this->repository->save(new Contacto(
            id: null,
            nombre: 'César Díaz',
            email: 'cesar@example.pe',
            telefono: '+51 999 888 777',
            direccion: 'Av. Javier Prado 123',
            ciudad: 'Lima',
            comentarios: 'Quisiera información sobre la B15.',
        ));

        self::assertGreaterThan(0, $id);

        $fila = $this->fila($id);

        self::assertSame('César Díaz', $fila['nombre']);
        self::assertSame('cesar@example.pe', $fila['email']);
        self::assertSame('+51 999 888 777', $fila['telefono']);
        self::assertSame('Av. Javier Prado 123', $fila['direccion']);
        self::assertSame('Lima', $fila['ciudad']);
        self::assertSame('Quisiera información sobre la B15.', $fila['comentarios']);
    }

    public function testTheFieldsNobodyFilledInAreStoredEmpty(): void
    {
        $id = $this->repository->save(new Contacto(
            id: null,
            nombre: 'Ana',
            email: 'ana@example.pe',
            telefono: '',
            direccion: '',
            ciudad: '',
            comentarios: 'Precio del furgón, por favor.',
        ));

        $fila = $this->fila($id);

        // Empty strings rather than NULL: the columns are NOT NULL, which is
        // how the rest of this schema says "not given".
        self::assertSame('', $fila['telefono']);
        self::assertSame('', $fila['direccion']);
        self::assertSame('', $fila['ciudad']);
    }

    public function testTheMessageIsStampedByTheServer(): void
    {
        // The database's clock, not PHP's. The two run in separate
        // containers and need not share a timezone -- here they are five
        // hours apart -- so comparing the stamp against time() would be
        // asserting something about the deployment, not about the code.
        $antes = (string) $this->pdo->query('SELECT NOW()')->fetchColumn();

        $id = $this->repository->save(new Contacto(
            id: null,
            nombre: 'Ana',
            email: 'ana@example.pe',
            telefono: '',
            direccion: '',
            ciudad: '',
            comentarios: 'Precio del furgón, por favor.',
        ));

        $despues = (string) $this->pdo->query('SELECT NOW() + INTERVAL 1 MINUTE')->fetchColumn();
        $registrado = (string) $this->fila($id)['fecha_registro'];

        self::assertGreaterThanOrEqual($antes, $registrado);
        self::assertLessThanOrEqual($despues, $registrado);
    }

    public function testFourByteCharactersSurvive(): void
    {
        // The column is utf8mb4 for this: on the utf8 the other tables use,
        // the insert would fail outright.
        $id = $this->repository->save(new Contacto(
            id: null,
            nombre: 'Ana',
            email: 'ana@example.pe',
            telefono: '',
            direccion: '',
            ciudad: '',
            comentarios: 'Me encantó la B15 🏍️',
        ));

        self::assertSame('Me encantó la B15 🏍️', $this->fila($id)['comentarios']);
    }
}
