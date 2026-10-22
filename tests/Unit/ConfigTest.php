<?php

declare(strict_types=1);

namespace Buffalo\Tests\Unit;

use Buffalo\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Config::class)]
final class ConfigTest extends TestCase
{
    private const array COMPLETE = [
        'BUFFALO_DB_HOST' => 'db.example',
        'BUFFALO_DB_NAME' => 'buffalo9_portal',
        'BUFFALO_DB_USER' => 'buffalo9_root',
        'BUFFALO_DB_PASS' => 'secret',
        'BUFFALO_DB_PORT' => '3312',
    ];

    public function testReadsEveryValueFromTheEnvironment(): void
    {
        $config = Config::fromEnvironment(self::COMPLETE);

        self::assertSame('db.example', $config->host);
        self::assertSame('buffalo9_portal', $config->database);
        self::assertSame('buffalo9_root', $config->username);
        self::assertSame('secret', $config->password);
        self::assertSame(3312, $config->port);
    }

    public function testPortDefaultsToTheMysqlDefault(): void
    {
        $env = self::COMPLETE;
        unset($env['BUFFALO_DB_PORT']);

        self::assertSame(3306, Config::fromEnvironment($env)->port);
    }

    public function testNamesEveryMissingVariableAtOnce(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BUFFALO_DB_USER, BUFFALO_DB_PASS');

        Config::fromEnvironment([
            'BUFFALO_DB_HOST' => 'db.example',
            'BUFFALO_DB_NAME' => 'buffalo9_portal',
        ]);
    }

    public function testTreatsAnEmptyVariableAsMissing(): void
    {
        $env = self::COMPLETE;
        $env['BUFFALO_DB_PASS'] = '';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BUFFALO_DB_PASS');

        Config::fromEnvironment($env);
    }

    public function testRejectsANonNumericPort(): void
    {
        $env = self::COMPLETE;
        $env['BUFFALO_DB_PORT'] = '3312;DROP';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be a number');

        Config::fromEnvironment($env);
    }
}
