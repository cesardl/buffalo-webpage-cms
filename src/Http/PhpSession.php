<?php

declare(strict_types=1);

namespace Buffalo\Http;

final class PhpSession implements Session
{
    private const string USER_KEY = 'user';
    private const string ID_KEY = 'id_usuario';
    private const string CSRF_KEY = 'csrf';

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function usuario(): ?string
    {
        $usuario = $_SESSION[self::USER_KEY] ?? null;

        return is_string($usuario) && $usuario !== '' ? $usuario : null;
    }

    public function usuarioId(): ?int
    {
        $id = $_SESSION[self::ID_KEY] ?? null;

        return is_int($id) ? $id : null;
    }

    public function logIn(int $id, string $usuario): void
    {
        // A fresh id on privilege change, so a token an attacker planted
        // before sign-in is not the one that ends up authenticated.
        session_regenerate_id(true);

        $_SESSION[self::USER_KEY] = $usuario;
        $_SESSION[self::ID_KEY] = $id;
    }

    public function logOut(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public function csrfToken(): string
    {
        if (!isset($_SESSION[self::CSRF_KEY]) || !is_string($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CSRF_KEY];
    }

    public function csrfTokenMatches(string $candidate): bool
    {
        return hash_equals($this->csrfToken(), $candidate);
    }
}
