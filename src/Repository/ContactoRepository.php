<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Contacto;

interface ContactoRepository
{
    /** @return int the id the message was stored under */
    public function save(Contacto $contacto): int;

    /**
     * @param string|null $estado one of Contacto::NUEVA or Contacto::ATENDIDA; null for every message
     *
     * @return list<Contacto> newest first
     */
    public function findAll(?string $estado = null): array;

    public function find(int $id): ?Contacto;

    /**
     * Moves a message between states.
     *
     * Attending one records when and by whom; reopening clears both, so the
     * row never claims it was handled by someone who then changed their mind.
     */
    public function cambiarEstado(int $id, string $estado, ?int $usuarioId): void;
}
