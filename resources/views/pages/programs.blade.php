<x-layouts.site
    title="Our Programs"
    description="The connected areas of work through which Global Support Foundation supports grassroots enterprise: entrepreneurship, co-operative development, human capacity, agriculture, industry and the professions.">

    <x-page-header
        eyebrow="What we do"
        title="Our Programs"
        lead="Each program addresses a specific reason a small enterprise fails to get off the ground — capital, structure, skills or terms of trade."
        :breadcrumbs="[['label' => 'Programs']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($programs->isEmpty())
            <x-empty-state
                title="Programs are being prepared"
                description="Program pages are published once the organisation has confirmed their content."
            />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($programs as $program)
                    <x-card.program :program="$program" />
                @endforeach
            </div>
        @endif
    </div>

    <x-donate-cta />

    <x-explore-our-work current="programs" />
</x-layouts.site>
