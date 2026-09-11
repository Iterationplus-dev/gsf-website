@props(['publication'])

<article class="card flex gap-5 p-5">
    <x-picture :media="$publication->image" :alt="$publication->title" ratio="aspect-[3/4]" :width="240"
        class="w-24 shrink-0 rounded-md" sizes="96px" />

    <div class="flex min-w-0 flex-1 flex-col">
        <p class="flex flex-wrap items-center gap-2 text-xs text-muted">
            @if ($publication->category)
                <span class="font-semibold tracking-wide text-accent-600 uppercase">{{ $publication->category }}</span>
            @endif
            @if ($publication->published_at)
                <span>{{ $publication->published_at->format('Y') }}</span>
            @endif
        </p>

        <h3 class="mt-2 text-base text-ink">
            <a href="{{ route('resources.show', $publication->slug) }}" class="hover:text-primary-700 hover:underline">{{ $publication->title }}</a>
        </h3>

        <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">{{ Str::limit($publication->excerpt, 130) }}</p>
    </div>
</article>
