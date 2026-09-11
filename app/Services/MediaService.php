<?php

namespace App\Services;

use App\Models\Media;
use App\Services\Media\MediaStorageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Application-facing media operations. Every upload and delivery URL passes through
 * here so that switching between Cloudinary and a local disk stays a configuration
 * change rather than a code change.
 */
class MediaService
{
    /**
     * Widths offered to the browser. The list is deliberately wide at the top end so
     * scanned certificates and project photography stay legible on large displays.
     *
     * @var list<int>
     */
    public const WIDTHS = [400, 640, 960, 1280, 1600, 2000];

    public function __construct(private MediaStorageManager $storage) {}

    /**
     * Store an upload and record its metadata. Records start unapproved so that
     * editorial review controls what reaches the public site.
     *
     * @param  array{title?: string, alt?: string, caption?: string, credit?: string}  $attributes
     */
    public function store(UploadedFile $file, string $folder = 'media', array $attributes = []): Media
    {
        $stored = $this->storage->default()->store($file, $folder);

        return Media::create([
            'title' => $attributes['title'] ?? Str::headline(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            'disk' => $stored->disk,
            'path' => $stored->path,
            'mime' => $stored->mime,
            'size' => $stored->size,
            'alt' => $attributes['alt'] ?? null,
            'caption' => $attributes['caption'] ?? null,
            'credit' => $attributes['credit'] ?? null,
            'variants' => array_filter(['width' => $stored->width, 'height' => $stored->height]),
            'approved' => false,
        ]);
    }

    public function delete(Media $media): void
    {
        $this->storage->driver($media->disk)->delete($media->path);

        $media->delete();
    }

    /**
     * @param  array{width?: int, height?: int, quality?: string, crop?: string, gravity?: string, aspect?: string}  $transform
     */
    public function url(Media $media, array $transform = []): string
    {
        return $this->storage->driver($media->disk)->url($media->path, $this->resolveAspect($transform));
    }

    /**
     * Expand an `aspect` ratio into the height the driver needs.
     *
     * Carrying the ratio rather than a fixed height means every candidate in a
     * `srcset` is cropped to the same shape, instead of one height being applied
     * at every width and distorting all but one of them.
     *
     * @param  array{width?: int, height?: int, quality?: string, crop?: string, gravity?: string, aspect?: string}  $transform
     * @return array{width?: int, height?: int, quality?: string, crop?: string, gravity?: string}
     */
    private function resolveAspect(array $transform): array
    {
        if (! isset($transform['aspect'])) {
            return $transform;
        }

        $aspect = $transform['aspect'];
        unset($transform['aspect']);

        if (! isset($transform['width']) || ! str_contains($aspect, ':')) {
            return $transform;
        }

        [$horizontal, $vertical] = array_map('intval', explode(':', $aspect, 2));

        if ($horizontal < 1 || $vertical < 1) {
            return $transform;
        }

        return $transform + ['height' => (int) round((int) $transform['width'] * $vertical / $horizontal)];
    }

    /**
     * Build a `srcset` value. Widths larger than the stored original are skipped so
     * the browser is never offered an upscaled variant.
     *
     * @param  array{quality?: string, crop?: string, gravity?: string, aspect?: string}  $transform
     */
    public function srcset(Media $media, array $transform = []): string
    {
        $original = $media->variants['width'] ?? null;

        $candidates = array_values(array_filter(
            self::WIDTHS,
            fn (int $width): bool => ! is_int($original) || $width <= $original,
        )) ?: [self::WIDTHS[0]];

        return implode(', ', array_map(
            fn (int $width): string => $this->url($media, $transform + ['width' => $width]).' '.$width.'w',
            $candidates,
        ));
    }
}
