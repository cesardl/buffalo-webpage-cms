<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Usuario;
use PDO;

final readonly class PdoUsuarioRepository implements UsuarioRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUsername(string $usuario): ?Usuario
    {
        $statement = $this->pdo->prepare(
            'SELECT id, usuario, passwd FROM usuario WHERE usuario = :usuario LIMIT 1',
        );
        $statement->execute(['usuario' => $usuario]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->toUsuario($row);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $statement = $this->pdo->prepare('UPDATE usuario SET passwd = :passwd WHERE id = :id');
        $statement->execute(['passwd' => $hash, 'id' => $id]);
    }

    /** @param array<string, mixed> $row */
    private function toUsuario(array $row): Usuario
    {
        return new Usuario(
            id: (int) $row['id'],
            usuario: (string) $row['usuario'],
            storedPassword: (string) $row['passwd'],
        );
    }
}
