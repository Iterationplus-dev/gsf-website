@php
    $service = app(App\Services\MediaService::class);
    $media = $content->image?->approved ? $content->image : null;
    $verification = $content->detail('verification_url');
@endphp

<x-detail-page
    :content="$content"
    eyebrow="Award"
    :breadcrumbs="[
        ['label' => 'Awards', 'url' => route('awards.index')],
        ['label' => $content->title],
    ]">

    <x-slot:meta>
        <h2 class="text-lg text-ink">Award details</h2>
        <dl class="mt-4 space-y-3 text-sm">
            @foreach ([
                'Awarding institution' => $content->detail('awarding_institution'),
                'Year' => $content->detail('year'),
                'Category' => $content->category,
            ] as $label => $value)
                @if (filled($value))
                    <div>
                        <dt class="font-semibold text-ink">{{ $label }}</dt>
                        <dd class="mt-0.5 text-muted">{{ $value }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>

        @if ($verification)
            <a href="{{ $verification }}" target="_blank" rel="noopener noreferrer" class="btn-outline mt-5 w-full">
                Verify this award
                <span class="sr-only">(opens in a new tab)</span>
            </a>
        @endif
    </x-slot:meta>

    @if ($media)
        <section class="mt-12" aria-labelledby="certificate-heading">
            <h2 id="certificate-heading" class="text-2xl text-ink">Certificate</h2>
            <p class="mt-2 text-sm text-muted">Select the certificate to view it at full resolution.</p>

            <button type="button" class="mt-5 block w-full cursor-zoom-in overflow-hidden rounded-card border border-line bg-sunken"
                data-lightbox="award-{{ $content->id }}"
                data-lightbox-full="{{ $service->url($media, ['quality' => 'auto:best', 'width' => 2000]) }}"
                data-lightbox-alt="{{ $media->alt ?? $content->title }}"
                data-lightbox-caption="{{ $content->title }}">
                {{-- Certificates are delivered at the highest quality setting:
                     compressing a scan until the citation is unreadable defeats
                     the purpose of publishing it. --}}
                <x-picture :media="$media" :alt="$content->title" ratio="aspect-[4/3]" :width="1280"
                    quality="auto:best" class="[&_img]:object-contain" sizes="(min-width: 1024px) 760px, 100vw" />
                <span class="sr-only">Enlarge certificate</span>
            </button>
        </section>
    @endif
</x-detail-page>
