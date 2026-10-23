<?php

declare(strict_types=1);

namespace Buffalo\Upload;

/**
 * Stores an uploaded file.
 *
 * Everything that can be decided without a live request lives in
 * FileValidator, which is where the tests are. What is left here is the part
 * that genuinely needs one: confirming the file arrived through an upload,
 * and moving it.
 */
final readonly class Uploader
{
    public function __construct(
        private string $rootDirectory,
        private FileValidator $validator = new FileValidator(),
    ) {
    }

    /** @param array<string, mixed>|null $file One entry of $_FILES. */
    public function image(?array $file, string $subdirectory): UploadResult
    {
        return $this->store($file, $subdirectory, FileValidator::IMAGE_EXTENSIONS);
    }

    /** @param array<string, mixed>|null $file */
    public function pdf(?array $file, string $subdirectory): UploadResult
    {
        return $this->store($file, $subdirectory, FileValidator::PDF_EXTENSIONS);
    }

    /**
     * @param array<string, mixed>|null $file
     * @param list<string> $allowed
     */
    private function store(?array $file, string $subdirectory, array $allowed): UploadResult
    {
        $size = (int) ($file['size'] ?? 0);

        if ($file === null || $size === 0) {
            return UploadResult::none();
        }

        $temporary = (string) ($file['tmp_name'] ?? '');
        if ($temporary === '' || !is_uploaded_file($temporary)) {
            return UploadResult::failed('Transferencia de archivo fallida.');
        }

        $filename = (string) ($file['name'] ?? '');
        $reason = $this->validator->reasonToReject($temporary, $filename, $size, $allowed);

        if ($reason !== null) {
            return UploadResult::failed($reason);
        }

        $name = FileValidator::safeName($filename, FileValidator::extension($filename));
        $destination = $this->rootDirectory . '/' . $subdirectory . '/' . $name;

        if (!move_uploaded_file($temporary, $destination)) {
            return UploadResult::failed('No se pudo guardar el archivo.');
        }

        return UploadResult::stored($subdirectory . '/' . $name);
    }
}
