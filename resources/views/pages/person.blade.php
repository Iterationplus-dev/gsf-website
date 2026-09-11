@php
    $service = app(App\Services\MediaService::class);
    $ogImage = $content->image?->approved ? $service->url($content->image, ['width' => 1200]) : null;
    $linkedin = $content->detail('linkedin');
@endphp

<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt"
    :image="$ogImage">

    <x-page-header
        :eyebrow="$content->category"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[
            ['label' => 'Leadership', 'url' => route('leadership.index')],
            ['label' => $content->title],
        ]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <aside class="lg:col-span-4">
                <div class="lg:sticky lg:top-24">
                    <x-picture :media="$content->image" :alt="'Portrait of '.$content->title"
                        ratio="aspect-[4/5]" class="rounded-card" :width="640" eager
                        sizes="(min-width: 1024px) 360px, 100vw" />

                    <div class="card mt-6 p-6">
                        <h2 class="text-lg text-ink">{{ $content->title }}</h2>
                        @if ($content->category)
                            <p class="mt-1 text-sm font-medium text-accent-600">{{ $content->category }}</p>
                        @endif

                        @if ($linkedin)
                            <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer"
                                class="btn-outline mt-5 w-full">
                                LinkedIn profile
                                <span class="sr-only">(opens in a new tab)</span>
                            </a>
                        @endif
                    </div>
                </div>
            </aside>

            <article class="prose-gsf lg:col-span-8">
                {!! $content->body !!}
            </article>
        </div>
    </div>

    <x-donate-cta />
</x-layouts.site>
