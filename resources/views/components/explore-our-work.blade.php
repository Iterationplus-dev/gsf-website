@props(['current' => null])

@php
    /**
     * Cross-navigation between the sections that make up "Our Work".
     *
     * A visitor who has reached the end of one of these pages is usually looking
     * for a different view of the same work rather than another appeal, so this
     * offers the sibling sections and drops the one they are already on.
     */
    $sections = collect([
        [
            'key' => 'programs',
            'label' => 'Programs',
            'url' => route('programs.index'),
            'description' => 'The areas of work, and what each one provides.',
        ],
        [
            'key' => 'projects',
            'label' => 'Projects',
            'url' => route('projects.index'),
            'description' => 'What is being delivered, and where.',
        ],
        [
            'key' => 'impact',
            'label' => 'Our Impact',
            'url' => route('impact'),
            'description' => 'Results, reach, and how we report them.',
        ],
        [
            'key' => 'stories',
            'label' => 'Success Stories',
            'url' => route('stories.index'),
            'description' => 'Co-operatives in their own words.',
        ],
        [
            'key' => 'partners',
            'label' => 'Partners',
            'url' => route('partners'),
            'description' => 'The organisations we work alongside.',
        ],
        [
            'key' => 'gallery',
            'label' => 'Photo Gallery',
            'url' => route('gallery'),
            'description' => 'The work in pictures.',
        ],
    ])->reject(fn (array $section): bool => $section['key'] === $current)->values();
@endphp

<section class="border-t border-line bg-sunken" aria-labelledby="explore-our-work">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <x-section-heading
            eyebrow="Keep looking"
            title="Explore the rest of our work"
            lead="The same work seen from a different angle."
            id="explore-our-work"
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
    </div>
</section>
