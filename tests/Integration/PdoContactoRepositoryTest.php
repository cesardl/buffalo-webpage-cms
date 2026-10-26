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

    private function guardar(string $nombre = 'César Díaz', string $comentarios = 'Quisiera información sobre la B15.'): int
    {
        return $this->repository->save(new Contacto(
            id: null,
            nombre: $nombre,
            email: 'cesar@example.pe',
            telefono: '+51 999 888 777',
            direccion: 'Av. Javier Prado 123',
            ciudad: 'Lima',
            comentarios: $comentarios,
        ));
    }

    public function testStoresAMessageAndReturnsItsId(): void
    {
        $id = $this->guardar();

        self::assertGreaterThan(0, $id);

        $fila = $this->fila($id);

        self::assertSame('César Díaz', $fila['nombre']);
        self::assertSame('cesar@example.pe', $fila['email']);
        self::assertSame('+51 999 888 777', $fila['telefono']);
        self::assertSame('Av. Javier Prado 123', $fila['direccion']);
        self::assertSame('Lima', $fila['ciudad']);
        self::assertSame('Quisiera información sobre la B15.', $fila['comentarios']);
    }

    public function testAMessageArrivesWaitingForAttention(): void
    {
        $id = $this->guardar();

        self::assertSame(Contacto::NUEVA, $this->fila($id)['estado']);
        self::assertTrue($this->repository->find($id)?->esNueva());
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

        $id = $this->guardar();

        $despues = (string) $this->pdo->query('SELECT NOW() + INTERVAL 1 MINUTE')->fetchColumn();
        $registrado = (string) $this->fila($id)['fecha_registro'];

        self::assertGreaterThanOrEqual($antes, $registrado);
        self::assertLessThanOrEqual($despues, $registrado);
    }

    public function testFourByteCharactersSurvive(): void
    {
        // The column is utf8mb4 for this: on the utf8 the other tables use,
        // the insert would fail outright.
        $id = $this->guardar(comentarios: 'Me encantó la B15 🏍️');

        self::assertSame('Me encantó la B15 🏍️', $this->fila($id)['comentarios']);
    }

    public function testReadsOneMessageBack(): void
    {
        $solicitud = $this->repository->find($this->guardar());

        self::assertNotNull($solicitud);
        self::assertSame('César Díaz', $solicitud->nombre);
        self::assertSame('Lima', $solicitud->ciudad);
        self::assertNotNull($solicitud->fechaRegistro);
        self::assertNull($solicitud->fechaAtencion);
        self::assertNull($solicitud->atendidoPor);
    }

    public function testAMessageThatIsNotThereIsNull(): void
    {
        self::assertNull($this->repository->find(0));
    }

    public function testTheInboxIsNewestFirst(): void
    {
        $this->pdo->exec('DELETE FROM contacto');

        $primera = $this->guardar('Primera');
        $segunda = $this->guardar('Segunda');

        $ids = array_map(
            static fn (Contacto $solicitud): ?int => $solicitud->id,
            $this->repository->findAll(),
        );

        // Both land inside the same second, so the id is what breaks the tie.
        self::assertSame([$segunda, $primera], $ids);
    }

    public function testTheInboxCanBeFilteredByState(): void
    {
        $this->pdo->exec('DELETE FROM contacto');

        $pendiente = $this->guardar('Pendiente');
        $resuelta = $this->guardar('Resuelta');

        $this->repository->cambiarEstado($resuelta, Contacto::ATENDIDA, null);

        self::assertSame([$pendiente], array_map(
            static fn (Contacto $s): ?int => $s->id,
            $this->repository->findAll(Contacto::NUEVA),
        ));
        self::assertSame([$resuelta], array_map(
            static fn (Contacto $s): ?int => $s->id,
            $this->repository->findAll(Contacto::ATENDIDA),
        ));
        self::assertCount(2, $this->repository->findAll());
    }

    public function testAttendingAMessageRecordsWhenAndByWhom(): void
    {
        $id = $this->guardar();
        $usuarioId = (int) $this->pdo->query('SELECT id FROM usuario ORDER BY id LIMIT 1')->fetchColumn();

        $this->repository->cambiarEstado($id, Contacto::ATENDIDA, $usuarioId);

        $solicitud = $this->repository->find($id);

        self::assertNotNull($solicitud);
        self::assertFalse($solicitud->esNueva());
        self::assertNotNull($solicitud->fechaAtencion);
        // The name, not the id: that is what the screen shows.
        self::assertSame('root', $solicitud->atendidoPor);
    }

    public function testReopeningAMessageForgetsWhoClosedIt(): void
    {
        $id = $this->guardar();
        $usuarioId = (int) $this->pdo->query('SELECT id FROM usuario ORDER BY id LIMIT 1')->fetchColumn();

        $this->repository->cambiarEstado($id, Contacto::ATENDIDA, $usuarioId);
        $this->repository->cambiarEstado($id, Contacto::NUEVA, $usuarioId);

        $solicitud = $this->repository->find($id);

        self::assertNotNull($solicitud);
        self::assertTrue($solicitud->esNueva());
        // A row that is waiting again must not still claim it was handled.
        self::assertNull($solicitud->fechaAtencion);
        self::assertNull($solicitud->atendidoPor);
    }
}
