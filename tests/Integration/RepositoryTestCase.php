<?php

declare(strict_types=1);

namespace Buffalo\Tests\Integration;

use Buffalo\Config;
use Buffalo\Database\ConnectionFactory;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Every test runs inside a transaction that is rolled back afterwards, so the
 * write tests can insert and delete freely without leaving the seeded data
 * different from how they found it.
 */
abstract class RepositoryTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        if (getenv('BUFFALO_DB_HOST') === false) {
            self::markTestSkipped('No BUFFALO_DB_* environment configured.');
        }

        $this->pdo = (new ConnectionFactory(Config::fromEnvironment()))->create();
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo) && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
