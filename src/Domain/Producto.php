<?php

declare(strict_types=1);

namespace Buffalo\Domain;

use InvalidArgumentException;

final readonly class Producto
{
    public const int ZOOM_SLOTS = 4;

    /**
     * @param list<string> $fotosZoom Exactly ZOOM_SLOTS entries, positional:
     *        index 0 is foto_zoom_1. Unused slots are '' rather than missing,
     *        because the admin form edits them by position.
     */
    public function __construct(
        public ?int $id,
        public int $masterId,
        public string $modelo,
        public string $descripcion,
        public string $fotoPrincipal,
        public array $fotosZoom,
        public string $fichaTecnica,
    ) {
        if (count($fotosZoom) !== self::ZOOM_SLOTS) {
            throw new InvalidArgumentException(
                sprintf('Expected %d zoom slots, got %d.', self::ZOOM_SLOTS, count($fotosZoom)),
            );
        }
    }

    /**
     * The slots that actually hold an image, for callers rendering a gallery
     * rather than an edit form.
     *
     * @return list<string>
     */
    public function fotosZoomPresentes(): array
    {
        return array_values(array_filter(
            $this->fotosZoom,
            static fn (string $foto): bool => $foto !== '',
        ));
    }
}
