<x-layouts.site
    title="News"
    description="News, updates and insight from Global Support Foundation for Grassroot Entrepreneurship.">

    <x-page-header
        eyebrow="Newsroom"
        title="News & Insights"
        lead="Updates from our programs, our projects and the wider co-operative movement."
        :breadcrumbs="[['label' => 'News']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($categories->isNotEmpty())
            <nav class="flex flex-wrap gap-2" aria-label="Filter by category">
                <a href="{{ route('news.index') }}"
                    @class([
                        'rounded-full border px-4 py-1.5 text-sm font-medium',
                        'border-primary-700 bg-primary-700 text-white' => ! $category,
                        'border-line text-muted hover:border-primary-600 hover:text-primary-700' => (bool) $category,
                    ])
                    @if (! $category) aria-current="page" @endif>All</a>

                @foreach ($categories as $option)
                    <a href="{{ route('news.index', ['category' => $option]) }}"
                        @class([
                            'rounded-full border px-4 py-1.5 text-sm font-medium',
                            'border-primary-700 bg-primary-700 text-white' => $category === $option,
                            'border-line text-muted hover:border-primary-600 hover:text-primary-700' => $category !== $option,
                        ])
                        @if ($category === $option) aria-current="page" @endif>{{ $option }}</a>
                @endforeach
            </nav>
        @endif

        <div class="@if ($categories->isNotEmpty()) mt-10 @endif">
            @if ($articles->isEmpty())
                <x-empty-state
                    title="No articles published yet"
                    description="Articles appear here as the foundation publishes them. In the meantime, our programs describe the work in detail."
                >
                    <a href="{{ route('programs.index') }}" class="btn-outline">See our programs</a>
                </x-empty-state>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($articles as $article)
                        <x-card.article :article="$article" />
                    @endforeach
                </div>

                <div class="mt-12">{{ $articles->links() }}</div>
            @endif
        </div>
    </div>

    <x-donate-cta />

    <x-blog-subscribe />
</x-layouts.site>
