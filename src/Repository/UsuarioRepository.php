<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Usuario;

interface UsuarioRepository
{
    /**
     * Looks the user up by name only. Checking the password is the caller's
     * job: the legacy query matched on both columns at once, which made the
     * lookup and the credential check the same indivisible step and left
     * nowhere to introduce hashing.
     */
    public function findByUsername(string $usuario): ?Usuario;
}
