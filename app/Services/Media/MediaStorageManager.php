<?php

namespace App\Services\Media;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Http\Client\Factory as HttpFactory;
use InvalidArgumentException;

/**
 * Resolves media storage drivers by name so that files uploaded under a previous
 * provider keep resolving after the configured default changes.
 */
class MediaStorageManager
{
    /** @var array<string, MediaStorage> */
    private array $resolved = [];

    public function __construct(private Container $container) {}

    public function default(): MediaStorage
    {
        return $this->driver((string) config('foundation.media.driver', 'public'));
    }

    public function driver(string $name): MediaStorage
    {
        return $this->resolved[$name] ??= $this->resolve($name);
    }

    private function resolve(string $name): MediaStorage
    {
        if ($name === 'cloudinary') {
            return new CloudinaryMediaStorage(
                $this->container->make(HttpFactory::class),
                config('foundation.media.cloudinary'),
            );
        }

        if (! is_array(config('filesystems.disks.'.$name))) {
            throw new InvalidArgumentException('Unknown media storage driver: '.$name.'.');
        }

        return new LocalMediaStorage(
            $this->container->make(FilesystemFactory::class)->disk($name),
            $name,
        );
    }
}
