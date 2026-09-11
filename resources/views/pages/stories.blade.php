<x-layouts.site
    title="Success Stories"
    description="Co-operative societies formed with the foundation's support, in their own words — how each one started, who its members are, and what trading together changed.">

    <x-page-header
        eyebrow="In their words"
        title="Success Stories"
        lead="What changes for a person when an enterprise starts to work."
        :breadcrumbs="[['label' => 'Stories']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($stories->isEmpty())
            <x-empty-state
                title="Stories are being gathered"
                description="Stories are published with the consent of the people they are about. We are collecting them, and they will appear here once that consent is in place."
            >
                <a href="{{ route('programs.index') }}" class="btn-outline">See our programs</a>
            </x-empty-state>
        @else
            <div class="grid gap-6">
                @foreach ($stories as $story)
                    <x-card.story :story="$story" />
                @endforeach
            </div>

            <div class="mt-12">{{ $stories->links() }}</div>
        @endif
    </div>

    <x-donate-cta />

    <x-explore-our-work current="stories" />
</x-layouts.site>
