<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Contacto;
use PDO;

final readonly class PdoContactoRepository implements ContactoRepository
{
    /**
     * The admin who attended a message is read as a name, not an id: that is
     * what the screen shows, and nothing else asks for it.
     */
    private const string SELECT =
        'SELECT c.id_contacto, c.nombre, c.email, c.telefono, c.direccion, c.ciudad,
                c.comentarios, c.estado, c.fecha_registro, c.fecha_atencion,
                u.usuario AS atendido_por
           FROM contacto c
           LEFT JOIN usuario u ON u.id = c.atendido_por';

    public function __construct(private PDO $pdo)
    {
    }

    public function save(Contacto $contacto): int
    {
        // fecha_registro is the server's clock rather than the caller's: the
        // only other candidate is a timestamp from the browser, which the
        // sender chooses. It is the *database* server's clock, in whatever
        // timezone that machine keeps -- here it runs five hours behind the
        // application container, which matters the day these are read back.
        $statement = $this->pdo->prepare(
            'INSERT INTO contacto
                    (nombre, email, telefono, direccion, ciudad, comentarios, fecha_registro)
             VALUES (:nombre, :email, :telefono, :direccion, :ciudad, :comentarios, NOW())',
        );
        $statement->execute([
            'nombre' => $contacto->nombre,
            'email' => $contacto->email,
            'telefono' => $contacto->telefono,
            'direccion' => $contacto->direccion,
            'ciudad' => $contacto->ciudad,
            'comentarios' => $contacto->comentarios,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(?string $estado = null): array
    {
        // Newest first: an inbox is read from the top, and the id breaks ties
        // for two messages that arrive inside the same second.
        $sql = self::SELECT
            . ($estado === null ? '' : ' WHERE c.estado = :estado')
            . ' ORDER BY c.fecha_registro DESC, c.id_contacto DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($estado === null ? [] : ['estado' => $estado]);

        return array_map(self::toContacto(...), $statement->fetchAll());
    }

    public function find(int $id): ?Contacto
    {
        $statement = $this->pdo->prepare(self::SELECT . ' WHERE c.id_contacto = :id');
        $statement->execute(['id' => $id]);

        $fila = $statement->fetch();

        return $fila === false ? null : self::toContacto($fila);
    }

    public function cambiarEstado(int $id, string $estado, ?int $usuarioId): void
    {
        $atendida = $estado === Contacto::ATENDIDA;

        $statement = $this->pdo->prepare(
            'UPDATE contacto
                SET estado = :estado,
                    fecha_atencion = ' . ($atendida ? 'NOW()' : 'NULL') . ',
                    atendido_por = :usuario
              WHERE id_contacto = :id',
        );
        $statement->execute([
            'estado' => $estado,
            'usuario' => $atendida ? $usuarioId : null,
            'id' => $id,
        ]);
    }

    /** @param array<string, mixed> $fila */
    private static function toContacto(array $fila): Contacto
    {
        return new Contacto(
            id: (int) $fila['id_contacto'],
            nombre: (string) $fila['nombre'],
            email: (string) $fila['email'],
            telefono: (string) $fila['telefono'],
            direccion: (string) $fila['direccion'],
            ciudad: (string) $fila['ciudad'],
            comentarios: (string) $fila['comentarios'],
            estado: (string) $fila['estado'],
            fechaRegistro: (string) $fila['fecha_registro'],
            fechaAtencion: $fila['fecha_atencion'] === null ? null : (string) $fila['fecha_atencion'],
            atendidoPor: $fila['atendido_por'] === null ? null : (string) $fila['atendido_por'],
        );
    }
}
