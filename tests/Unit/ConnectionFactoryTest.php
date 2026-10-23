<?php

declare(strict_types=1);

namespace Buffalo\Tests\Unit;

use Buffalo\Config;
use Buffalo\Database\ConnectionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConnectionFactory::class)]
final class ConnectionFactoryTest extends TestCase
{
    public function testBuildsADsnCarryingHostPortDatabaseAndCharset(): void
    {
        $factory = new ConnectionFactory(new Config(
            host: 'db.example',
            database: 'buffalo9_portal',
            username: 'buffalo9_root',
            password: 'secret',
            port: 3312,
        ));

        self::assertSame(
            'mysql:host=db.example;port=3312;dbname=buffalo9_portal;charset=utf8mb4',
            $factory->dsn(),
        );
    }

    public function testDsnAsksForUtf8mb4RatherThanTheLegacyInvalidCharset(): void
    {
        $factory = new ConnectionFactory(new Config(
            host: 'localhost',
            database: 'buffalo9_portal',
            username: 'u',
            password: 'p',
        ));

        self::assertStringContainsString('charset=utf8mb4', $factory->dsn());
        self::assertStringNotContainsString('UTF-8', $factory->dsn());
    }
}
