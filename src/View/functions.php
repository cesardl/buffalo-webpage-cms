<?php

declare(strict_types=1);

namespace Buffalo\View;

/** Escape for HTML text and attribute content. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * The palette as <option> elements, with one marked selected.
 *
 * ColorBL::getColores() built this string and the form echoed it, which is
 * why a business-logic class was emitting HTML. The markup is reproduced
 * exactly, two spaces after "<option" included, so the ported form still
 * matches the page it replaces.
 *
 * @param list<\Buffalo\Domain\Color> $palette
 */
function opcionesColor(array $palette, ?int $seleccionado): string
{
    $html = '';

    foreach ($palette as $color) {
        $selected = $color->id === $seleccionado ? 'selected' : '';
        $html .= "<option {$selected} value=\"" . e((string) $color->id)
            . '" style="background-color: #' . e($color->codigo) . ';color: #FFFFFF;">
                    ' . e($color->nombre) . '
                    </option>';
    }

    return $html;
}
