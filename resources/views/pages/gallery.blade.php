<x-layouts.site
    title="Photo Gallery"
    description="Photographs from Global Support Foundation's training, co-operative development and community work.">

    <x-page-header
        eyebrow="Our work in pictures"
        title="Photo Gallery"
        lead="Training sessions, co-operative launches and community outreach, photographed as they happened."
        :breadcrumbs="[['label' => 'Photo Gallery']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($images->isEmpty())
            <x-empty-state
                title="No photographs published yet"
                description="Images appear here once they have been reviewed and approved. Photographs of identifiable people are only published where consent is on record."
            >
                <a href="{{ route('projects.index') }}" class="btn-outline">See our projects</a>
            </x-empty-state>
        @else
            <ul role="list" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($images as $image)
                    <li>
                        <figure class="zoomable card overflow-hidden">
                            <x-picture :media="$image" :alt="$image->alt ?? $image->title"
                                ratio="aspect-square" :width="640"
                                sizes="(min-width: 1024px) 25vw, (min-width: 640px) 33vw, 50vw" />

                            @if ($image->caption || $image->credit)
                                <figcaption class="px-4 py-3 text-xs leading-relaxed text-muted">
                                    {{ $image->caption }}
                                    @if ($image->credit)
                                        <span class="block text-muted/80">Photo: {{ $image->credit }}</span>
                                    @endif
                                </figcaption>
                            @endif
                        </figure>
                    </li>
                @endforeach
            </ul>

            <div class="mt-12">{{ $images->links() }}</div>
        @endif
    </div>

    <x-donate-cta />

    <x-explore-our-work current="gallery" />
</x-layouts.site>
