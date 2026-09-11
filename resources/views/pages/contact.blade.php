@php
    $addressLines = collect([
        $settings['contact.address'] ?? null,
        trim(collect([$settings['contact.city'] ?? null, $settings['contact.state'] ?? null])->filter()->implode(', ')),
        $settings['contact.country'] ?? null,
    ])->filter();

    $phones = collect([
        'Nigeria' => $settings['contact.phone_primary'] ?? null,
        'Nigeria (alternative)' => $settings['contact.phone_secondary'] ?? null,
        'International' => $settings['contact.phone_international'] ?? null,
    ])->filter();

    // The map is built from the verified postal address, never from guessed
    // coordinates, so it can never point at the wrong building.
    $mapQuery = rawurlencode($addressLines->implode(', '));
@endphp

<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt">

    <x-page-header
        eyebrow="Get in touch"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[['label' => 'Contact Us']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <h2 class="text-2xl text-ink">Our office</h2>

                <address class="mt-5 space-y-6 not-italic">
                    @if ($addressLines->isNotEmpty())
                        <div>
                            <h3 class="eyebrow text-muted">Address</h3>
                            <p class="mt-2 leading-relaxed text-ink">{!! $addressLines->map(fn ($line) => e($line))->implode('<br>') !!}</p>
                        </div>
                    @endif

                    @if ($settings['contact.email'] ?? null)
                        <div>
                            <h3 class="eyebrow text-muted">Email</h3>
                            <p class="mt-2">
                                <a href="mailto:{{ $settings['contact.email'] }}" class="font-medium text-primary-700 hover:underline">{{ $settings['contact.email'] }}</a>
                            </p>
                        </div>
                    @endif

                    @if ($phones->isNotEmpty())
                        <div>
                            <h3 class="eyebrow text-muted">Telephone</h3>
                            <ul class="mt-2 space-y-1">
                                @foreach ($phones as $label => $number)
                                    <li>
                                        <a href="tel:{{ Str::remove(' ', $number) }}" class="font-medium text-primary-700 hover:underline">{{ $number }}</a>
                                        <span class="text-sm text-muted">({{ $label }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (($settings['contact.hours'] ?? '') !== '')
                        <div>
                            <h3 class="eyebrow text-muted">Office hours</h3>
                            <p class="mt-2 text-ink">{{ $settings['contact.hours'] }}</p>
                        </div>
                    @endif
                </address>

                @if ($content->body)
                    <div class="prose-gsf mt-8">{!! $content->body !!}</div>
                @endif
            </div>

            <div class="lg:col-span-7">
                <x-enquiry-form
                    type="contact"
                    heading="Send us a message"
                    description="We will respond using the details you provide. If your enquiry is about partnership or volunteering, those pages have dedicated forms."
                />
            </div>
        </div>

        @if ($mapQuery)
            <section class="mt-16" aria-labelledby="map-heading">
                <h2 id="map-heading" class="text-2xl text-ink">Find us</h2>

                @if (config('foundation.maps_enabled') && config('foundation.maps_key'))
                    {{-- Held behind functional consent: the embed is a Google
                         request that can set cookies. Declining leaves the
                         address and the link below, which is enough to find us. --}}
                    <x-consent-embed
                        class="mt-5 h-[420px]"
                        category="functional"
                        provider="Google Maps"
                        title="Map showing the Global Support Foundation office in Port Harcourt, Rivers State, Nigeria"
                        :src="'https://www.google.com/maps/embed/v1/place?key='.config('foundation.maps_key').'&q='.$mapQuery"
                        :href="'https://www.google.com/maps/search/?api=1&query='.$mapQuery"
                        referrerpolicy="no-referrer-when-downgrade"
                    />

                    <p class="mt-3 text-sm text-muted">{{ $addressLines->implode(', ') }}</p>
                @else
                    {{-- Without a configured API key, link out rather than embed:
                         a broken grey box is worse than an honest link. --}}
                    <div class="card mt-5 flex flex-wrap items-center justify-between gap-6 p-8">
                        <div>
                            <p class="font-medium text-ink">{{ $addressLines->implode(', ') }}</p>
                            <p class="mt-1 text-sm text-muted">Open the address in your preferred map application.</p>
                        </div>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}"
                            target="_blank" rel="noopener noreferrer" class="btn-outline">
                            Open in Google Maps
                            <span class="sr-only">(opens in a new tab)</span>
                        </a>
                    </div>
                @endif
            </section>
        @endif
    </div>
    <x-donate-cta />

    <x-involvement-details page="contact-us" />
</x-layouts.site>
