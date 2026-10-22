<?php

declare(strict_types=1);

namespace Buffalo\Upload;

/**
 * Validated uploads.
 *
 * The legacy controllers decided whether a file was safe from the declared
 * content type -- a header the caller picks -- and then wrote it into the web
 * root under the name it arrived with, which allowed an executable file to be
 * placed there. The extension must be one we recognise, the bytes on disk
 * have to agree with it, and the stored name is generated.
 */
final readonly class Uploader
{
    private const int MAX_BYTES = 5_000_000;

    private const array IMAGE_TYPES = [
        'gif' => IMAGETYPE_GIF,
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
    ];

    public function __construct(private string $rootDirectory)
    {
    }

    /** @param array<string, mixed>|null $file One entry of $_FILES. */
    public function image(?array $file, string $subdirectory): UploadResult
    {
        return $this->store($file, $subdirectory, array_keys(self::IMAGE_TYPES));
    }

    /** @param array<string, mixed>|null $file */
    public function pdf(?array $file, string $subdirectory): UploadResult
    {
        return $this->store($file, $subdirectory, ['pdf']);
    }

    /**
     * @param array<string, mixed>|null $file
     * @param list<string> $allowed
     */
    private function store(?array $file, string $subdirectory, array $allowed): UploadResult
    {
        if ($file === null || (int) ($file['size'] ?? 0) === 0) {
            return UploadResult::none();
        }

        if ((int) $file['size'] >= self::MAX_BYTES) {
            return UploadResult::failed('El archivo supera el tamaño permitido.');
        }

        $temporary = (string) ($file['tmp_name'] ?? '');
        if ($temporary === '' || !is_uploaded_file($temporary)) {
            return UploadResult::failed('Transferencia de archivo fallida.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            return UploadResult::failed('No es un archivo valido.');
        }

        if (!$this->contentsMatch($temporary, $extension)) {
            return UploadResult::failed('El contenido no corresponde a la extensión.');
        }

        $name = $this->safeName((string) $file['name'], $extension);
        $destination = $this->rootDirectory . '/' . $subdirectory . '/' . $name;

        if (!move_uploaded_file($temporary, $destination)) {
            return UploadResult::failed('No se pudo guardar el archivo.');
        }

        return UploadResult::stored($subdirectory . '/' . $name);
    }

    /**
     * The extension decides what the server will do with the file; this says
     * what the file is. Both have to agree.
     */
    private function contentsMatch(string $path, string $extension): bool
    {
        if ($extension === 'pdf') {
            $handle = fopen($path, 'rb');
            if ($handle === false) {
                return false;
            }
            $magic = fread($handle, 5);
            fclose($handle);

            return $magic === '%PDF-';
        }

        $image = @getimagesize($path);

        return $image !== false && ($image[2] ?? null) === self::IMAGE_TYPES[$extension];
    }

    private function safeName(string $original, string $extension): string
    {
        $base = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($original, PATHINFO_FILENAME)) ?? '';
        $base = trim(substr($base, 0, 60), '_');

        return ($base === '' ? 'archivo' : $base)
            . '_' . substr(bin2hex(random_bytes(8)), 0, 8) . '.' . $extension;
    }
}
