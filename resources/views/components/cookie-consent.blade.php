@php
    /*
    | Cookie consent
    |--------------------------------------------------------------------------
    | Rendered immediately after the skip link so that a keyboard user reaches
    | the choice as their second stop, rather than having to traverse the whole
    | page to find it.
    |
    | The banner starts hidden and is revealed by resources/ts/consent.ts only
    | when there is no decision on record. Without script it stays hidden, which
    | is correct: nothing beyond the necessary category is ever loaded either,
    | so there is nothing to consent to.
    */
    $categories = [
        [
            'key' => 'necessary',
            'name' => 'Strictly necessary',
            'summary' => 'Required for the site to work. These remember your session, keep forms secure against cross-site request forgery, and store this cookie choice itself. They cannot be switched off.',
        ],
        [
            'key' => 'functional',
            'name' => 'Functional',
            'summary' => 'Features that need a third party to work, such as the Google map on the contact page. Declining these leaves a plain address and a link instead.',
        ],
        [
            'key' => 'analytics',
            'name' => 'Analytics',
            'summary' => 'Anonymous, aggregated measurement of which pages are read, so the foundation can see what its work reaches. No personal data and no donation details are ever passed to it.',
        ],
        [
            'key' => 'marketing',
            'name' => 'Marketing',
            'summary' => 'Embedded video from YouTube. YouTube may set its own advertising cookies once a player is loaded, so the videos stay as still previews until you allow this.',
        ],
    ];

    $privacyUrl = route('pages.show', 'privacy-policy');
    $cookieUrl = route('pages.show', 'cookie-policy');
@endphp

<div>
    {{-- Banner ----------------------------------------------------------------
         z-30 puts this below the sticky header, which is z-40. That matters on a
         phone: the header's menu panel is opaque, so opening it simply covers
         the banner instead of being covered by it. --}}
    <div data-consent-banner hidden role="region" aria-labelledby="cookie-banner-title"
        class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-surface shadow-raised">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:gap-10 lg:px-8 lg:py-6">
            <div class="lg:flex-1">
                <h2 id="cookie-banner-title" class="text-base text-ink sm:text-lg">Your choice about cookies</h2>
                <p class="mt-1.5 max-w-3xl text-sm leading-relaxed text-muted">
                    Cookies this site needs to work are always on. With your agreement it will also measure how
                    its pages are read, and load the maps and videos some pages carry. Read the
                    <a href="{{ $cookieUrl }}" class="font-medium text-primary-700 underline decoration-primary-300 hover:decoration-primary-700">cookie policy</a>
                    or the
                    <a href="{{ $privacyUrl }}" class="font-medium text-primary-700 underline decoration-primary-300 hover:decoration-primary-700">privacy notice</a>.
                </p>
            </div>

            {{-- Two across on a phone so the banner never takes half the screen;
                 a single row from `sm` up, where there is width for it. --}}
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-2.5 lg:shrink-0 lg:justify-end">
                <button type="button" data-consent-action="accept" class="btn-primary">Accept all</button>
                <button type="button" data-consent-action="reject" class="btn-secondary">Reject non-essential</button>
                <button type="button" data-consent-action="open" class="btn-outline col-span-2 sm:col-auto">Manage preferences</button>
            </div>
        </div>
    </div>

    {{-- Preferences ----------------------------------------------------------
         A native dialog, as in the lightbox: focus trapping, Escape and inert
         background all come from the platform. Closing without saving leaves
         the existing decision untouched. --}}
    <dialog data-consent-dialog aria-labelledby="cookie-preferences-title"
        class="m-auto w-[calc(100%_-_2rem)] max-w-2xl overflow-hidden rounded-card bg-surface p-0 text-ink shadow-raised backdrop:bg-ink/70">
        {{-- Only the category list scrolls, so the heading and the three actions
             stay in view however short the window is. --}}
        <div class="flex max-h-[85vh] flex-col">
            <div class="shrink-0 border-b border-line px-6 py-5 sm:px-8">
                <p class="eyebrow text-primary-700">Privacy</p>
                <h2 id="cookie-preferences-title" class="mt-1.5 text-2xl text-ink">Cookie preferences</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">
                    Choose what this site may load. Your choice is remembered for six months, and
                    <strong class="font-medium text-ink">Cookie settings</strong> in the footer reopens this.
                </p>
            </div>

            <ul role="list" class="min-h-0 flex-1 divide-y divide-line overflow-y-auto px-6 sm:px-8">
                @foreach ($categories as $category)
                    @php $inputId = 'cookie-category-'.$category['key']; @endphp

                    <li class="flex items-start gap-4 py-5">
                        <div class="flex h-6 shrink-0 items-center">
                            <input type="checkbox" id="{{ $inputId }}"
                                data-consent-toggle="{{ $category['key'] }}"
                                @checked($category['key'] === 'necessary')
                                @disabled($category['key'] === 'necessary')
                                class="size-5 shrink-0 rounded border-line text-primary-700 focus:ring-primary-600 disabled:opacity-60">
                        </div>

                        <div class="min-w-0">
                            <label for="{{ $inputId }}" class="flex flex-wrap items-center gap-2 text-sm font-semibold text-ink">
                                {{ $category['name'] }}

                                @if ($category['key'] === 'necessary')
                                    <span class="rounded-full bg-sunken px-2 py-0.5 text-xs font-medium text-muted">Always on</span>
                                @endif
                            </label>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $category['summary'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="shrink-0 grid grid-cols-2 gap-2.5 border-t border-line px-6 py-5 sm:flex sm:flex-wrap sm:items-center sm:justify-end sm:px-8">
                <a href="{{ $cookieUrl }}" class="col-span-2 text-sm font-medium text-primary-700 underline decoration-primary-300 hover:decoration-primary-700 sm:mr-auto sm:col-auto">
                    Full cookie policy
                </a>

                <button type="button" data-consent-action="reject" class="btn-outline">Reject non-essential</button>
                <button type="button" data-consent-action="accept" class="btn-secondary">Accept all</button>
                <button type="button" data-consent-action="save" class="btn-primary col-span-2 sm:col-auto">Save preferences</button>
            </div>
        </div>
    </dialog>
</div>
