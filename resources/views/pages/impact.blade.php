<x-layouts.site
    title="Our Impact"
    description="How Global Support Foundation measures, evidences and reports the results of its work.">

    <x-page-header
        tone="dark"
        eyebrow="Results and reporting"
        title="Our Impact"
        lead="What we count, where the figures come from, and what we are still working to evidence."
        :breadcrumbs="[['label' => 'Impact']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">

        @if ($metrics->isNotEmpty())
            <section aria-labelledby="impact-metrics">
                <x-section-heading eyebrow="In numbers" title="Reported results" id="impact-metrics" />
                <x-impact-counters :metrics="$metrics" class="mt-10" />

                <p class="mt-6 max-w-3xl text-sm text-muted">
                    Every figure shown here is recorded against a source and a reporting period.
                    Figures without a documented source are not published.
                </p>
            </section>
        @else
            {{-- Being straight about the absence of verified figures is more
                 credible to an institutional donor than an invented number. --}}
            <x-empty-state
                title="Verified impact figures are being compiled"
                description="Global Support Foundation publishes a result only once the underlying records support it. Programme and project figures are being assembled for publication; in the meantime, our programs describe the work in detail."
            >
                <a href="{{ route('programs.index') }}" class="btn-secondary">See our programs</a>
                <a href="{{ route('pages.show', 'contact-us') }}" class="btn-outline">Request our reporting</a>
            </x-empty-state>
        @endif

        <section class="mt-20 border-t border-line pt-16" aria-labelledby="impact-method">
            <div class="grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <x-section-heading eyebrow="How we measure" title="What counts as evidence" id="impact-method" />
                </div>

                <div class="prose-gsf lg:col-span-7">
                    <p>
                        A training session delivered is an activity, not a result. What matters to the people
                        we work with — and to the institutions that fund the work — is whether an enterprise
                        exists afterwards, whether it trades, and whether its members are better off.
                    </p>
                    <h3>What we track</h3>
                    <ul>
                        <li><strong>Reach</strong> — how many people took part, and who they were.</li>
                        <li><strong>Formation</strong> — co-operatives registered and governing themselves.</li>
                        <li><strong>Continuity</strong> — enterprises still trading a year on.</li>
                        <li><strong>Participation</strong> — the share of women, young people and people living with disabilities among members.</li>
                        <li><strong>Geography</strong> — where the work happens, so that concentration and gaps are both visible.</li>
                    </ul>
                    <h3>Where the figures come from</h3>
                    <p>
                        Attendance registers, training reports, registration documents and follow-up visits.
                        Each published metric records its source and its reporting year, and the administration
                        panel refuses to publish a figure that has neither.
                    </p>
                </div>
            </div>
        </section>

        @if ($projects->isNotEmpty())
            <section class="mt-20 border-t border-line pt-16" aria-labelledby="impact-projects">
                <x-section-heading eyebrow="Evidence" title="Projects and results" id="impact-projects" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-card.project :project="$project" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($stories->isNotEmpty())
            <section class="mt-20 border-t border-line pt-16" aria-labelledby="impact-stories">
                <x-section-heading eyebrow="In their words" title="Stories of impact" id="impact-stories" />
                <div class="mt-10 grid gap-6">
                    @foreach ($stories as $story)
                        <x-card.story :story="$story" />
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-20 border-t border-line pt-16" aria-labelledby="impact-sdg">
            <x-section-heading
                eyebrow="Global goals"
                title="Where our work meets the SDGs"
                lead="Co-operative enterprise development speaks most directly to these goals. Individual projects are tagged only where the contribution is real."
                id="impact-sdg"
            />
            <x-sdg-badges :goals="[1, 2, 4, 5, 8, 10, 11, 17]" class="mt-8" />
        </section>
    </div>

    <x-donate-cta heading="Fund work that is measured, not assumed" />

    <x-explore-our-work current="impact" />
</x-layouts.site>
