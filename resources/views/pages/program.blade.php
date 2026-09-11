@php
    $service = app(App\Services\MediaService::class);
    $ogImage = $content->image?->approved ? $service->url($content->image, ['width' => 1200]) : null;
@endphp

<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt"
    :image="$ogImage">

    <x-page-header
        eyebrow="Program"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[
            ['label' => 'Programs', 'url' => route('programs.index')],
            ['label' => $content->title],
        ]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-8">
                @if ($content->image)
                    <x-picture :media="$content->image" :alt="$content->title" ratio="aspect-[16/9]"
                        class="mb-10 rounded-card" :width="1280" eager sizes="(min-width: 1024px) 760px, 100vw" />
                @endif

                <div class="prose-gsf">{!! $content->body !!}</div>
            </article>

            <aside class="lg:col-span-4">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Fund this program</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Support the training, advice and follow-up that make this work possible.
                        </p>
                        <a href="{{ route('donate') }}" class="btn-primary mt-5 w-full">Donate now</a>
                        <a href="{{ route('pages.show', 'partner-with-us') }}" class="btn-outline mt-3 w-full">Partner with us</a>
                    </div>

                    <x-share-links :title="$content->title" />
                </div>
            </aside>
        </div>

        @if ($projects->isNotEmpty())
            <section class="mt-20 border-t border-line pt-16" aria-labelledby="program-projects">
                <x-section-heading eyebrow="On the ground" title="Projects in this program" id="program-projects" />

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-card.project :project="$project" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <x-donate-cta />
</x-layouts.site>
