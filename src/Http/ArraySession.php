<?php

declare(strict_types=1);

namespace Buffalo\Http;

/** An in-memory Session, so routing and controllers are testable without PHP's. */
final class ArraySession implements Session
{
    private ?string $usuario = null;
    private ?int $id = null;
    private ?string $csrf = null;

    public function usuario(): ?string
    {
        return $this->usuario;
    }

    public function logIn(int $id, string $usuario): void
    {
        $this->id = $id;
        $this->usuario = $usuario;
    }

    public function logOut(): void
    {
        $this->id = null;
        $this->usuario = null;
        $this->csrf = null;
    }

    public function csrfToken(): string
    {
        return $this->csrf ??= bin2hex(random_bytes(32));
    }

    public function csrfTokenMatches(string $candidate): bool
    {
        return $candidate !== '' && hash_equals($this->csrfToken(), $candidate);
    }
}
