@props(['program'])

<article class="card group relative flex flex-col overflow-hidden transition-shadow hover:shadow-raised">
    <x-picture :media="$program->image" :alt="$program->title" ratio="aspect-[16/9]" :width="640"
        sizes="(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw" />

    <div class="flex flex-1 flex-col p-6">
        <h3 class="text-xl text-ink">
            <a href="{{ route('programs.show', $program->slug) }}" class="after:absolute after:inset-0 group-hover:text-primary-700">
                {{ $program->title }}
            </a>
        </h3>
        <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ $program->excerpt }}</p>
        <p class="mt-5 text-sm font-semibold text-primary-700">Explore this program &rarr;</p>
    </div>
</article>
