<?php

declare(strict_types=1);

namespace Buffalo;

use RuntimeException;

/**
 * Database settings, read from the environment.
 *
 * The old administrator/util/Connection.php did four things in one
 * constructor: it held the credentials as literals, opened the connection,
 * selected the database and set a charset. Those are separate concerns with
 * separate reasons to change, so configuration lives here and connecting
 * lives in ConnectionFactory.
 *
 * Keeping the credentials out of the source also means they stop being
 * committed to a public repository.
 */
final readonly class Config
{
    private const array REQUIRED = [
        'BUFFALO_DB_HOST',
        'BUFFALO_DB_NAME',
        'BUFFALO_DB_USER',
        'BUFFALO_DB_PASS',
    ];

    public function __construct(
        public string $host,
        public string $database,
        public string $username,
        public string $password,
        public int $port = 3306,
    ) {
    }

    /**
     * @param array<string, string>|null $env Defaults to the process environment.
     */
    public static function fromEnvironment(?array $env = null): self
    {
        $env ??= self::processEnvironment();

        $missing = array_values(array_filter(
            self::REQUIRED,
            static fn (string $key): bool => ($env[$key] ?? '') === '',
        ));

        if ($missing !== []) {
            throw new RuntimeException(
                'Missing required environment variable(s): ' . implode(', ', $missing),
            );
        }

        $port = $env['BUFFALO_DB_PORT'] ?? '3306';
        if (!ctype_digit((string) $port)) {
            throw new RuntimeException("BUFFALO_DB_PORT must be a number, got '{$port}'.");
        }

        return new self(
            host: $env['BUFFALO_DB_HOST'],
            database: $env['BUFFALO_DB_NAME'],
            username: $env['BUFFALO_DB_USER'],
            password: $env['BUFFALO_DB_PASS'],
            port: (int) $port,
        );
    }

    /**
     * getenv() rather than $_ENV: the official PHP images ship
     * variables_order="GPCS", so $_ENV is empty under Apache.
     *
     * @return array<string, string>
     */
    private static function processEnvironment(): array
    {
        $keys = [...self::REQUIRED, 'BUFFALO_DB_PORT'];
        $values = [];

        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $values[$key] = $value;
            }
        }

        return $values;
    }
}
