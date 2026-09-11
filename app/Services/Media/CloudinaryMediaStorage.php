<?php

namespace App\Services\Media;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Cloudinary storage using the signed REST upload API.
 *
 * Credentials stay server-side: the API secret is only ever used to sign a request
 * body, never rendered into markup or client assets. Stored paths keep Cloudinary's
 * `{resource_type}/{delivery_type}/{public_id}.{format}` shape so a delivery URL can
 * be rebuilt, and transformations injected, without a second API call.
 */
class CloudinaryMediaStorage implements MediaStorage
{
    private const ENDPOINT = 'https://api.cloudinary.com/v1_1';

    private const DELIVERY = 'https://res.cloudinary.com';

    /**
     * @param  array{cloud_name: ?string, api_key: ?string, api_secret: ?string, folder: ?string, signature_algorithm: ?string}  $config
     */
    public function __construct(private HttpFactory $http, private array $config) {}

    public function name(): string
    {
        return 'cloudinary';
    }

    public function store(UploadedFile $file, string $folder): StoredFile
    {
        $resourceType = $this->resourceTypeFor($file->getMimeType() ?: '');
        $parameters = [
            'folder' => trim($this->prefix().'/'.trim($folder, '/'), '/'),
            'public_id' => Str::lower(Str::random(24)),
            'timestamp' => (string) time(),
        ];

        $response = $this->http
            ->timeout(60)
            ->attach('file', $file->getContent(), $file->getClientOriginalName())
            ->post(self::ENDPOINT.'/'.$this->credential('cloud_name').'/'.$resourceType.'/upload', $parameters + [
                'api_key' => $this->credential('api_key'),
                'signature' => $this->sign($parameters),
            ]);

        $response->throw();

        $publicId = $response->json('public_id');
        $deliveryType = $response->json('type', 'upload');

        if (! is_string($publicId) || $publicId === '' || ! is_string($deliveryType)) {
            throw new RuntimeException('Cloudinary did not return a usable public identifier.');
        }

        $format = $response->json('format');
        $path = $response->json('resource_type', $resourceType).'/'.$deliveryType.'/'.$publicId;

        if (is_string($format) && $format !== '' && ! Str::endsWith($publicId, '.'.$format)) {
            $path .= '.'.$format;
        }

        return new StoredFile(
            disk: $this->name(),
            path: $path,
            mime: $file->getMimeType() ?: 'application/octet-stream',
            size: (int) $response->json('bytes', $file->getSize()),
            width: $response->json('width'),
            height: $response->json('height'),
        );
    }

    public function delete(string $path): void
    {
        [$resourceType, , $publicId] = $this->segments($path);

        $parameters = [
            'public_id' => $resourceType === 'raw' ? $publicId : $this->withoutFormat($publicId),
            'timestamp' => (string) time(),
        ];

        $this->http
            ->timeout(30)
            ->asForm()
            ->post(self::ENDPOINT.'/'.$this->credential('cloud_name').'/'.$resourceType.'/destroy', $parameters + [
                'api_key' => $this->credential('api_key'),
                'signature' => $this->sign($parameters),
            ])
            ->throw();
    }

    /**
     * @param  array{width?: int, height?: int, quality?: string, crop?: string, gravity?: string}  $transform
     */
    public function url(string $path, array $transform = []): string
    {
        [$resourceType, $deliveryType, $publicId] = $this->segments($path);

        $segments = [self::DELIVERY, $this->credential('cloud_name'), $resourceType, $deliveryType];

        if ($resourceType === 'image') {
            $segments[] = $this->transformation($transform);
        }

        $segments[] = $publicId;

        return implode('/', array_filter($segments));
    }

    /**
     * Cloudinary derives the delivered encoding and compression from the requesting
     * browser, so `f_auto` yields AVIF or WebP where supported without a separate
     * stored variant, and the default quality stays visually lossless.
     *
     * @param  array{width?: int, height?: int, quality?: string, crop?: string, gravity?: string}  $transform
     */
    private function transformation(array $transform): string
    {
        $parts = ['f_auto', 'q_'.($transform['quality'] ?? 'auto:good')];

        if (isset($transform['width'])) {
            $parts[] = 'w_'.(int) $transform['width'];
        }

        if (isset($transform['height'])) {
            $parts[] = 'h_'.(int) $transform['height'];
        }

        $crop = $transform['crop'] ?? (isset($transform['height']) ? 'fill' : 'limit');
        $parts[] = 'c_'.$crop;

        // A crop needs to know what to keep. `face` is what a portrait wants:
        // it holds the subject's head in frame whatever the source framing was.
        if (in_array($crop, ['fill', 'thumb', 'crop'], true)) {
            $parts[] = 'g_'.($transform['gravity'] ?? 'auto');
        }

        return implode(',', $parts);
    }

    /**
     * Split a stored path into its resource type, delivery type and public identifier.
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function segments(string $path): array
    {
        $parts = explode('/', ltrim($path, '/'), 3);

        if (count($parts) !== 3 || in_array('', $parts, true)) {
            throw new RuntimeException('Malformed Cloudinary media path: '.$path);
        }

        return $parts;
    }

    private function withoutFormat(string $publicId): string
    {
        $extension = pathinfo($publicId, PATHINFO_EXTENSION);

        return $extension === '' ? $publicId : Str::beforeLast($publicId, '.'.$extension);
    }

    /**
     * PDFs and office documents are delivered untouched; everything Cloudinary can
     * transform is uploaded as an image so responsive variants remain available.
     */
    private function resourceTypeFor(string $mime): string
    {
        return Str::startsWith($mime, 'image/') ? 'image' : (Str::startsWith($mime, 'video/') ? 'video' : 'raw');
    }

    /**
     * Cloudinary signs the alphabetically sorted parameters joined as an unencoded
     * query string. Accounts default to SHA-1; SHA-256 accounts set the algorithm
     * through configuration.
     *
     * @param  array<string, string>  $parameters
     */
    private function sign(array $parameters): string
    {
        ksort($parameters);

        $algorithm = $this->config['signature_algorithm'] ?? 'sha1';

        return hash($algorithm, urldecode(http_build_query($parameters)).$this->credential('api_secret'));
    }

    private function prefix(): string
    {
        return trim((string) ($this->config['folder'] ?? ''), '/');
    }

    private function credential(string $key): string
    {
        $value = $this->config[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new RuntimeException('Cloudinary media storage is not configured: missing '.$key.'.');
        }

        return $value;
    }
}
