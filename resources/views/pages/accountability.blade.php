@php
    $service = app(App\Services\MediaService::class);
    $ogImage = $content->image?->approved ? $service->url($content->image, ['width' => 1200]) : null;
@endphp

<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt"
    :image="$ogImage">

    <x-page-header
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[['label' => $content->title]]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="prose-gsf lg:col-span-8">
                @if ($content->image)
                    <x-picture :media="$content->image" :alt="$content->title" ratio="aspect-[16/9]"
                        class="mb-10 rounded-card" :width="1280" eager
                        sizes="(min-width: 1024px) 760px, 100vw" />
                @endif

                {!! $content->body !!}
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

                    <nav class="card mt-6 p-6" aria-labelledby="page-related">
                        <h2 id="page-related" class="text-lg text-ink">Explore</h2>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li><a href="{{ route('programs.index') }}" class="text-primary-700 hover:underline">Our programs</a></li>
                            <li><a href="{{ route('projects.index') }}" class="text-primary-700 hover:underline">Projects</a></li>
                            <li><a href="{{ route('impact') }}" class="text-primary-700 hover:underline">Our impact</a></li>
                            <li><a href="{{ route('leadership.index') }}" class="text-primary-700 hover:underline">Leadership</a></li>
                            <li><a href="{{ route('pages.show', 'contact-us') }}" class="text-primary-700 hover:underline">Contact us</a></li>
                        </ul>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    <x-donate-cta />

    <x-accountability-next :current="$accountabilitySection ?? null" />
</x-layouts.site>
