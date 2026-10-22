<?php

declare(strict_types=1);

namespace Buffalo\Tests\Unit\Security;

use Buffalo\Domain\Usuario;
use Buffalo\Security\Authenticator;
use Buffalo\Tests\Support\InMemoryUsuarioRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Authenticator::class)]
final class AuthenticatorTest extends TestCase
{
    private InMemoryUsuarioRepository $usuarios;
    private Authenticator $authenticator;

    protected function setUp(): void
    {
        $this->usuarios = new InMemoryUsuarioRepository();
        $this->authenticator = new Authenticator($this->usuarios);
    }

    public function testAcceptsTheRightPasswordAgainstAHash(): void
    {
        $this->givenUser('root', Authenticator::hash('s3cret'));

        $usuario = $this->authenticator->attempt('root', 's3cret');

        self::assertNotNull($usuario);
        self::assertSame('root', $usuario->usuario);
    }

    public function testRejectsTheWrongPassword(): void
    {
        $this->givenUser('root', Authenticator::hash('s3cret'));

        self::assertNull($this->authenticator->attempt('root', 'wrong'));
    }

    public function testRejectsAnUnknownUser(): void
    {
        self::assertNull($this->authenticator->attempt('nobody', 'whatever'));
    }

    public function testTheOldInjectionStringAuthenticatesNobody(): void
    {
        $this->givenUser('root', Authenticator::hash('s3cret'));

        self::assertNull($this->authenticator->attempt("root' OR '1'='1", 'x'));
        self::assertNull($this->authenticator->attempt('root', "' OR '1'='1"));
    }

    public function testUpgradesARowStillHoldingClearText(): void
    {
        // What a restored pre-migration dump looks like.
        $this->givenUser('root', 'root');

        $usuario = $this->authenticator->attempt('root', 'root');

        self::assertNotNull($usuario, 'The admin should not be locked out by an old dump.');
        self::assertArrayHasKey(1, $this->usuarios->passwordUpdates);

        $stored = $this->usuarios->passwordUpdates[1];
        self::assertTrue(Authenticator::isHashed($stored));
        self::assertTrue(password_verify('root', $stored));
    }

    public function testDoesNotUpgradeOnAFailedClearTextAttempt(): void
    {
        $this->givenUser('root', 'root');

        self::assertNull($this->authenticator->attempt('root', 'wrong'));
        self::assertSame([], $this->usuarios->passwordUpdates);
    }

    public function testRecognisesWhatIsAndIsNotAHash(): void
    {
        self::assertTrue(Authenticator::isHashed(Authenticator::hash('x')));
        self::assertFalse(Authenticator::isHashed('root'));
        self::assertFalse(Authenticator::isHashed(''));
    }

    public function testHashesAreSaltedSoTwoUsersSharingAPasswordDifferOnDisk(): void
    {
        self::assertNotSame(Authenticator::hash('same'), Authenticator::hash('same'));
    }

    private function givenUser(string $nombre, string $stored): void
    {
        $this->usuarios->add(new Usuario(id: 1, usuario: $nombre, storedPassword: $stored));
    }
}
