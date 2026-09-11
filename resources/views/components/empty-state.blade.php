@props(['title', 'description' => null])

<div class="card px-6 py-16 text-center">
    <svg viewBox="0 0 24 24" class="mx-auto size-10 text-primary-200" fill="currentColor" aria-hidden="true">
        <path d="M12 22C12 13 13.5 6.5 17 2c-3.2 1.2-5.4 3.6-6.6 7.2C9.2 5.6 7 3.2 4 2c3.5 4.5 5 11 5 20"/>
    </svg>
    <p class="mt-4 font-serif text-xl text-ink">{{ $title }}</p>
    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-muted">{{ $description }}</p>
    @endif
    @if (isset($slot) && trim($slot) !== '')
        <div class="mt-6 flex justify-center gap-3">{{ $slot }}</div>
    @endif
</div>
