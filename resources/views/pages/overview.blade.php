@php
    $service = app(App\Services\MediaService::class);
    $ogImage = $content->image?->approved ? $service->url($content->image, ['width' => 1200]) : null;
@endphp

<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt"
    :image="$ogImage">

    {{-- Shared by the pages that carry a hero image and the stats band. The
         photograph sits in the right half only, faint and fading out towards
         the copy, so it never competes with the heading. --}}
    <section class="relative isolate overflow-hidden border-b border-line bg-surface">
        @if ($heroImage?->approved)
            <div class="pointer-events-none absolute inset-y-0 right-0 -z-10 hidden w-1/2 lg:block" aria-hidden="true">
                <img
                    src="{{ $service->url($heroImage, ['width' => 960]) }}"
                    srcset="{{ $service->srcset($heroImage) }}"
                    sizes="50vw"
                    alt=""
                    loading="lazy"
                    decoding="async"
                    class="size-full object-cover opacity-15">

                {{-- Fades the image into the page rather than ending on a hard edge. --}}
                <div class="absolute inset-0 bg-gradient-to-r from-surface via-surface/60 to-surface/20"></div>
            </div>
        @endif

        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <x-breadcrumbs :items="[['label' => $content->title]]" />

            <p class="eyebrow mt-6 text-accent-600">{{ $eyebrow ?? 'Who we are' }}</p>

            <h1 class="mt-3 max-w-3xl text-4xl leading-tight text-ink sm:text-5xl lg:max-w-lg">{{ $content->title }}</h1>

            @if ($content->excerpt)
                <p class="mt-5 max-w-2xl text-lg leading-relaxed text-muted lg:max-w-lg">{{ $content->excerpt }}</p>
            @endif
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="prose-gsf lg:col-span-8">
                {!! $content->body !!}

                @if ($content->slug === 'about-us')
                    @include('pages.about-sections')
                @endif
            </article>

            <aside class="lg:col-span-4">
                <div class="lg:sticky lg:top-24">
                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Support this work</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Training, business advice and getting co-operatives registered and trading all cost money.
                        </p>
                        <a href="{{ route('donate') }}" class="btn-primary mt-5 w-full">Donate now</a>
                        <a href="{{ route('pages.show', 'partner-with-us') }}" class="btn-outline mt-3 w-full">Partner with us</a>
                    </div>

                    <nav class="card mt-6 p-6" aria-labelledby="about-related">
                        <h2 id="about-related" class="text-lg text-ink">Explore</h2>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li><a href="{{ route('pages.show', 'mission-vision-values') }}" class="text-primary-700 hover:underline">Mission, vision &amp; values</a></li>
                            <li><a href="{{ route('pages.show', 'our-history') }}" class="text-primary-700 hover:underline">Our history</a></li>
                            <li><a href="{{ route('pages.show', 'our-founder') }}" class="text-primary-700 hover:underline">Our founder</a></li>
                            <li><a href="{{ route('leadership.index') }}" class="text-primary-700 hover:underline">Leadership</a></li>
                            <li><a href="{{ route('programs.index') }}" class="text-primary-700 hover:underline">Our programs</a></li>
                            <li><a href="{{ route('pages.show', 'contact-us') }}" class="text-primary-700 hover:underline">Contact us</a></li>
                        </ul>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    <x-donate-cta />

    <x-stats-band :metrics="$metrics" />
</x-layouts.site>
