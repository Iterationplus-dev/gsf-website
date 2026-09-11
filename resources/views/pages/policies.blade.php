<x-layouts.site
    title="Policies"
    description="Approved organisational policies governing how Global Support Foundation conducts its work.">

    <x-page-header
        eyebrow="Accountability"
        title="Policies"
        lead="The governing documents that set out how we conduct our work and how we can be held to account."
        :breadcrumbs="[['label' => 'Policies']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($policies->isEmpty())
            <x-empty-state
                title="Policies are being adopted"
                description="A policy is a governing document: it is published here once it has been drafted, approved by the board and dated. Placeholders are held in our content system so that each document has a permanent home the moment it is approved."
            >
                <a href="{{ route('pages.show', 'contact-us') }}" class="btn-outline">Ask about our policy position</a>
            </x-empty-state>

            {{-- Naming the set is itself a form of accountability: it shows which
                 documents the foundation considers itself to need. --}}
            <section class="mt-12" aria-labelledby="policy-set">
                <h2 id="policy-set" class="text-xl text-ink">The policies being adopted</h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted">
                    Each of these has a permanent home in our content system and appears above the moment it is
                    approved and dated. None is published before then.
                </p>

                <ul role="list" class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        'Safeguarding Policy',
                        'Child Protection Policy',
                        'Code of Conduct',
                        'Anti-Fraud and Anti-Corruption Policy',
                        'Conflict of Interest Policy',
                        'Whistleblowing Policy',
                        'Complaints and Feedback Policy',
                        'Data Protection Policy',
                        'Procurement Policy',
                        'Gender and Inclusion Policy',
                    ] as $policyName)
                        <li class="flex items-center justify-between gap-4 rounded-md border border-line bg-surface px-4 py-3">
                            <span class="text-sm font-medium text-ink">{{ $policyName }}</span>
                            <span class="shrink-0 rounded-full bg-sunken px-2.5 py-1 text-xs font-semibold text-muted">In preparation</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @else
            <ul class="grid gap-4 sm:grid-cols-2">
                @foreach ($policies as $policy)
                    <li class="card flex flex-col p-6">
                        <h2 class="text-lg text-ink">
                            <a href="{{ route('policies.show', $policy->slug) }}" class="hover:text-primary-700 hover:underline">{{ $policy->title }}</a>
                        </h2>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">{{ $policy->excerpt }}</p>
                        @if ($policy->detail('approved_on'))
                            <p class="mt-4 text-xs text-muted">Approved {{ $policy->detail('approved_on') }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <x-donate-cta />

    <x-accountability-next current="policies" />
</x-layouts.site>
