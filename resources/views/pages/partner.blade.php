<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt">

    <x-page-header
        tone="dark"
        eyebrow="For funders and institutions"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[['label' => 'Partner With Us']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="prose-gsf lg:col-span-7">
                {!! $content->body !!}
            </article>

            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-24">
                    <x-enquiry-form
                        type="partnership"
                        heading="Partnership enquiry"
                        description="Tell us about your organisation and what you are looking to achieve. Enquiries reach the foundation's administration directly."
                        submit="Send partnership enquiry"
                        organisation
                    />
                </div>
            </div>
        </div>

        <section class="mt-20 border-t border-line pt-16" aria-labelledby="partner-next">
            <x-section-heading
                eyebrow="What happens next"
                title="How we respond to an enquiry"
                id="partner-next"
            />

            <ol class="mt-10 grid gap-6 sm:grid-cols-3">
                @foreach ([
                    'We read it properly' => 'Your enquiry reaches the foundation\'s administration, not a shared inbox nobody owns.',
                    'We send you the position' => 'A description of current work, our governance arrangements and what we can and cannot yet evidence.',
                    'We agree scope together' => 'What the partnership funds, what we report, and when — written down before anything starts.',
                ] as $heading => $description)
                    <li class="card p-6">
                        <span class="grid size-9 place-items-center rounded-full bg-primary-700 font-semibold text-white tabular-nums">{{ $loop->iteration }}</span>
                        <h3 class="mt-4 text-lg text-ink">{{ $heading }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $description }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
    <x-donate-cta />

    <x-involvement-details page="partner-with-us" />
</x-layouts.site>
