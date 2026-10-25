<?php

declare(strict_types=1);

namespace Buffalo\Panel;

/** One class within a vehicle type, and how many models it holds. */
final readonly class ClaseConteo
{
    public function __construct(
        public string $clase,
        public int $modelos,
    ) {
    }
}
