@php
    $service = app(App\Services\MediaService::class);
    $profile = $founder ?? null;
    $portrait = $profile?->image;
    $linkedin = $profile?->detail('linkedin') ?? ($settings['founder.linkedin'] ?? null);
    $ogImage = $portrait?->approved ? $service->url($portrait, ['width' => 1200]) : null;
@endphp

<x-layouts.site
    :title="$content->seo_title ?: 'Our Founder'"
    :description="$content->seo_description ?: $content->excerpt"
    :image="$ogImage">

    {{-- The founder page is a leadership statement rather than a staff profile:
         a portrait at scale, the person's name and title, and the conviction the
         organisation was built on — before any biography. --}}
    <section class="bg-primary-900 text-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20">
            <x-breadcrumbs :items="[['label' => 'Our Founder']]" dark />

            <div class="mt-10 grid gap-12 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-5">
                    <x-picture :media="$portrait" :alt="'Portrait of '.($profile?->title ?? 'the founder')"
                        ratio="aspect-[4/5]" class="rounded-card" :width="800" eager
                        sizes="(min-width: 1024px) 460px, 100vw" />
                </div>

                <div class="lg:col-span-7">
                    <p class="eyebrow text-accent-300">Founder &amp; {{ $profile?->category ?? 'Executive Director' }}</p>
                    <h1 class="mt-4 text-4xl text-white sm:text-5xl">{{ $profile?->title ?? 'Golden Chudi Anikwe' }}</h1>

                    {{-- Deliberately not a blockquote. The organisational record
                         describes his position in the third person; setting it in
                         quotation marks would invent a statement he did not make.
                         A signed message from the founder can replace this block
                         once GSF supplies one. --}}
                    <div class="mt-8 border-l-4 border-accent-500 pl-6">
                        <p class="eyebrow text-primary-300">Leadership philosophy</p>
                        <p class="mt-3 font-serif text-xl leading-relaxed text-primary-100">
                            He came to regard co-operatives as a genuine platform through which people's
                            cultural, social, economic and political emancipation can be realised — and held
                            that giving community members the opportunity to contribute directly to the
                            economic development of their own locality is what enlarges their capacity to
                            participate in society.
                        </p>
                    </div>

                    @if ($linkedin)
                        <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer"
                            class="btn mt-8 bg-white text-primary-900 hover:bg-primary-100">
                            <svg viewBox="0 0 24 24" class="size-4" fill="currentColor" aria-hidden="true">
                                <path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9h4v12H3V9Zm7 0h3.8v1.7h.05c.53-.95 1.83-1.95 3.77-1.95C21.4 8.75 22 11 22 14.1V21h-4v-6.1c0-1.45-.03-3.3-2.02-3.3-2.02 0-2.33 1.57-2.33 3.2V21h-4V9Z"/>
                            </svg>
                            Connect on LinkedIn
                            <span class="sr-only">(opens in a new tab)</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-8">
                @if ($content->body)
                    <div class="prose-gsf">{!! $content->body !!}</div>
                @endif

                @if ($profile?->body)
                    <div class="prose-gsf @if ($content->body) mt-10 @endif">{!! $profile->body !!}</div>
                @endif
            </article>

            <aside class="lg:col-span-4">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <div class="card p-6">
                        <h2 class="text-lg text-ink">The foundation he built</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Global Support Foundation for Grassroot Entrepreneurship was established in
                            August 2015 to bring the co-operative model — proven elsewhere, underused in
                            Africa — to young people who have the will to trade but not the means.
                        </p>
                        <a href="{{ route('pages.show', 'our-history') }}" class="mt-4 inline-block text-sm font-semibold text-primary-700 hover:underline">
                            Read our history &rarr;
                        </a>
                    </div>

                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Leadership</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Meet the management team delivering the foundation's programs.
                        </p>
                        <a href="{{ route('leadership.index') }}" class="btn-outline mt-4 w-full">View leadership</a>
                    </div>

                    <x-share-links title="Our Founder — Global Support Foundation" />
                </div>
            </aside>
        </div>
    </div>

    <x-donate-cta />

    <x-stats-band :metrics="$metrics" />
</x-layouts.site>
