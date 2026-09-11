<?php

namespace App\Services\Media;

/**
 * Immutable description of a file that has been persisted by a media storage driver.
 */
final class StoredFile
{
    public function __construct(
        public readonly string $disk,
        public readonly string $path,
        public readonly string $mime,
        public readonly int $size,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
    ) {}
}
