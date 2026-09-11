@props(['award'])

@php
    $service = app(App\Services\MediaService::class);
    $media = $award->image?->approved ? $award->image : null;
@endphp

<article class="card flex flex-col overflow-hidden transition-shadow hover:shadow-raised">
    @if ($media)
        {{-- Certificates are scans, and legibility is the whole point of the
             record: the enlarged view loads the original at the highest quality
             setting rather than a display-sized variant. --}}
        <button type="button"
            class="block w-full cursor-zoom-in bg-sunken"
            data-lightbox="awards"
            data-lightbox-full="{{ $service->url($media, ['quality' => 'auto:best', 'width' => 2000]) }}"
            data-lightbox-alt="{{ $media->alt ?? $award->title }}"
            data-lightbox-caption="{{ $award->title }}">
            <x-picture :media="$media" :alt="$award->title" ratio="aspect-[4/3]" :width="640" quality="auto:best"
                class="[&_img]:object-contain" sizes="(min-width: 1024px) 380px, 100vw" />
            <span class="sr-only">Enlarge certificate</span>
        </button>
    @else
        <x-picture :media="null" :alt="$award->title" ratio="aspect-[4/3]" />
    @endif

    <div class="flex flex-1 flex-col p-6">
        <h3 class="text-lg text-ink">
            <a href="{{ route('awards.show', $award->slug) }}" class="hover:text-primary-700 hover:underline">{{ $award->title }}</a>
        </h3>

        @if ($award->detail('awarding_institution'))
            <p class="mt-1 text-sm font-medium text-muted">{{ $award->detail('awarding_institution') }}</p>
        @endif

        @if ($award->detail('year'))
            <p class="mt-1 text-sm text-muted">{{ $award->detail('year') }}</p>
        @endif

        <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ Str::limit($award->excerpt, 140) }}</p>
    </div>
</article>
