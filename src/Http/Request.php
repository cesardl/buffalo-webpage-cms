<?php

declare(strict_types=1);

namespace Buffalo\Http;

final readonly class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     */
    public function __construct(
        public string $method,
        public string $path,
        public array $query = [],
        public array $post = [],
        public array $files = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

        return new self(
            method: strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            path: is_string($path) ? $path : '/',
            query: $_GET,
            post: $_POST,
            files: $_FILES,
        );
    }

    public function queryString(string $key, string $default = ''): string
    {
        $value = $this->query[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    public function postString(string $key, string $default = ''): string
    {
        $value = $this->post[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    public function queryInt(string $key, int $default = 0): int
    {
        $value = $this->query[$key] ?? null;

        return is_numeric($value) ? (int) $value : $default;
    }
}
