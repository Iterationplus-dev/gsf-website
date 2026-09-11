@props([
    'content',
    'eyebrow' => null,
    'breadcrumbs' => [],
    'meta' => null,
])

@php
    $service = app(App\Services\MediaService::class);
    $ogImage = $content->image?->approved ? $service->url($content->image, ['width' => 1200]) : null;
@endphp

<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt"
    :image="$ogImage">

    <x-page-header
        :eyebrow="$eyebrow"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="$breadcrumbs"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-8">
                @if ($content->image)
                    <x-picture :media="$content->image" :alt="$content->title" ratio="aspect-[16/9]"
                        class="mb-10 rounded-card" :width="1280" eager
                        sizes="(min-width: 1024px) 760px, 100vw" />
                @endif

                <div class="prose-gsf">
                    {!! $content->body !!}
                </div>

                {{ $slot }}
            </article>

            <aside class="lg:col-span-4">
                <div class="space-y-6 lg:sticky lg:top-24">
                    @if ($meta)
                        <div class="card p-6">{{ $meta }}</div>
                    @endif

                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Support this work</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Your donation funds training, business advice and the practical work of getting
                            co-operatives established.
                        </p>
                        <a href="{{ route('donate') }}" class="btn-primary mt-5 w-full">Donate now</a>
                    </div>

                    <x-share-links :title="$content->title" />
                </div>
            </aside>
        </div>
    </div>
</x-layouts.site>
