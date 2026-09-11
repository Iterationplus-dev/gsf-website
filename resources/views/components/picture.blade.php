@props([
    'media' => null,
    'alt' => null,
    'sizes' => '100vw',
    'width' => 1280,
    'ratio' => 'aspect-[16/10]',
    'quality' => null,
    'class' => '',
    'eager' => false,
    'crop' => null,
    'gravity' => null,
    'aspect' => null,
])

@php
    /**
     * Renders an approved image through the configured media driver.
     *
     * Width and height are always emitted so the browser reserves the space
     * before the file arrives, which is what keeps cumulative layout shift at
     * zero. Cloudinary negotiates AVIF or WebP per browser, so no separate
     * <source> elements are needed.
     *
     * When there is no approved image the branded placeholder is shown rather
     * than generic stock photography: an empty frame is honest, a stock photo
     * of strangers presented as GSF's work is not.
     */
    $service = app(App\Services\MediaService::class);
    $renderable = $media?->approved && $media->isImage() ? $media : null;

    $intrinsicWidth = $renderable?->variants['width'] ?? null;
    $intrinsicHeight = $renderable?->variants['height'] ?? null;
    // A crop is only applied when asked for. `gravity: face` keeps a portrait
    // subject in frame; `aspect` keeps every srcset candidate the same shape.
    $transform = array_filter([
        'quality' => $quality,
        'crop' => $crop,
        'gravity' => $gravity,
        'aspect' => $aspect,
    ]);
@endphp

<figure {{ $attributes->merge(['class' => 'relative overflow-hidden '.$ratio.' '.$class]) }}>
    @if ($renderable)
        <img
            src="{{ $service->url($renderable, $transform + ['width' => $width]) }}"
            srcset="{{ $service->srcset($renderable, $transform) }}"
            sizes="{{ $sizes }}"
            alt="{{ $alt ?? $renderable->alt ?? '' }}"
            @if ($intrinsicWidth) width="{{ $intrinsicWidth }}" @endif
            @if ($intrinsicHeight) height="{{ $intrinsicHeight }}" @endif
            loading="{{ $eager ? 'eager' : 'lazy' }}"
            @if ($eager) fetchpriority="high" @endif
            decoding="async"
            class="size-full object-cover"
        >
    @else
        <div class="grid size-full place-items-center bg-primary-50" role="img" aria-label="{{ $alt ?: 'Image not yet added' }}">
            <svg viewBox="0 0 24 24" class="size-10 text-primary-200" fill="currentColor" aria-hidden="true">
                <path d="M12 22C12 13 13.5 6.5 17 2c-3.2 1.2-5.4 3.6-6.6 7.2C9.2 5.6 7 3.2 4 2c3.5 4.5 5 11 5 20"/>
            </svg>
        </div>
    @endif

    @if ($renderable?->caption)
        <figcaption class="absolute inset-x-0 bottom-0 bg-ink/70 px-3 py-2 text-xs text-white">
            {{ $renderable->caption }}
            @if ($renderable->credit)
                <span class="text-white/70">— {{ $renderable->credit }}</span>
            @endif
        </figcaption>
    @endif
</figure>
