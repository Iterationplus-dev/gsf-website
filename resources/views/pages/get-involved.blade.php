<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt">

    <x-page-header
        tone="dark"
        eyebrow="Take part"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[['label' => 'Get Involved']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Give', 'Fund training, business advice and the practical work of getting a co-operative trading.', 'Donate now', route('donate')],
                ['Volunteer', 'Contribute skills in co-operative development, business planning, training or finance.', 'Apply to volunteer', route('pages.show', 'volunteer')],
                ['Partner', 'For development agencies, foundations, corporations, government and universities.', 'Discuss partnership', route('pages.show', 'partner-with-us')],
                ['Stay informed', 'News, project updates and opportunities to take part, straight to your inbox.', 'Read our news', route('news.index')],
            ] as [$heading, $description, $label, $url])
                <article class="card flex flex-col p-6">
                    <h2 class="text-xl text-ink">{{ $heading }}</h2>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ $description }}</p>
                    <a href="{{ $url }}" class="{{ $loop->first ? 'btn-primary' : 'btn-outline' }} mt-6 w-full">{{ $label }}</a>
                </article>
            @endforeach
        </div>

        <div class="mt-16 grid gap-12 lg:grid-cols-12">
            <article class="prose-gsf lg:col-span-7">
                {!! $content->body !!}
            </article>

            <div class="lg:col-span-5">
                <div class="card p-6 sm:p-8">
                    <x-newsletter-form tone="light" />
                </div>
            </div>
        </div>
    </div>

    <x-donate-cta />

    <x-involvement-details page="get-involved" />
</x-layouts.site>
