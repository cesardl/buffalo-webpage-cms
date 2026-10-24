<?php

declare(strict_types=1);

namespace Buffalo\Repository;

use Buffalo\Domain\Contacto;

interface ContactoRepository
{
    /** @return int the id the message was stored under */
    public function save(Contacto $contacto): int;
}
