@props(['person'])

<article class="card group relative overflow-hidden text-center transition-shadow hover:shadow-raised">
    {{-- Portraits are cropped to a common 4:5 on the face, so a team page reads
         as one set however differently each photograph was framed. --}}
    <x-picture :media="$person->image" :alt="'Portrait of '.$person->title" ratio="aspect-[4/5]" :width="480"
        crop="fill" gravity="face" aspect="4:5"
        sizes="(min-width: 1024px) 300px, (min-width: 640px) 45vw, 100vw" />

    <div class="p-6">
        <h3 class="text-lg text-ink">
            <a href="{{ route('leadership.show', $person->slug) }}" class="after:absolute after:inset-0 group-hover:text-primary-700">
                {{ $person->title }}
            </a>
        </h3>

        @if ($person->category)
            <p class="mt-1 text-sm font-medium text-accent-600">{{ $person->category }}</p>
        @endif

        <p class="mt-3 text-sm leading-relaxed text-muted">{{ Str::limit($person->excerpt, 130) }}</p>
    </div>
</article>
