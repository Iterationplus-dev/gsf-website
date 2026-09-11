<x-layouts.site :description="$settings['seo.description'] ?? null">

    {{-- Hero slider ------------------------------------------------------ --}}
    <x-home-slider />

    {{-- What we do ------------------------------------------------------- --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <x-section-heading
                    eyebrow="The problem we work on"
                    title="Enterprise is possible. Doing it alone usually is not."
                />

                <x-picture :media="$sectionImage" alt="Members of a co-operative working together towards a shared enterprise."
                    ratio="aspect-[16/9]" :width="960" class="mt-8 rounded-card"
                    sizes="(min-width: 1024px) 460px, 100vw" />
            </div>

            <div class="prose-gsf lg:col-span-7">
                <p>
                    More than a third of young Africans between 15 and 40 are out of work, including school
                    leavers, diploma holders and graduates — some of them unemployed for years. The barrier is
                    rarely willingness. It is capital, land, equipment, skills and terms of trade that no
                    individual starting out can secure.
                </p>
                <p>
                    The co-operative model addresses those constraints together. Members pool resources, share
                    equipment none could buy alone, negotiate as a group, and govern themselves democratically.
                    It has been used successfully in many countries to create durable employment — and it remains
                    poorly understood across much of Africa as a development instrument.
                </p>
                <p>
                    Established in <strong>August 2015</strong> and based in Port Harcourt, Rivers State, GSF exists to
                    close that gap: training, business advice, consultancy and advocacy for the young people,
                    women, people living with disabilities and retirees who are furthest from economic
                    participation.
                </p>
                <p><a href="{{ route('pages.show', 'about-us') }}">Read more about our approach</a></p>
            </div>
        </div>
    </section>

    {{-- Target groups --------------------------------------------------------
         Carried over from the previous home page. Images are delivered through
         the media pipeline, so each card gets AVIF or WebP at the width it
         actually occupies rather than the full-size original. --}}
    <section class="border-t border-line bg-surface" aria-labelledby="home-target-groups">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <x-section-heading
                eyebrow="Who we work with"
                title="Target Groups"
                lead="The people for whom a co-operative changes what is economically possible."
                id="home-target-groups"
            />

            <ul role="list" class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach ([
                    [
                        'title' => 'Unemployed Youths, Male and Female',
                        'image' => 'youths',
                        'body' => 'Above a third of African youths between the ages of 15 and 40 are unemployed. In this category are JSS and SSS leavers, ND and HND holders, and B.Sc. and Masters degree holders. Some have been unemployed for fifteen years.',
                    ],
                    [
                        'title' => 'People living with Disabilities',
                        'image' => 'disabilities',
                        'body' => 'A study in the UK found poverty among disabled people at 23.1 per cent against 17.9 per cent for non-disabled people — and once the extra costs of disability were counted, the rate rose to 47.4 per cent.',
                    ],
                    [
                        'title' => 'Retirees & Senior Citizens',
                        'image' => 'retirees',
                        'body' => 'Ageism — the systematic stereotyping of people because they are old — is a real phenomenon in Nigerian society. It points to a serious situation: people can ill afford to retire in Africa from any form of government service.',
                    ],
                    [
                        'title' => 'Established Businesses & Startups',
                        'image' => 'businesses',
                        'body' => 'Established businesses run by older owners often face challenges that lead to failure or poor performance. Many do not see the need for consultancy, cannot afford it, or are unaware that affordable support exists.',
                    ],
                ] as $group)
                    <li class="card flex flex-col overflow-hidden">
                        <x-picture :media="$targetGroupImages[$group['image']] ?? null" :alt="$group['title']"
                            ratio="aspect-[3/2]" :width="640"
                            sizes="(min-width: 640px) 50vw, 100vw" />

                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="text-lg leading-snug text-ink">{{ $group['title'] }}</h3>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ $group['body'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Mission band ----------------------------------------------------------
         A full-bleed photograph carrying the argument between who the work is
         for and what the work actually is. The overlay is heavy because the copy
         over it is white and the photograph is bright. --}}
    <section class="relative isolate overflow-hidden bg-primary-900 text-white" aria-labelledby="home-mission">
        @if ($missionImage?->approved)
            @php $missionService = app(App\Services\MediaService::class); @endphp
            <div class="absolute inset-0 -z-10" aria-hidden="true">
                <img
                    src="{{ $missionService->url($missionImage, ['width' => 1600]) }}"
                    srcset="{{ $missionService->srcset($missionImage) }}"
                    sizes="100vw"
                    alt=""
                    loading="lazy"
                    decoding="async"
                    class="size-full object-cover">
                <div class="absolute inset-0 bg-primary-900/85"></div>
            </div>
        @endif

        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-3xl">
                <p class="eyebrow text-accent-300">Why it matters</p>

                <h2 id="home-mission" class="mt-3 text-3xl leading-tight text-white sm:text-4xl lg:text-5xl">
                    Enterprise has to reach the places capital does not
                </h2>

                <p class="mt-6 text-lg leading-relaxed text-primary-100">
                    The foundation works at the grassroots — with communities a long way from the banks,
                    the markets and the advisory services that make a business possible. A co-operative is
                    how people in those places assemble what none of them could reach alone: equipment,
                    working capital, buyers, and a legal structure that outlasts any one member.
                </p>

                <p class="mt-4 text-lg leading-relaxed text-primary-100">
                    That is the whole argument. Not charity, and not a grant that runs out — a structure
                    that makes people's own work pay.
                </p>

                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="{{ route('programs.index') }}" class="btn-primary">Explore our programs</a>
                    <a href="{{ route('pages.show', 'membership') }}" class="btn-ghost-light">Affiliate your co-operative</a>
                    <a href="{{ route('pages.show', 'partner-with-us') }}" class="btn-ghost-light">Partner with us</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Impact metrics ---------------------------------------------------- --}}
    @if ($metrics->isNotEmpty())
        <section class="border-y border-line bg-sunken" aria-labelledby="home-impact">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <h2 id="home-impact" class="sr-only">Our impact in numbers</h2>
                <x-impact-counters :metrics="$metrics" />
                <p class="mt-6 text-sm text-muted">
                    <a href="{{ route('impact') }}" class="font-semibold text-primary-700 hover:underline">
                        How we measure and report impact &rarr;
                    </a>
                </p>
            </div>
        </section>
    @endif

    {{-- Programs ---------------------------------------------------------- --}}
    @if ($programs->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" aria-labelledby="home-programs">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-section-heading
                    eyebrow="What we do"
                    title="Our programs"
                    lead="Connected areas of work, each one addressing a specific reason an enterprise fails to get off the ground."
                    id="home-programs"
                />
                <a href="{{ route('programs.index') }}" class="btn-outline">All programs</a>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($programs as $program)
                    <x-card.program :program="$program" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Featured projects -------------------------------------------------- --}}
    @if ($projects->isNotEmpty())
        <section class="border-y border-line bg-sunken" aria-labelledby="home-projects">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <x-section-heading eyebrow="On the ground" title="Featured projects" id="home-projects" />
                    <a href="{{ route('projects.index') }}" class="btn-outline">All projects</a>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-card.project :project="$project" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Stories ------------------------------------------------------------ --}}
    @if ($stories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" aria-labelledby="home-stories">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-section-heading eyebrow="Stories of impact" title="The people behind the work" id="home-stories" />
                <a href="{{ route('stories.index') }}" class="btn-outline">All stories</a>
            </div>

            <div class="mt-12 grid gap-6">
                @foreach ($stories as $story)
                    <x-card.story :story="$story" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Partners ------------------------------------------------------------ --}}
    @if ($partners->isNotEmpty())
        <section class="border-t border-line" aria-labelledby="home-partners">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <h2 id="home-partners" class="eyebrow text-center text-muted">Working with</h2>
                <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-12 gap-y-8">
                    @foreach ($partners as $partner)
                        <li>
                            <x-picture :media="$partner->image" :alt="$partner->title" ratio="aspect-[3/2]"
                                class="w-32 [&_img]:object-contain" :width="256" sizes="128px" />
                            <span class="sr-only">{{ $partner->title }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Donate -------------------------------------------------------------- --}}
    <x-donate-cta />

    {{-- Featured videos --------------------------------------------------------
         Carried over from the previous home page. Each player is held behind
         marketing consent, because YouTube sets advertising cookies as soon as
         one loads; until then the placeholder offers to allow it or to watch on
         YouTube directly. Each frame carries the video's own title, because an
         iframe is announced by that attribute alone. --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" aria-labelledby="home-videos">
        <x-section-heading eyebrow="Watch" title="Featured Videos" id="home-videos" />

        <ul role="list" class="mt-12 grid gap-6 sm:grid-cols-2">
            @foreach ([
                ['id' => 'BFqwPHTwnO0', 'title' => 'Minister of State on agriculture'],
                ['id' => 'FerTkgKOyHI', 'title' => 'Cooperative Housing'],
                ['id' => 'FGR_Ylj_G_Y', 'title' => 'Global Support Foundation'],
                ['id' => 'G65z8p1YNhw', 'title' => 'Giving $100 to Homeless People — Give Back Films'],
            ] as $video)
                <li>
                    <x-consent-embed
                        class="aspect-video"
                        category="marketing"
                        provider="YouTube"
                        :title="$video['title']"
                        :src="'https://www.youtube.com/embed/'.$video['id']"
                        :href="'https://www.youtube.com/watch?v='.$video['id']"
                        allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    />
                    <p class="mt-3 text-sm font-medium text-ink">{{ $video['title'] }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- News ---------------------------------------------------------------- --}}
    @if ($articles->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" aria-labelledby="home-news">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-section-heading eyebrow="Latest" title="News and updates" id="home-news" />
                <a href="{{ route('news.index') }}" class="btn-outline">All news</a>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-card.article :article="$article" />
                @endforeach
            </div>
        </section>
    @endif

</x-layouts.site>
