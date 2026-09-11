<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt">

    <x-page-header
        eyebrow="Get involved"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[['label' => 'Volunteer']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-7">
                <div class="prose-gsf">{!! $content->body !!}</div>

                <section class="mt-12">
                    <h2 class="text-2xl text-ink">Skills we need most</h2>
                    <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ([
                            'Co-operative governance',
                            'Business planning',
                            'Bookkeeping and finance',
                            'Training delivery',
                            'Agronomy and agribusiness',
                            'Digital and ICT skills',
                            'Monitoring and evaluation',
                            'Legal and registration support',
                        ] as $skill)
                            <li class="flex items-center gap-2.5 rounded-md border border-line bg-surface px-4 py-3 text-sm text-ink">
                                <svg viewBox="0 0 20 20" class="size-4 shrink-0 text-primary-600" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.7-9.3-4.2 4.2a1 1 0 0 1-1.4 0l-2-2 1.4-1.4L8.8 10.8l3.5-3.5 1.4 1.4Z" clip-rule="evenodd"/></svg>
                                {{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            </article>

            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-24">
                    <x-enquiry-form
                        type="volunteer"
                        heading="Volunteer application"
                        description="Tell us what you can offer and how much time you have available."
                        submit="Submit application"
                        organisation
                    />
                </div>
            </div>
        </div>
    </div>

    <x-donate-cta />

    <x-involvement-details page="volunteer" />
</x-layouts.site>
