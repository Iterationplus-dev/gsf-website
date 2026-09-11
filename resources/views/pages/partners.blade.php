<x-layouts.site
    title="Partners"
    description="Organisations working with Global Support Foundation for Grassroot Entrepreneurship.">

    <x-page-header
        eyebrow="Working together"
        title="Partners"
        lead="Organisations we work with to deliver our programs."
        :breadcrumbs="[['label' => 'Partners']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($partners->isEmpty())
            {{-- Deliberately blank rather than populated with institutional logos.
                 The legacy website listed bodies GSF hoped to work with; presenting
                 those as partners would misrepresent the organisation to donors. --}}
            <x-empty-state
                title="Partnerships are being established"
                description="We publish an organisation here only where a partnership agreement is in place. If you represent an institution interested in working with us, we would like to hear from you."
            >
                <a href="{{ route('pages.show', 'partner-with-us') }}" class="btn-secondary">Partner with us</a>
            </x-empty-state>
        @else
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($partners as $partner)
                    <li class="card flex flex-col items-center p-6 text-center">
                        <x-picture :media="$partner->image" :alt="$partner->title" ratio="aspect-[3/2]"
                            class="w-32 [&_img]:object-contain" :width="256" sizes="128px" />
                        <h2 class="mt-4 text-base text-ink">{{ $partner->title }}</h2>
                        @if ($partner->excerpt)
                            <p class="mt-2 text-sm leading-relaxed text-muted">{{ $partner->excerpt }}</p>
                        @endif
                        @if ($partner->detail('website'))
                            <a href="{{ $partner->detail('website') }}" target="_blank" rel="noopener noreferrer"
                                class="mt-4 text-sm font-semibold text-primary-700 hover:underline">Visit website</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <x-donate-cta />

    <x-explore-our-work current="partners" />
</x-layouts.site>
