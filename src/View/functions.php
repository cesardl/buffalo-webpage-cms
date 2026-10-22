<?php

declare(strict_types=1);

namespace Buffalo\View;

/** Escape for HTML text and attribute content. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
