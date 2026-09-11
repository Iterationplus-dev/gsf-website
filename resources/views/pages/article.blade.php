@php
    $service = app(App\Services\MediaService::class);
    $ogImage = $content->image?->approved ? $service->url($content->image, ['width' => 1200]) : null;

    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $content->title,
        'description' => $content->excerpt,
        'datePublished' => $content->published_at?->toAtomString(),
        'dateModified' => $content->updated_at?->toAtomString(),
        'image' => $ogImage,
        'author' => $content->author ? ['@type' => 'Person', 'name' => $content->author->name] : ['@id' => url('/').'#organisation'],
        'publisher' => ['@id' => url('/').'#organisation'],
        'mainEntityOfPage' => url()->current(),
    ]);
@endphp

<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt"
    :image="$ogImage">

    @push('head')
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    @endpush

    <x-page-header
        :eyebrow="$content->category"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[
            ['label' => 'News', 'url' => route('news.index')],
            ['label' => $content->title],
        ]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-8">
                <p class="flex flex-wrap items-center gap-2 text-sm text-muted">
                    <time datetime="{{ $content->published_at?->toDateString() }}">
                        {{ $content->published_at?->format('j F Y') }}
                    </time>
                    @if ($content->author)
                        <span aria-hidden="true">&middot;</span>
                        <span>{{ $content->author->name }}</span>
                    @endif
                </p>

                @if ($content->image)
                    <x-picture :media="$content->image" :alt="$content->title" ratio="aspect-[16/9]"
                        class="my-8 rounded-card" :width="1280" eager sizes="(min-width: 1024px) 760px, 100vw" />
                @endif

                <div class="prose-gsf mt-8">{!! $content->body !!}</div>

                @if ($content->tags)
                    <ul class="mt-10 flex flex-wrap gap-2 border-t border-line pt-6">
                        @foreach ($content->tags as $tag)
                            <li class="rounded-full bg-sunken px-3 py-1 text-xs font-medium text-muted">{{ $tag }}</li>
                        @endforeach
                    </ul>
                @endif
            </article>

            <aside class="lg:col-span-4">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <x-share-links :title="$content->title" />

                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Support this work</h2>
                        <a href="{{ route('donate') }}" class="btn-primary mt-4 w-full">Donate now</a>
                    </div>
                </div>
            </aside>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-20 border-t border-line pt-16" aria-labelledby="related-heading">
                <x-section-heading eyebrow="Keep reading" title="Related articles" id="related-heading" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $article)
                        <x-card.article :article="$article" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
    <x-donate-cta />

    <x-blog-subscribe />
</x-layouts.site>
