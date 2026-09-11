@props(['eyebrow' => null, 'title', 'lead' => null, 'align' => 'left', 'id' => null])

<div @class(['max-w-3xl', 'mx-auto text-center' => $align === 'center'])>
    @if ($eyebrow)
        <p class="eyebrow text-accent-600">{{ $eyebrow }}</p>
    @endif

    {{-- The id belongs on the heading itself so that a section's
         aria-labelledby resolves to the text a sighted reader sees. --}}
    <h2 @if ($id) id="{{ $id }}" @endif class="mt-3 text-3xl text-ink sm:text-4xl">{{ $title }}</h2>

    @if ($lead)
        <p class="mt-4 text-lg leading-relaxed text-muted">{{ $lead }}</p>
    @endif
</div>
