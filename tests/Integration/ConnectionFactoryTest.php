<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Config;
use Buffalo\Database\ConnectionFactory;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConnectionFactory::class)]
final class ConnectionFactoryTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        if (getenv('BUFFALO_DB_HOST') === false) {
            self::markTestSkipped('No BUFFALO_DB_* environment configured.');
        }

        $this->pdo = (new ConnectionFactory(Config::fromEnvironment()))->create();
    }

    public function testConnectsAndReadsTheSeededCatalogue(): void
    {
        $count = $this->pdo->query('SELECT COUNT(*) FROM producto')->fetchColumn();

        self::assertSame(10, (int) $count);
    }

    public function testAccentedTextSurvivesTheRoundTrip(): void
    {
        $statement = $this->pdo->prepare(
            'SELECT descripcion FROM producto WHERE id_producto = :id',
        );
        $statement->execute(['id' => 9]);
        $descripcion = (string) $statement->fetchColumn();

        // The legacy connection set an invalid charset, so this is the
        // assertion that the replacement actually fixed it: real "diseño",
        // not the double-encoded "diseÃ±o".
        self::assertStringContainsString('diseño', $descripcion);
        self::assertStringNotContainsString('Ã', $descripcion);
    }

    public function testPreparedStatementsAreNotEmulated(): void
    {
        // PDO reports this one back as an int rather than a bool, so cast
        // before asserting: what matters is that emulation is off, not which
        // falsy type the driver chose to say it with.
        self::assertFalse(
            (bool) $this->pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
            'Emulated prepares interpolate parameters into the SQL string.',
        );
    }

    public function testAParameterIsNeverInterpretedAsSql(): void
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM producto WHERE modelo = :modelo',
        );
        $statement->execute(['modelo' => "B5' OR '1'='1"]);

        self::assertSame(0, (int) $statement->fetchColumn());
    }
}
