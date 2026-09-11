@props(['story'])

<article class="card group relative flex flex-col overflow-hidden transition-shadow hover:shadow-raised sm:flex-row">
    <x-picture :media="$story->image" :alt="$story->title" ratio="aspect-[4/3]" :width="640"
        class="sm:w-2/5 sm:shrink-0" sizes="(min-width: 640px) 40vw, 100vw" />

    <div class="flex flex-1 flex-col p-6">
        <p class="eyebrow text-accent-600">Story of impact</p>

        <h3 class="mt-2 text-xl text-ink">
            <a href="{{ route('stories.show', $story->slug) }}" class="after:absolute after:inset-0 group-hover:text-primary-700">
                {{ $story->title }}
            </a>
        </h3>

        <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ Str::limit($story->excerpt, 200) }}</p>
    </div>
</article>
