<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Services\Media\MediaStorageManager;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('foundation.media.cloudinary', [
            'cloud_name' => 'gsf-test',
            'api_key' => 'test-key',
            'api_secret' => 'test-secret',
            'folder' => 'gsf',
            'signature_algorithm' => 'sha1',
        ]);
    }

    private function useCloudinary(): void
    {
        Config::set('foundation.media.driver', 'cloudinary');
    }

    public function test_it_uploads_an_image_to_cloudinary_and_records_the_returned_path(): void
    {
        $this->useCloudinary();

        Http::fake(['api.cloudinary.com/*' => Http::response([
            'public_id' => 'gsf/projects/abc123',
            'resource_type' => 'image',
            'type' => 'upload',
            'format' => 'jpg',
            'bytes' => 204_800,
            'width' => 1600,
            'height' => 1067,
        ])]);

        $media = app(MediaService::class)->store(
            UploadedFile::fake()->image('cooperative-training.jpg', 1600, 1067),
            'projects',
        );

        $this->assertSame('cloudinary', $media->disk);
        $this->assertSame('image/upload/gsf/projects/abc123.jpg', $media->path);
        $this->assertSame(204_800, $media->size);
        $this->assertSame(1600, $media->variants['width']);

        // Newly uploaded media awaits editorial approval before it can be published.
        $this->assertFalse($media->approved);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.cloudinary.com/v1_1/gsf-test/image/upload'
                && $request->isMultipart();
        });
    }

    public function test_it_signs_uploads_without_leaking_the_api_secret(): void
    {
        $this->useCloudinary();

        Http::fake(['api.cloudinary.com/*' => Http::response([
            'public_id' => 'gsf/media/abc', 'resource_type' => 'image', 'type' => 'upload', 'format' => 'png',
        ])]);

        app(MediaService::class)->store(UploadedFile::fake()->image('logo.png'), 'media');

        Http::assertSent(function (Request $request): bool {
            $fields = collect($request->data())->pluck('contents', 'name');

            $expected = sha1(
                'folder=gsf/media&public_id='.$fields['public_id'].'&timestamp='.$fields['timestamp'].'test-secret'
            );

            return $fields['signature'] === $expected
                && $fields['api_key'] === 'test-key'
                && ! str_contains((string) json_encode($fields->all()), 'test-secret');
        });
    }

    public function test_it_uploads_documents_as_raw_resources(): void
    {
        $this->useCloudinary();

        Http::fake(['api.cloudinary.com/*' => Http::response([
            'public_id' => 'gsf/resources/report.pdf', 'resource_type' => 'raw', 'type' => 'upload',
        ])]);

        $media = app(MediaService::class)->store(
            UploadedFile::fake()->create('annual-report.pdf', 512, 'application/pdf'),
            'resources',
        );

        $this->assertSame('raw/upload/gsf/resources/report.pdf', $media->path);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.cloudinary.com/v1_1/gsf-test/raw/upload');
    }

    public function test_it_builds_transformed_delivery_urls_for_images(): void
    {
        $media = Media::factory()->create([
            'disk' => 'cloudinary',
            'path' => 'image/upload/gsf/projects/abc123.jpg',
        ]);

        $url = app(MediaService::class)->url($media, ['width' => 960]);

        $this->assertSame(
            'https://res.cloudinary.com/gsf-test/image/upload/f_auto,q_auto:good,w_960,c_limit/gsf/projects/abc123.jpg',
            $url,
        );
    }

    public function test_it_leaves_raw_documents_untransformed(): void
    {
        $media = Media::factory()->document()->create([
            'disk' => 'cloudinary',
            'path' => 'raw/upload/gsf/resources/report.pdf',
        ]);

        $this->assertSame(
            'https://res.cloudinary.com/gsf-test/raw/upload/gsf/resources/report.pdf',
            app(MediaService::class)->url($media),
        );
    }

    public function test_srcset_never_offers_a_width_larger_than_the_original(): void
    {
        $media = Media::factory()->create([
            'disk' => 'cloudinary',
            'path' => 'image/upload/gsf/projects/abc123.jpg',
            'variants' => ['width' => 960, 'height' => 640],
        ]);

        $srcset = app(MediaService::class)->srcset($media);

        $this->assertStringContainsString('w_960,c_limit/gsf/projects/abc123.jpg 960w', $srcset);
        $this->assertStringContainsString('w_400,c_limit/gsf/projects/abc123.jpg 400w', $srcset);
        $this->assertStringNotContainsString('1280w', $srcset);
        $this->assertStringNotContainsString('2000w', $srcset);
    }

    public function test_existing_files_keep_their_original_driver_after_the_default_changes(): void
    {
        Storage::fake('public');

        $media = app(MediaService::class)->store(UploadedFile::fake()->image('legacy.jpg'), 'media');
        $this->assertSame('public', $media->disk);

        // The organisation later moves to Cloudinary; the earlier upload must still resolve.
        $this->useCloudinary();
        app()->forgetInstance(MediaStorageManager::class);

        $this->assertStringContainsString('/storage/media/', app(MediaService::class)->url($media->fresh()));
    }

    public function test_deleting_media_removes_the_remote_file_and_the_record(): void
    {
        Http::fake(['api.cloudinary.com/*' => Http::response(['result' => 'ok'])]);

        $media = Media::factory()->create([
            'disk' => 'cloudinary',
            'path' => 'image/upload/gsf/projects/abc123.jpg',
        ]);

        app(MediaService::class)->delete($media);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.cloudinary.com/v1_1/gsf-test/image/destroy'
                // The stored format is stripped: Cloudinary destroys by public id.
                && $request['public_id'] === 'gsf/projects/abc123';
        });
    }

    public function test_it_fails_loudly_when_cloudinary_is_not_configured(): void
    {
        $this->useCloudinary();
        Config::set('foundation.media.cloudinary.cloud_name', null);

        $this->expectExceptionMessage('Cloudinary media storage is not configured: missing cloud_name.');

        app(MediaService::class)->store(UploadedFile::fake()->image('x.jpg'), 'media');
    }
}
