<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Repository\PdoUsuarioRepository;
use Buffalo\Security\Authenticator;
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

    public function testTheStoredPasswordIsAHashNotClearText(): void
    {
        $usuario = $this->repository->findByUsername('root');

        self::assertNotNull($usuario);
        self::assertTrue(
            Authenticator::isHashed($usuario->storedPassword),
            'Run bin/migrate-passwords.php against this database.',
        );
        self::assertNotSame('root', $usuario->storedPassword);
    }

    public function testTheSeededAdministratorCanStillSignIn(): void
    {
        $usuario = (new Authenticator($this->repository))->attempt('root', 'root');

        self::assertNotNull($usuario);
        self::assertSame(1, $usuario->id);
    }

    public function testTheWrongPasswordIsRejectedAgainstTheRealRow(): void
    {
        self::assertNull((new Authenticator($this->repository))->attempt('root', 'wrong'));
    }

    public function testUpdatePasswordReplacesTheStoredHash(): void
    {
        // usuario is InnoDB after the migration, so this rolls back with the
        // rest of the test rather than persisting.
        $nuevo = Authenticator::hash('otra-clave');
        $this->repository->updatePassword(1, $nuevo);

        $usuario = $this->repository->findByUsername('root');

        self::assertNotNull($usuario);
        self::assertSame($nuevo, $usuario->storedPassword);
        self::assertTrue(password_verify('otra-clave', $usuario->storedPassword));
    }
}
