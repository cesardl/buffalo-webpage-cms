<?php

declare(strict_types=1);

namespace Buffalo\Http;

interface Session
{
    public function usuario(): ?string;

    /** Who is signed in, for the rows that record who did something. */
    public function usuarioId(): ?int;

    public function logIn(int $id, string $usuario): void;

    public function logOut(): void;

    /** Stable for the life of the session; issued on first use. */
    public function csrfToken(): string;

    public function csrfTokenMatches(string $candidate): bool;
}
