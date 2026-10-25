<?php

declare(strict_types=1);

namespace Buffalo\View;

use Buffalo\Http\Session;
use Buffalo\Repository\ResumenRepository;

/**
 * What every page of the console needs around its own content: who is signed
 * in, which menu item is lit, how many requests are still waiting, and the
 * CSRF token for whatever the page posts.
 *
 * Collected here so a controller asks for it in one line instead of each one
 * remembering to pass four values to the shell. Not readonly, because the
 * counts are worth asking the database for once per request: the menu badge
 * and the panel's own figures are the same two numbers.
 */
final class Consola
{
    /** @var array{nuevas: int, atendidas: int}|null */
    private ?array $estados = null;

    public function __construct(
        private readonly Session $session,
        private readonly ResumenRepository $resumen,
    ) {
    }

    /**
     * @param array<string, mixed> $datos the page's own variables, which win over these
     *
     * @return array<string, mixed>
     */
    public function pagina(string $menuActivo, string $titulo, array $datos = []): array
    {
        return $datos + [
            'menuActivo' => $menuActivo,
            'titulo' => $titulo,
            'usuario' => $this->session->usuario() ?? '',
            'nuevas' => $this->solicitudes()['nuevas'],
            'token' => $this->session->csrfToken(),
        ];
    }

    /** @return array{nuevas: int, atendidas: int} */
    public function solicitudes(): array
    {
        return $this->estados ??= $this->resumen->contactosPorEstado();
    }
}
