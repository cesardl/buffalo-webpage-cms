<?php

declare(strict_types=1);

namespace Buffalo\Http;

final readonly class Response
{
    /** @param array<string, string> $headers */
    private function __construct(
        public int $status,
        public array $headers,
        public string $body,
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($status, ['Content-Type' => 'text/html; charset=UTF-8'], $body);
    }

    public static function text(string $body, int $status = 200): self
    {
        return new self($status, ['Content-Type' => 'text/plain; charset=UTF-8'], $body);
    }

    /** @param array<string, mixed> $datos */
    public static function json(array $datos, int $status = 200): self
    {
        return new self(
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8'],
            json_encode($datos, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        );
    }

    public static function redirect(string $location): self
    {
        return new self(302, ['Location' => $location], '');
    }

    public static function forbidden(): self
    {
        // Empty body, as the legacy endpoints produced: they sent the status
        // header and exited. The callers are XHR handlers that look at the
        // status, and a body would be injected into the page as content.
        return self::text('', 403);
    }

    public static function notFound(): self
    {
        return self::text('Not Found', 404);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }
}
