<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;

/**
 * Contract every media storage provider implements so that the application can swap
 * between local disks and Cloudinary through configuration alone.
 */
interface MediaStorage
{
    /**
     * Identifier persisted on the media record so stored files stay resolvable
     * after the configured default provider changes.
     */
    public function name(): string;

    /**
     * Persist an uploaded file and return its canonical storage description.
     */
    public function store(UploadedFile $file, string $folder): StoredFile;

    /**
     * Remove a previously stored file. Missing files are not an error.
     */
    public function delete(string $path): void;

    /**
     * Build a delivery URL. Recognised transform keys are `width`, `height`,
     * `quality` and `crop`; providers ignore what they cannot honour.
     *
     * @param  array{width?: int, height?: int, quality?: string, crop?: string}  $transform
     */
    public function url(string $path, array $transform = []): string;
}
