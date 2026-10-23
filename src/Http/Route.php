<?php

declare(strict_types=1);

namespace Buffalo\Http;

final readonly class Route
{
    /** @param class-string<Controller> $handler */
    public function __construct(
        public string $method,
        public string $path,
        public Access $access,
        public string $handler,
    ) {
    }

    public function matches(Request $request): bool
    {
        return $this->method === $request->method && $this->path === $request->path;
    }
}
