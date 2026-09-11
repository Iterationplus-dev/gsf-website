@php
    /**
     * The homepage banner carried over from the previous website.
     *
     * Slides, their order and their lines of copy are reproduced from
     * backup/application/views/pages/view_home.php, which drove the Bootstrap
     * carousel on the old site.
     *
     * Photography is served from Cloudinary. `asset()` returns an absolute URL
     * unchanged, so a slide may equally point at a file under public/.
     */
    $slides = [
        [
            'image' => 'https://res.cloudinary.com/dt6xndtv/image/upload/v1789028013/slide-1.jpg',
            'alt' => 'Members of a co-operative seated around a table during a training session.',
            'lines' => [
                'Providing Business/Entrepreneurship Training',
                'Encouraging Advocacy by Co-operative Youths',
                'Acting as a network for exchange of information, ideas and experience',
            ],
        ],
        [
            'image' => 'https://res.cloudinary.com/dt6xndtv/image/upload/v1789028014/slide-2.jpg',
            'alt' => 'A young child looking out from behind a wooden post.',
            'lines' => [
                'Children Development Programmes',
                'Helping People with Disabilities',
                'Other Career Opportunities',
            ],
        ],
        [
            'image' => 'https://res.cloudinary.com/dt6xndtv/image/upload/v1789028013/slide-3.jpg',
            'alt' => 'Four people meeting around a table with printed reports in front of them.',
            'lines' => [
                'Providing Consultancy',
                'Advisory Services to Co-operatives.',
                'Supporting Retirees',
                'Support to Co-operatives',
            ],
        ],
        [
            'image' => 'https://res.cloudinary.com/dt6xndtv/image/upload/v1789049664/slider-6.png',
            'alt' => 'Financial district towers with the logos of institutions the foundation works alongside.',
            'lines' => [
                'Partners with Central Bank of Nigeria',
                'Partners with World Bank',
                'Partners with AU & UN',
            ],
        ],
    ];

    $count = count($slides);
@endphp

{{--
    Auto-advance stops on hover, on keyboard focus and whenever the visitor has
    asked for reduced motion, and there is an explicit pause control. A banner
    that moves under you while you are reading it is the usual carousel failure.
--}}
<section
    x-data="{
        active: 0,
        count: {{ $count }},
        paused: false,
        timer: null,
        reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        go(i) { this.active = (i + this.count) % this.count; this.restart(); },
        next() { this.go(this.active + 1); },
        prev() { this.go(this.active - 1); },
        restart() {
            clearInterval(this.timer);
            if (this.paused || this.reduced) { return; }
            this.timer = setInterval(() => { this.active = (this.active + 1) % this.count; }, 6000);
        },
        setPaused(value) { this.paused = value; this.restart(); },
    }"
    x-init="restart()"
    @mouseenter="setPaused(true)"
    @mouseleave="setPaused(false)"
    @focusin="setPaused(true)"
    @focusout="setPaused(false)"
    class="relative overflow-hidden bg-primary-900"
    role="region"
    aria-roledescription="carousel"
    aria-label="What Global Support Foundation does">

    <h1 class="sr-only">Global Support Foundation for Grassroot Entrepreneurship</h1>

    {{-- Sized to the copy rather than to the source photograph, which is a very
         wide 2.45:1 and would otherwise leave a large empty band under the
         bullets on a phone. --}}
    <div class="relative h-[28.665rem] sm:h-[30.87rem] lg:h-[33.075rem]">
        @foreach ($slides as $index => $slide)
            <div class="absolute inset-0"
                x-show="active === {{ $index }}"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @if ($index !== 0) x-cloak @endif
                role="group"
                aria-roledescription="slide"
                aria-label="{{ $index + 1 }} of {{ $count }}"
                :aria-hidden="active === {{ $index }} ? 'false' : 'true'">

                <img src="{{ asset($slide['image']) }}" alt="{{ $slide['alt'] }}"
                    width="1600" height="652"
                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                    @if ($index === 0) fetchpriority="high" @endif
                    decoding="async"
                    class="size-full object-cover">

                {{-- Light enough to keep the photograph readable, with the weight
                     kept on the reading side. The copy carries its own shadow so
                     that thinner text still holds up over a bright frame. --}}
                <div class="absolute inset-0 bg-primary-900/15"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-primary-900/85 via-primary-900/50 to-transparent"></div>

                <div class="absolute inset-0 flex items-center pb-16">
                    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="max-w-2xl">
                            <p class="eyebrow text-accent-300">Global Support Foundation</p>

                            <ul class="mt-4 list-disc space-y-2 ps-5 text-white marker:text-accent-300">
                                @foreach ($slide['lines'] as $line)
                                    {{-- Exactly 20% above the standard 0.75/0.875/1/1.125rem steps. --}}
                                    <li class="font-serif text-[0.9rem] leading-snug font-semibold [text-shadow:0_1px_3px_rgb(0_0_0/0.55)] sm:text-[1.05rem] md:text-[1.2rem] lg:text-[1.35rem]">
                                        {{ $line }}
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="{{ route('donate') }}" class="btn-primary">Donate now</a>
                                <a href="{{ route('pages.show', 'membership') }}" class="btn-ghost-light">Become a member</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Controls sit together below the copy rather than flanking it, where the
         arrows would overlap the caption on narrow viewports. --}}
    <div class="absolute inset-x-0 bottom-5 flex items-center justify-center gap-3">
        <button type="button" @click="prev()"
            class="grid size-9 place-items-center rounded-full bg-primary-900/60 text-white backdrop-blur transition hover:bg-primary-900/85 focus-visible:bg-primary-900/85">
            <span class="sr-only">Previous slide</span>
            <svg viewBox="0 0 20 20" class="size-5" fill="currentColor" aria-hidden="true"><path d="M12.5 4.5 7 10l5.5 5.5 1.4-1.4L9.8 10l4.1-4.1-1.4-1.4Z"/></svg>
        </button>

        <ul class="flex items-center gap-2.5">
            @foreach ($slides as $index => $slide)
                <li>
                    {{-- The dot stays small, but the button around it is a full
                         24px target so it can actually be hit with a thumb. --}}
                    <button type="button" @click="go({{ $index }})"
                        :aria-current="active === {{ $index }} ? 'true' : 'false'"
                        class="grid h-6 min-w-6 place-items-center">
                        <span aria-hidden="true"
                            :class="active === {{ $index }} ? 'w-8 bg-white' : 'w-2.5 bg-white/50 hover:bg-white/80'"
                            class="block h-2.5 rounded-full transition-all"></span>
                        <span class="sr-only">Go to slide {{ $index + 1 }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        <button type="button" @click="next()"
            class="grid size-9 place-items-center rounded-full bg-primary-900/60 text-white backdrop-blur transition hover:bg-primary-900/85 focus-visible:bg-primary-900/85">
            <span class="sr-only">Next slide</span>
            <svg viewBox="0 0 20 20" class="size-5" fill="currentColor" aria-hidden="true"><path d="M7.5 4.5 6.1 5.9 10.2 10l-4.1 4.1 1.4 1.4L13 10 7.5 4.5Z"/></svg>
        </button>

        <button type="button" @click="setPaused(! paused)" x-show="! reduced"
            class="ml-1 grid size-9 place-items-center rounded-full bg-primary-900/60 text-white backdrop-blur transition hover:bg-primary-900/85">
            <span class="sr-only" x-text="paused ? 'Resume the slideshow' : 'Pause the slideshow'">Pause the slideshow</span>
            <svg x-show="! paused" viewBox="0 0 20 20" class="size-4" fill="currentColor" aria-hidden="true"><path d="M6 4h3v12H6V4Zm5 0h3v12h-3V4Z"/></svg>
            <svg x-show="paused" x-cloak viewBox="0 0 20 20" class="size-4" fill="currentColor" aria-hidden="true"><path d="M6 4l10 6-10 6V4Z"/></svg>
        </button>
    </div>
</section>
