@php
    $service = app(App\Services\MediaService::class);
    $document = App\Models\Media::approved()->find($content->detail('document_id'));
@endphp

<x-detail-page
    :content="$content"
    eyebrow="Publication"
    :breadcrumbs="[
        ['label' => 'Resources', 'url' => route('resources.index')],
        ['label' => $content->title],
    ]">

    <x-slot:meta>
        <h2 class="text-lg text-ink">Publication details</h2>
        <dl class="mt-4 space-y-3 text-sm">
            @foreach (['Category' => $content->category, 'Year' => $content->published_at?->format('Y')] as $label => $value)
                @if (filled($value))
                    <div>
                        <dt class="font-semibold text-ink">{{ $label }}</dt>
                        <dd class="mt-0.5 text-muted">{{ $value }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>

        @if ($document)
            <a href="{{ $service->url($document) }}" class="btn-secondary mt-5 w-full" download>
                Download ({{ Str::upper(Str::afterLast($document->mime, '/')) }}, {{ round($document->size / 1024) }} KB)
            </a>
        @endif
    </x-slot:meta>
</x-detail-page>
