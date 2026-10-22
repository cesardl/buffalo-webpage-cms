<?php

declare(strict_types=1);

namespace Buffalo\Upload;

/**
 * Decides whether a file may be stored.
 *
 * Split out from Uploader so it can be tested: Uploader has to call
 * is_uploaded_file() and move_uploaded_file(), which only behave inside a
 * real request, and that was enough to leave the whole of this untested.
 * Nothing here touches the request, so all of it can be exercised with
 * ordinary files on disk.
 */
final readonly class FileValidator
{
    public const int MAX_BYTES = 5_000_000;

    public const array IMAGE_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'png'];
    public const array PDF_EXTENSIONS = ['pdf'];

    private const array IMAGE_TYPES = [
        'gif' => IMAGETYPE_GIF,
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
    ];

    /**
     * @param list<string> $allowed
     * @return string|null The reason it was rejected, or null if acceptable.
     */
    public function reasonToReject(string $path, string $filename, int $size, array $allowed): ?string
    {
        if ($size >= self::MAX_BYTES) {
            return 'El archivo supera el tamaño permitido.';
        }

        $extension = self::extension($filename);

        // The extension decides what the server will do with the file. A
        // script named .php never reaches the content check below, which is
        // the half the legacy gate was missing: it trusted the declared
        // content type and kept the submitted name.
        if (!in_array($extension, $allowed, true)) {
            return 'No es un archivo valido.';
        }

        if (!$this->contentsMatch($path, $extension)) {
            return 'El contenido no corresponde a la extensión.';
        }

        return null;
    }

    public static function extension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /** What the file actually is, read from its bytes. */
    public function contentsMatch(string $path, string $extension): bool
    {
        if ($extension === 'pdf') {
            $handle = @fopen($path, 'rb');
            if ($handle === false) {
                return false;
            }
            $magic = fread($handle, 5);
            fclose($handle);

            return $magic === '%PDF-';
        }

        if (!isset(self::IMAGE_TYPES[$extension])) {
            return false;
        }

        $image = @getimagesize($path);

        return $image !== false && ($image[2] ?? null) === self::IMAGE_TYPES[$extension];
    }

    /**
     * Keeps something recognisable from the original name, but the stored
     * name is ours: the submitted one decides both where the file lands and
     * whether the server will execute it.
     */
    public static function safeName(string $original, string $extension): string
    {
        $base = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($original, PATHINFO_FILENAME)) ?? '';
        $base = trim(substr($base, 0, 60), '_');

        return ($base === '' ? 'archivo' : $base)
            . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    }
}
