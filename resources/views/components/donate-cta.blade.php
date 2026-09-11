@props(['heading' => 'Your support builds enterprises, not dependency'])

@php
    // Shared by a view composer, because this block appears on most pages and
    // is not passed in by any one controller.
    $background = $ctaBackground ?? null;
    $service = app(App\Services\MediaService::class);
@endphp

<section {{ $attributes->class(['relative isolate overflow-hidden bg-primary-800 text-white']) }} aria-labelledby="donate-cta-heading">
    @if ($background?->approved)
        {{-- Decorative only, so the alt is empty and the frame is hidden from
             assistive technology. The overlay is heavy because the photograph
             is bright and the copy above it is white. --}}
        <div class="absolute inset-0 -z-10" aria-hidden="true">
            <img
                src="{{ $service->url($background, ['width' => 1600]) }}"
                srcset="{{ $service->srcset($background) }}"
                sizes="100vw"
                alt=""
                loading="lazy"
                decoding="async"
                class="size-full object-cover">
            <div class="absolute inset-0 bg-primary-900/80"></div>
        </div>
    @endif

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-7">
                <p class="eyebrow text-accent-300">Support our work</p>
                <h2 id="donate-cta-heading" class="mt-3 text-3xl text-white sm:text-4xl">{{ $heading }}</h2>
                <p class="mt-5 max-w-2xl text-lg leading-relaxed text-primary-100">
                    Training, business advice and the practical work of getting a co-operative registered
                    and trading all cost money. A donation funds that work directly.
                </p>
            </div>

            <div class="flex flex-wrap gap-3 lg:col-span-5 lg:justify-end">
                <a href="{{ route('donate') }}" class="btn-primary">Donate now</a>
                <a href="{{ route('impact') }}" class="btn-ghost-light">See our impact</a>
            </div>
        </div>
    </div>
</section>
