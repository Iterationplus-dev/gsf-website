@php
    $sections = [
        [
            'label' => 'About',
            'url' => route('pages.show', 'about-us'),
            'children' => [
                ['label' => 'About Us', 'url' => route('pages.show', 'about-us'), 'description' => 'Who we are and what we do'],
                ['label' => 'Mission, Vision & Values', 'url' => route('pages.show', 'mission-vision-values'), 'description' => 'What we exist to do'],
                ['label' => 'Our History', 'url' => route('pages.show', 'our-history'), 'description' => 'From Ireland to Nigeria, 2006 onwards'],
                ['label' => 'Our Founder', 'url' => route('pages.show', 'our-founder'), 'description' => 'Golden Chudi Anikwe'],
                ['label' => 'Leadership', 'url' => route('leadership.index'), 'description' => 'The management team'],
                ['label' => 'Awards & Recognition', 'url' => route('awards.index'), 'description' => 'Certificates and honours'],
            ],
        ],
        [
            'label' => 'Our Work',
            'url' => route('programs.index'),
            'children' => [
                ['label' => 'Programs', 'url' => route('programs.index'), 'description' => 'The areas we work in'],
                ['label' => 'Projects', 'url' => route('projects.index'), 'description' => 'What we are delivering, and where'],
                ['label' => 'Our Impact', 'url' => route('impact'), 'description' => 'Results, reach and reporting'],
                ['label' => 'Success Stories', 'url' => route('stories.index'), 'description' => 'The people behind the work'],
                ['label' => 'Partners', 'url' => route('partners'), 'description' => 'Who we work with'],
                ['label' => 'Photo Gallery', 'url' => route('gallery'), 'description' => 'Our work in pictures'],
            ],
        ],
        [
            'label' => 'Accountability',
            'url' => route('resources.index'),
            'children' => [
                ['label' => 'Publications & Reports', 'url' => route('resources.index'), 'description' => 'Reports, research and brochures'],
                ['label' => 'Policies', 'url' => route('policies.index'), 'description' => 'How we govern our conduct'],
                ['label' => 'Transparency', 'url' => route('pages.show', 'transparency'), 'description' => 'Registration and reporting'],
                ['label' => 'Governance', 'url' => route('pages.show', 'governance'), 'description' => 'How we are governed'],
            ],
        ],
        [
            'label' => 'Get Involved',
            'url' => route('pages.show', 'get-involved'),
            'children' => [
                ['label' => 'Get Involved', 'url' => route('pages.show', 'get-involved'), 'description' => 'Every way to take part'],
                ['label' => 'Partner With Us', 'url' => route('pages.show', 'partner-with-us'), 'description' => 'For funders and institutions'],
                ['label' => 'Volunteer', 'url' => route('pages.show', 'volunteer'), 'description' => 'Contribute your skills'],
                ['label' => 'Membership', 'url' => route('pages.show', 'membership'), 'description' => 'Affiliate your co-operative society'],
                ['label' => 'Contact Us', 'url' => route('pages.show', 'contact-us'), 'description' => 'Port Harcourt, Rivers State'],
            ],
        ],
        [
            'label' => 'Blog',
            'url' => route('news.index'),
            // Recent posts come from a view composer; the archive is always the
            // first child so the menu still leads somewhere before anything is
            // published.
            'children' => collect([
                ['label' => 'All posts', 'url' => route('news.index'), 'description' => 'News and insights'],
            ])->concat(
                collect($recentPosts ?? [])->map(fn ($post): array => [
                    'label' => Str::limit($post->title, 48),
                    'url' => route('news.show', $post->slug),
                    'description' => $post->published_at?->format('j F Y') ?? '',
                ])
            )->all(),
        ],
    ];
    $currentUrl = rtrim(url()->current(), '/');
    $activeUrl = collect($sections)
        ->flatMap(fn (array $section): array => $section['children'])
        ->pluck('url')
        ->filter(fn (string $url): bool => $currentUrl === rtrim($url, '/') || str_starts_with($currentUrl, rtrim($url, '/').'/'))
        ->sortByDesc(fn (string $url): int => strlen($url))
        ->first();

    $sections = array_map(function (array $section) use ($activeUrl): array {
        $section['active'] = in_array($activeUrl, array_column($section['children'], 'url'), true);

        return $section;
    }, $sections);
@endphp

<header
    x-data="{ open: false, section: null, hovered: null, canHover: window.matchMedia('(hover: hover)').matches }"
    class="sticky top-0 z-40 border-b border-line bg-surface/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif class="flex min-w-0 shrink items-center gap-3">
            <img src="{{ ($settings['org.logo'] ?? '') ?: asset('images/logo.jpg') }}" alt="" aria-hidden="true"
                width="167" height="165" decoding="async"
                class="logo-glow size-[66px] shrink-0 rounded-full border border-line object-cover">
            <span class="hidden min-w-0 leading-tight min-[480px]:block">
                <span class="block truncate font-serif text-base font-semibold text-ink sm:text-lg">{{ $settings['org.name'] ?? 'Global Support Foundation' }}</span>
                <span class="block truncate text-[0.7rem] font-semibold tracking-wider text-accent-600 uppercase">Grassroot Entrepreneurship</span>
            </span>
        </a>

        <nav aria-label="Primary" class="ml-auto hidden lg:block"
            @click.outside="section = null; hovered = null"
            @keydown.escape.window="section = null; hovered = null">
            <ul class="flex items-center gap-1">
                @foreach ($sections as $section)
                    <li class="relative" @if ($section['children']) @mouseenter="canHover && (hovered = '{{ $section['label'] }}')" @mouseleave="canHover && (hovered = null)" @endif>
                        @if ($section['children'])
                            <button type="button"
                                @click="section = section === '{{ $section['label'] }}' ? null : '{{ $section['label'] }}'"
                                :aria-expanded="(section === '{{ $section['label'] }}' || hovered === '{{ $section['label'] }}') ? 'true' : 'false'"
                                aria-controls="menu-{{ Str::slug($section['label']) }}" data-active="{{ $section['active'] ? 'true' : 'false' }}"
                                class="flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-ink hover:bg-primary-50 hover:text-primary-700">
                                {{ $section['label'] }}
                                <svg viewBox="0 0 20 20" class="size-4 text-muted" fill="currentColor" aria-hidden="true"><path d="M5.5 7.5 10 12l4.5-4.5H5.5Z"/></svg>
                            </button>

                            <div x-show="section === '{{ $section['label'] }}' || hovered === '{{ $section['label'] }}'" x-cloak x-transition.opacity
                                id="menu-{{ Str::slug($section['label']) }}"
                                {{-- The last menu sits at the right edge, so its panel is
                                     anchored right or it would overflow the viewport. --}}
                                @class([
                                    'absolute top-full w-80 pt-2',
                                    'left-0' => ! $loop->last,
                                    'right-0' => $loop->last,
                                ])>
                                <ul class="rounded-card border border-line bg-surface p-2 shadow-raised">
                                    @foreach ($section['children'] as $child)
                                        <li>
                                            <a href="{{ $child['url'] }}" @if ($activeUrl === $child['url']) aria-current="{{ $currentUrl === rtrim($child['url'], '/') ? 'page' : 'true' }}" @endif class="block rounded-md px-3 py-2.5 hover:bg-primary-50">
                                                <span class="block text-sm font-semibold text-ink">{{ $child['label'] }}</span>
                                                <span class="block text-xs text-muted">{{ $child['description'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <a href="{{ $section['url'] }}" class="block rounded-md px-3 py-2 text-sm font-medium text-ink hover:bg-primary-50 hover:text-primary-700">{{ $section['label'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ml-auto flex items-center gap-2 lg:ml-0">
            <a href="{{ route('search') }}" @if (request()->routeIs('search')) aria-current="page" @endif class="hidden rounded-md p-2.5 text-muted hover:bg-sunken hover:text-ink sm:block" aria-label="Search the website">
                <svg viewBox="0 0 20 20" class="size-5" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.4 9.8l3.4 3.4a1 1 0 0 0 1.4-1.4l-3.4-3.4A5.5 5.5 0 0 0 9 3.5Zm-3.5 5.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z" clip-rule="evenodd"/></svg>
            </a>

            <a href="{{ route('donate') }}" @if (request()->routeIs('donate')) aria-current="page" @endif class="btn-primary px-4 py-2.5">Donate</a>

            <button type="button" @click="open = !open" :aria-expanded="open ? 'true' : 'false'" aria-controls="mobile-navigation"
                class="rounded-md p-2.5 text-ink hover:bg-sunken lg:hidden">
                <span class="sr-only">Toggle navigation</span>
                <svg x-show="!open" viewBox="0 0 20 20" class="size-6" fill="currentColor" aria-hidden="true"><path d="M3 5h14v2H3V5Zm0 4h14v2H3V9Zm0 4h14v2H3v-2Z"/></svg>
                <svg x-show="open" x-cloak viewBox="0 0 20 20" class="size-6" fill="currentColor" aria-hidden="true"><path d="m5.3 3.9 10.8 10.8-1.4 1.4L3.9 5.3l1.4-1.4Z"/><path d="M16.1 5.3 5.3 16.1l-1.4-1.4L14.7 3.9l1.4 1.4Z"/></svg>
            </button>
        </div>
    </div>

    <nav id="mobile-navigation" x-show="open" x-cloak aria-label="Primary, mobile"
        class="border-t border-line bg-surface lg:hidden">
        <ul class="mx-auto max-w-7xl divide-y divide-line px-4 sm:px-6">
            @foreach ($sections as $section)
                <li class="py-1">
                    @if ($section['children'])
                        <button type="button" @click="section = section === '{{ $section['label'] }}' ? null : '{{ $section['label'] }}'"
                            :aria-expanded="section === '{{ $section['label'] }}' ? 'true' : 'false'"
                            aria-controls="mobile-menu-{{ Str::slug($section['label']) }}" data-active="{{ $section['active'] ? 'true' : 'false' }}"
                            class="flex w-full items-center justify-between px-1 py-3 text-left text-base font-semibold text-ink">
                            {{ $section['label'] }}
                            <svg viewBox="0 0 20 20" class="size-5 text-muted transition-transform"  :class="section === '{{ $section['label'] }}' && 'rotate-180'" fill="currentColor" aria-hidden="true"><path d="M5.5 7.5 10 12l4.5-4.5H5.5Z"/></svg>
                        </button>
                        <ul x-show="section === '{{ $section['label'] }}'" x-cloak
                            id="mobile-menu-{{ Str::slug($section['label']) }}" class="pb-2">
                            @foreach ($section['children'] as $child)
                                <li><a href="{{ $child['url'] }}" @if ($activeUrl === $child['url']) aria-current="{{ $currentUrl === rtrim($child['url'], '/') ? 'page' : 'true' }}" @endif class="block rounded-md px-3 py-2.5 text-sm text-muted hover:bg-primary-50 hover:text-primary-700">{{ $child['label'] }}</a></li>
                            @endforeach
                        </ul>
                    @else
                        <a href="{{ $section['url'] }}" class="block px-1 py-3 text-base font-semibold text-ink">{{ $section['label'] }}</a>
                    @endif
                </li>
            @endforeach
            <li class="py-3">
                <a href="{{ route('search') }}" @if (request()->routeIs('search')) aria-current="page" @endif class="block px-1 py-2 text-base font-semibold text-ink">Search</a>
            </li>
        </ul>
    </nav>
</header>
