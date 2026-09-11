<?php

namespace App\Services\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Development and single-server fallback backed by a Laravel disk.
 *
 * The originals are served unmodified: this driver performs no resizing, so
 * transformation hints are ignored rather than silently degrading an image.
 * Production installations use {@see CloudinaryMediaStorage}.
 */
class LocalMediaStorage implements MediaStorage
{
    public function __construct(private Filesystem $disk, private string $name) {}

    public function name(): string
    {
        return $this->name;
    }

    public function store(UploadedFile $file, string $folder): StoredFile
    {
        $path = $this->disk->putFile(trim($folder, '/'), $file);

        if (! is_string($path)) {
            throw new RuntimeException('The uploaded file could not be stored.');
        }

        $dimensions = @getimagesize($file->getRealPath()) ?: null;

        return new StoredFile(
            disk: $this->name,
            path: $path,
            mime: $file->getMimeType() ?: 'application/octet-stream',
            size: (int) $file->getSize(),
            width: $dimensions[0] ?? null,
            height: $dimensions[1] ?? null,
        );
    }

    public function delete(string $path): void
    {
        $this->disk->delete($path);
    }

    /**
     * @param  array{width?: int, height?: int, quality?: string, crop?: string}  $transform
     */
    public function url(string $path, array $transform = []): string
    {
        return $this->disk->url($path);
    }
}
