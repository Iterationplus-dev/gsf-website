@props([
    'category',
    'src',
    'title',
    'provider',
    'href' => null,
    'allow' => null,
    'referrerpolicy' => 'strict-origin-when-cross-origin',
])

{{--
    A third-party embed that is not loaded until its category is consented to.

    The address is held in `data-consent-src` rather than `src`, so the browser
    makes no request to the provider and the provider sets no cookie until
    resources/ts/consent.ts activates it. Until then the placeholder explains
    what is missing and offers two ways forward: allow this provider, or open
    the full preferences. Where the content exists elsewhere, it also links out,
    which costs no cookie at all.
--}}

<div data-consent-holder {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-card border border-line bg-sunken']) }}>
    <iframe
        data-consent-src="{{ $src }}"
        data-consent-category="{{ $category }}"
        hidden
        title="{{ $title }}"
        loading="lazy"
        referrerpolicy="{{ $referrerpolicy }}"
        @if ($allow) allow="{{ $allow }}" @endif
        allowfullscreen
        class="size-full border-0"></iframe>

    <div data-consent-placeholder class="flex size-full flex-col items-center justify-center gap-4 p-6 text-center">
        <div>
            <p class="text-sm font-semibold text-ink">{{ $provider }} content is blocked</p>
            <p class="mx-auto mt-1.5 max-w-sm text-sm leading-relaxed text-muted">
                Loading this would let {{ $provider }} set cookies in your browser, so it waits for your agreement.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-2.5">
            <button type="button" data-consent-action="allow" data-consent-category="{{ $category }}" class="btn-primary">
                Allow {{ $provider }}
                <span class="sr-only">for {{ $title }}</span>
            </button>

            <button type="button" data-consent-action="open" class="btn-outline">Manage preferences</button>
        </div>

        @if ($href)
            <p class="text-sm">
                <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                    class="font-medium text-primary-700 underline decoration-primary-300 hover:decoration-primary-700">
                    Open on {{ $provider }} instead
                    <span class="sr-only">(opens in a new tab)</span>
                </a>
            </p>
        @endif
    </div>
</div>
