<?php

declare(strict_types=1);

namespace Buffalo\Tests\Support;

use Buffalo\Domain\Usuario;
use Buffalo\Repository\UsuarioRepository;

final class InMemoryUsuarioRepository implements UsuarioRepository
{
    /** @var array<string, Usuario> */
    private array $usuarios = [];

    /** @var array<int, string> id => the hash it was updated to */
    public array $passwordUpdates = [];

    public function add(Usuario $usuario): void
    {
        $this->usuarios[$usuario->usuario] = $usuario;
    }

    public function findByUsername(string $usuario): ?Usuario
    {
        return $this->usuarios[$usuario] ?? null;
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->passwordUpdates[$id] = $hash;
    }
}
