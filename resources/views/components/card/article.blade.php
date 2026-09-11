@props(['article'])

<article class="card group relative flex flex-col overflow-hidden transition-shadow hover:shadow-raised">
    <x-picture :media="$article->image" :alt="$article->title" ratio="aspect-[16/9]" :width="640"
        sizes="(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw" />

    <div class="flex flex-1 flex-col p-6">
        <p class="flex flex-wrap items-center gap-2 text-xs text-muted">
            @if ($article->category)
                <span class="font-semibold tracking-wide text-accent-600 uppercase">{{ $article->category }}</span>
                <span aria-hidden="true">&middot;</span>
            @endif
            <time datetime="{{ $article->published_at?->toDateString() }}">{{ $article->published_at?->format('j F Y') }}</time>
        </p>

        <h3 class="mt-3 text-lg text-ink">
            <a href="{{ route('news.show', $article->slug) }}" class="after:absolute after:inset-0 group-hover:text-primary-700">
                {{ $article->title }}
            </a>
        </h3>

        <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">{{ Str::limit($article->excerpt, 150) }}</p>
    </div>
</article>
