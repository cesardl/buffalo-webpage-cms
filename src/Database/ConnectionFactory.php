<?php

declare(strict_types=1);

namespace Buffalo\Database;

use Buffalo\Config;
use PDO;

/**
 * Hands out configured PDO connections.
 *
 * Every legacy DAO method called `new Connection()` for itself, so each one
 * owned both connection lifecycle and query logic, and nothing could be
 * tested without a live database. Repositories take a PDO instead and no
 * longer decide where it comes from.
 */
final readonly class ConnectionFactory
{
    public function __construct(private Config $config)
    {
    }

    public function create(): PDO
    {
        return new PDO($this->dsn(), $this->config->username, $this->config->password, [
            // Fail loudly instead of returning false and carrying on, which is
            // how the legacy code let a broken query reach the page.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Real prepared statements, not client-side interpolation.
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function dsn(): string
    {
        // charset belongs in the DSN. The legacy code called
        // mysql_set_charset("UTF-8"), which is not a charset MySQL knows; the
        // call returned false and the connection silently used the server
        // default.
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->config->host,
            $this->config->port,
            $this->config->database,
        );
    }
}
