<?php

declare(strict_types=1);

namespace Buffalo\Upload;

/**
 * Replaces the legacy "path string, or an integer index into the caller's
 * $messages array" convention, which is why every caller had to run
 * is_numeric() over its own return value to find out what happened.
 */
final readonly class UploadResult
{
    private function __construct(
        public ?string $path,
        public ?string $error,
    ) {
    }

    /** Nothing was submitted: keep whatever path is already stored. */
    public static function none(): self
    {
        return new self(null, null);
    }

    public static function stored(string $path): self
    {
        return new self($path, null);
    }

    public static function failed(string $error): self
    {
        return new self(null, $error);
    }

    public function hasFailed(): bool
    {
        return $this->error !== null;
    }

    public function submitted(): bool
    {
        return $this->path !== null || $this->error !== null;
    }
}
