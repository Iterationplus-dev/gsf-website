<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt">

    <x-page-header
        eyebrow="Get involved"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[['label' => 'Membership']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-5">
                <div class="prose-gsf">{!! $content->body !!}</div>

                <section class="mt-12">
                    <h2 class="text-2xl text-ink">Before you apply</h2>
                    <ul class="mt-5 space-y-3">
                        @foreach ([
                            'Your society has at least '.App\Models\MembershipApplication::MINIMUM_MEMBERS.' members.',
                            'You can supply a written list of those members on request.',
                            'You have a contact person authorised to speak for the society.',
                            'You can describe the sector the society trades in.',
                        ] as $requirement)
                            <li class="flex items-start gap-2.5 rounded-md border border-line bg-surface px-4 py-3 text-sm text-ink">
                                <svg viewBox="0 0 20 20" class="mt-0.5 size-4 shrink-0 text-primary-600" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.7-9.3-4.2 4.2a1 1 0 0 1-1.4 0l-2-2 1.4-1.4L8.8 10.8l3.5-3.5 1.4 1.4Z" clip-rule="evenodd"/></svg>
                                {{ $requirement }}
                            </li>
                        @endforeach
                    </ul>
                </section>

                <p class="mt-8 text-sm leading-relaxed text-muted">
                    Not a co-operative society?
                    <a href="{{ route('pages.show', 'volunteer') }}" class="font-medium text-primary-700 underline">Volunteer with us</a>
                    or
                    <a href="{{ route('pages.show', 'partner-with-us') }}" class="font-medium text-primary-700 underline">partner with us</a>
                    instead.
                </p>
            </article>

            <div class="lg:col-span-7">
                <x-membership-form
                    description="Applications are reviewed by our membership team. We will contact you using the details below, and will ask for your member list at that point." />
            </div>
        </div>
    </div>

    <x-donate-cta />

    <x-involvement-details page="membership" />
</x-layouts.site>
