@props(['current' => null])

@php
    /**
     * Cross-navigation for the accountability section.
     *
     * Someone reading one of these pages is usually checking whether the
     * organisation can be trusted with money, so the useful next step is the
     * rest of the evidence — and a direct route to ask for anything missing.
     */
    $sections = collect([
        [
            'key' => 'resources',
            'label' => 'Publications & Reports',
            'url' => route('resources.index'),
            'description' => 'Reports, research and printed material as they are published.',
        ],
        [
            'key' => 'policies',
            'label' => 'Policies',
            'url' => route('policies.index'),
            'description' => 'The governing documents the foundation is adopting, and their status.',
        ],
        [
            'key' => 'transparency',
            'label' => 'Transparency',
            'url' => route('pages.show', 'transparency'),
            'description' => 'How money and personal data are handled, and what is not yet published.',
        ],
        [
            'key' => 'governance',
            'label' => 'Governance',
            'url' => route('pages.show', 'governance'),
            'description' => 'How the foundation is structured and who takes decisions.',
        ],
        [
            'key' => 'privacy',
            'label' => 'Privacy Notice',
            'url' => route('pages.show', 'privacy-policy'),
            'description' => 'What we collect, why, and what you can ask us to do with it.',
        ],
    ])->reject(fn (array $section): bool => $section['key'] === $current)->values();
@endphp

<section class="border-t border-line bg-sunken" aria-labelledby="accountability-next">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <x-section-heading
            eyebrow="Hold us to it"
            title="The rest of the record"
            lead="Everything the foundation publishes about how it is run, in one place."
            id="accountability-next"
        />

        <ul role="list" class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($sections as $section)
                <li>
                    <a href="{{ $section['url'] }}"
                        class="card group flex h-full flex-col p-6 transition-shadow hover:shadow-raised">
                        <span class="flex items-center gap-2 text-lg text-ink group-hover:text-primary-700">
                            {{ $section['label'] }}
                            <svg viewBox="0 0 20 20" class="size-4 shrink-0 text-primary-600 transition-transform group-hover:translate-x-0.5" fill="currentColor" aria-hidden="true"><path d="M7.5 4.5 6.1 5.9 10.2 10l-4.1 4.1 1.4 1.4L13 10 7.5 4.5Z"/></svg>
                        </span>
                        <span class="mt-2 text-sm leading-relaxed text-muted">{{ $section['description'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="card mt-8 flex flex-wrap items-center justify-between gap-6 p-6 sm:p-8">
            <div>
                <h3 class="text-lg text-ink">Something you need that is not here?</h3>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted">
                    Ask us. We will tell you what exists and what does not, rather than leaving you to guess.
                </p>
            </div>
            <a href="{{ route('pages.show', 'contact-us') }}" class="btn-outline">Request information</a>
        </div>
    </div>
</section>
