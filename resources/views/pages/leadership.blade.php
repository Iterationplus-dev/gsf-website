@php
    // The director leads on a row of their own; everyone else follows in pairs.
    $director = $people->firstWhere('featured', true) ?? $people->first();
    $team = $people->reject(fn ($person) => $director && $person->is($director))->values();
    $logo = ($settings['org.logo'] ?? '') ?: asset('images/logo.jpg');
@endphp

<x-layouts.site
    title="Leadership"
    :description="$content?->excerpt ?: 'The management team responsible for delivering the foundation\'s programmes.'">

    {{-- The mark sits faintly in the right half only, well clear of the copy. --}}
    <section class="relative isolate overflow-hidden border-b border-line bg-surface">
        <div class="pointer-events-none absolute inset-y-0 right-0 -z-10 hidden w-1/2 items-center justify-center lg:flex" aria-hidden="true">
            <img src="{{ $logo }}" alt="" width="167" height="165" loading="lazy" decoding="async"
                class="size-80 object-contain opacity-[0.07]">
        </div>

        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <x-breadcrumbs :items="[['label' => 'Leadership']]" />

            <p class="eyebrow mt-6 text-accent-600">Our people</p>

            <h1 class="mt-3 max-w-3xl text-4xl leading-tight text-ink sm:text-5xl lg:max-w-lg">
                {{ $content?->title ?: 'Leadership' }}
            </h1>

            @if ($content?->excerpt)
                <p class="mt-5 max-w-2xl text-lg leading-relaxed text-muted lg:max-w-lg">{{ $content->excerpt }}</p>
            @endif
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($content?->body)
            <div class="prose-gsf max-w-3xl">{!! $content->body !!}</div>
        @endif

        @if ($people->isEmpty())
            <x-empty-state
                title="Team profiles are being prepared"
                description="Profiles are published once the organisation has confirmed each appointment."
            />
        @else
            @if ($director)
                {{-- Laid out wide rather than as a card, so the director's row does
                     not read as a single card with two empty slots beside it. --}}
                <article class="card group relative @if ($content?->body) mt-12 @endif overflow-hidden lg:grid lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <x-picture :media="$director->image" :alt="'Portrait of '.$director->title"
                            ratio="aspect-[4/5] lg:aspect-auto lg:h-full" :width="640"
                            crop="fill" gravity="face" aspect="4:5" eager
                            sizes="(min-width: 1024px) 460px, 100vw" />
                    </div>

                    <div class="flex flex-col justify-center p-8 lg:col-span-7 lg:p-10">
                        <p class="eyebrow text-accent-600">{{ $director->category }}</p>

                        <h2 class="mt-2 font-serif text-3xl font-semibold text-ink">
                            <a href="{{ route('leadership.show', $director->slug) }}" class="after:absolute after:inset-0 group-hover:text-primary-700">
                                {{ $director->title }}
                            </a>
                        </h2>

                        <p class="mt-4 leading-relaxed text-muted">{{ $director->excerpt }}</p>

                        <p class="mt-6 text-sm font-semibold text-primary-700">Read the full profile &rarr;</p>
                    </div>
                </article>
            @endif

            @foreach ($team->chunk(2) as $row)
                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    @foreach ($row as $person)
                        <x-card.person :person="$person" />
                    @endforeach
                </div>
            @endforeach
        @endif

        @if ($board->isNotEmpty())
            <section class="mt-20 border-t border-line pt-16" aria-labelledby="board-heading">
                <x-section-heading eyebrow="Governance" title="Board and trustees" id="board-heading" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($board as $member)
                        <x-card.person :person="$member" />
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-20 border-t border-line pt-16">
            <div class="card flex flex-wrap items-center justify-between gap-6 p-8">
                <div>
                    <h2 class="text-xl text-ink">How we are governed</h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-muted">
                        Our governance arrangements, board composition and accountability commitments.
                    </p>
                </div>
                <a href="{{ route('pages.show', 'governance') }}" class="btn-outline">Governance</a>
            </div>
        </section>
    </div>

    <x-donate-cta />

    <x-stats-band :metrics="$metrics" />
</x-layouts.site>
