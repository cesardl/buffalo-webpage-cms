<?php

declare(strict_types=1);

namespace Buffalo\Domain;

final readonly class Color
{
    public function __construct(
        public int $id,
        public string $nombre,
        /** Six hex digits, no leading '#'. */
        public string $codigo,
    ) {
    }
}
