@props([
    'eyebrow' => null,
    'title',
    'lead' => null,
    'breadcrumbs' => [],
    'tone' => 'light',
])

@php
    $dark = $tone === 'dark';
@endphp

<section {{ $attributes->class([
    'border-b',
    'border-line bg-surface' => ! $dark,
    'border-primary-800 bg-primary-800 text-white' => $dark,
]) }}>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        @if ($breadcrumbs)
            <x-breadcrumbs :items="$breadcrumbs" :dark="$dark" />
        @endif

        @if ($eyebrow)
            <p @class(['eyebrow mt-6', 'text-accent-600' => ! $dark, 'text-primary-200' => $dark])>{{ $eyebrow }}</p>
        @endif

        <h1 @class(['mt-3 max-w-4xl text-4xl leading-tight sm:text-5xl', 'text-ink' => ! $dark, 'text-white' => $dark])>{{ $title }}</h1>

        @if ($lead)
            <p @class(['mt-5 max-w-3xl text-lg leading-relaxed', 'text-muted' => ! $dark, 'text-primary-100' => $dark])>{{ $lead }}</p>
        @endif

        @if (isset($actions))
            <div class="mt-8 flex flex-wrap gap-3">{{ $actions }}</div>
        @endif
    </div>
</section>
