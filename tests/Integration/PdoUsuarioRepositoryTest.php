<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Repository\PdoUsuarioRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PdoUsuarioRepository::class)]
final class PdoUsuarioRepositoryTest extends RepositoryTestCase
{
    private PdoUsuarioRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoUsuarioRepository($this->pdo);
    }

    public function testFindsTheSeededAdministrator(): void
    {
        $usuario = $this->repository->findByUsername('root');

        self::assertNotNull($usuario);
        self::assertSame(1, $usuario->id);
        self::assertSame('root', $usuario->usuario);
    }

    public function testReturnsNullForAnUnknownUser(): void
    {
        self::assertNull($this->repository->findByUsername('nobody'));
    }

    public function testTheClassicLoginBypassNoLongerMatchesAnyone(): void
    {
        // The legacy login interpolated both fields straight into the query.
        self::assertNull($this->repository->findByUsername("root' OR '1'='1"));
        self::assertNull($this->repository->findByUsername("' OR 1=1 -- "));
    }

    public function testTheStoredPasswordIsReturnedUnverified(): void
    {
        // Phase 4 replaces this with a hash and an authenticator; the point
        // here is only that looking a user up and checking their password are
        // now two separate steps.
        $usuario = $this->repository->findByUsername('root');

        self::assertNotNull($usuario);
        self::assertSame('root', $usuario->storedPassword);
    }
}
