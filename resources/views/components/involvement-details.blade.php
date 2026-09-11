@props(['page'])

@php
    $details = [
        'get-involved' => [
            'title' => 'Find your way to contribute',
            'lead' => 'Start with the time, skills or resources you can offer, and choose the route that fits.',
            'items' => [
                ['Share your skills', 'Practical experience in training, business planning, finance or co-operative development can support the enterprises we work with. Tell us your skills and availability.', 'Explore volunteering', route('pages.show', 'volunteer')],
                ['Bring your organisation', 'Development agencies, businesses, universities and public institutions can discuss how their expertise and resources connect with our work.', 'Discuss a partnership', route('pages.show', 'partner-with-us')],
                ['Connect your co-operative', 'Co-operative societies can apply for affiliation to participate in training, seek advisory support and connect with other enterprises.', 'Explore membership', route('pages.show', 'membership')],
            ],
        ],
        'partner-with-us' => [
            'title' => 'Prepare for a partnership conversation',
            'lead' => 'A clear starting point helps us discuss a practical collaboration with your organisation.',
            'items' => [
                ['Describe the opportunity', 'Tell us about your organisation, the communities or sectors you work with, and the outcomes you hope to support.', 'Explore our programs', route('programs.index')],
                ['Outline your contribution', 'Explain the funding, technical expertise, training or connections you could offer. Include your proposed location and timeframe in the enquiry.', 'See our projects', route('projects.index')],
                ['Review how we work', 'Read about our governance and reporting before discussing responsibilities, scope and the information you will need from a partnership.', 'Read about governance', route('pages.show', 'governance')],
            ],
        ],
        'volunteer' => [
            'title' => 'Make your experience count',
            'lead' => 'Help us understand where your skills fit and what a manageable commitment looks like for you.',
            'items' => [
                ['Tell us what you do well', 'Include relevant experience and an example of the work you could support, such as bookkeeping, training delivery, digital skills or business planning.', 'Explore our programs', route('programs.index')],
                ['Be clear about availability', 'Use the application above to share your location, the time you can offer and any practical limits. These details help shape a discussion about a suitable contribution.', 'Ask a question', route('pages.show', 'contact-us')],
                ['Understand community responsibilities', 'Volunteers working with young people or in community settings are subject to the foundation’s safeguarding requirements. Review our published policies and raise any questions before starting.', 'Read our policies', route('policies.index')],
            ],
        ],
        'membership' => [
            'title' => 'Your society’s next steps',
            'lead' => 'Keep your society’s information ready and use the review process to explain the support you need.',
            'items' => [
                ['Prepare your member list', 'The membership team will ask for a written list of members when reviewing your application. Make sure your named contact can provide it and speak for the society.', 'Ask about membership', route('pages.show', 'contact-us')],
                ['Identify your priorities', 'Explain whether your society needs help with governance, bookkeeping, business planning or co-operative skills so the discussion starts with your practical needs.', 'Explore our training areas', route('programs.index')],
                ['Stay in contact', 'The membership team reviews applications and contacts the person named on the form. Keep those contact details accurate and let us know if they change.', 'Contact the foundation', route('pages.show', 'contact-us')],
            ],
        ],
        'contact-us' => [
            'title' => 'Help us understand your enquiry',
            'lead' => 'Choose the relevant form and include enough context for the foundation to respond usefully.',
            'items' => [
                ['For organisations', 'Use the partnership form for institutional enquiries. Include your organisation’s name, the purpose of the proposed collaboration and what you hope to achieve.', 'Send a partnership enquiry', route('pages.show', 'partner-with-us')],
                ['For volunteers and societies', 'The volunteer and membership pages collect the details needed for those applications. Choose the relevant page from the Get Involved menu to get started.', 'See ways to get involved', route('pages.show', 'get-involved')],
                ['For general questions', 'Use the message form above, describe what you need and provide a working email address. If following up on an earlier enquiry, include its subject and when you sent it.', 'Read our privacy notice', route('pages.show', 'privacy-policy')],
            ],
        ],
    ][$page];
@endphp

<section class="border-b border-line bg-sunken" aria-labelledby="involvement-details-heading">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <x-section-heading
            eyebrow="Taking the next step"
            :title="$details['title']"
            :lead="$details['lead']"
            id="involvement-details-heading"
        />

        <div class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach ($details['items'] as [$heading, $description, $label, $url])
                <article class="card flex h-full flex-col p-6 sm:p-8">
                    <h3 class="text-xl text-ink">{{ $heading }}</h3>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ $description }}</p>
                    <a href="{{ $url }}" class="mt-6 font-semibold text-primary-700 underline underline-offset-4">{{ $label }}</a>
                </article>
            @endforeach
        </div>
    </div>
</section>
