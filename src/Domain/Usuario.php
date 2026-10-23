<?php

declare(strict_types=1);

namespace Buffalo\Domain;

final readonly class Usuario
{
    public function __construct(
        public int $id,
        public string $usuario,
        /**
         * Whatever is in usuario.passwd today, which is the password in clear
         * text. Verifying it is deliberately not this object's job -- Phase 4
         * introduces an authenticator and migrates the column to a hash. The
         * column is varchar(50) and will need widening for that.
         */
        public string $storedPassword,
    ) {
    }
}
