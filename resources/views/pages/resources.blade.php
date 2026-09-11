<x-layouts.site
    title="Publications & Resources"
    description="Reports, research and publications from Global Support Foundation for Grassroot Entrepreneurship.">

    <x-page-header
        eyebrow="Accountability"
        title="Publications & Resources"
        lead="Annual reports, impact reporting, research and brochures."
        :breadcrumbs="[['label' => 'Resources']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($publications->isEmpty())
            <x-empty-state
                title="Publications are being prepared"
                description="Annual reports, impact reporting and other publications are published here as the organization produces and approves them."
            >
                <a href="{{ route('pages.show', 'contact-us') }}" class="btn-outline">Request our reporting</a>
            </x-empty-state>

            <section class="mt-12" aria-labelledby="resources-available">
                <h2 id="resources-available" class="text-xl text-ink">Available now</h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted">
                    While formal reporting is in preparation, the foundation publishes practical written guidance
                    on forming, registering and running a co-operative society.
                </p>

                <div class="card mt-6 flex flex-wrap items-center justify-between gap-6 p-6 sm:p-8">
                    <div>
                        <h3 class="text-lg text-ink">Guidance for co-operatives</h3>
                        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted">
                            Registration, bookkeeping, business planning, shared equipment and governance —
                            written for societies at the beginning.
                        </p>
                    </div>
                    <a href="{{ route('news.index') }}" class="btn-outline">Read the guidance</a>
                </div>
            </section>
        @else
            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($publications as $publication)
                    <x-card.publication :publication="$publication" />
                @endforeach
            </div>

            <div class="mt-12">{{ $publications->links() }}</div>
        @endif
    </div>

    <x-donate-cta />

    <x-accountability-next current="resources" />
</x-layouts.site>
