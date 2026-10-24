<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Contacto;
use PDO;

final readonly class PdoContactoRepository implements ContactoRepository
{
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
}
