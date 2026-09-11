@props(['items' => [], 'dark' => false])

@php
    // Structured data mirrors the visible trail so search engines show the same
    // path a visitor sees.
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($items)->prepend(['label' => 'Home', 'url' => route('home')])->values()->map(fn (array $item, int $index): array => array_filter([
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['label'],
            'item' => $item['url'] ?? null,
        ]))->all(),
    ];
@endphp

<nav aria-label="Breadcrumb">
    <ol @class(['flex flex-wrap items-center gap-x-2 gap-y-1 text-sm', 'text-muted' => ! $dark, 'text-primary-200' => $dark])>
        <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
        @foreach ($items as $item)
            <li aria-hidden="true">/</li>
            <li>
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="hover:underline">{{ $item['label'] }}</a>
                @else
                    <span @class(['font-medium', 'text-ink' => ! $dark, 'text-white' => $dark]) aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
