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

    public static function redirect(string $location): self
    {
        return new self(302, ['Location' => $location], '');
    }

    public static function forbidden(): self
    {
        return self::text('Forbidden', 403);
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
