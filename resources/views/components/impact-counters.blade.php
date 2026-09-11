@props(['metrics', 'tone' => 'light'])

@php
    $dark = $tone === 'dark';
@endphp

@if ($metrics->isNotEmpty())
    <dl {{ $attributes->class(['grid gap-6 sm:grid-cols-2 lg:grid-cols-4']) }}>
        @foreach ($metrics as $metric)
            <div @class([
                'rounded-card p-6',
                'border border-line bg-surface' => ! $dark,
                'bg-white/5 ring-1 ring-white/10' => $dark,
            ])>
                {{-- The figure is rendered server-side and is correct before any
                     script runs; the counter script only animates towards it. --}}
                <dd @class(['font-serif text-4xl font-semibold tabular-nums', 'text-primary-700' => ! $dark, 'text-white' => $dark])
                    data-counter>{{ $metric->display_value }}</dd>

                <dt class="mt-2">
                    <span @class(['block text-sm font-semibold', 'text-ink' => ! $dark, 'text-primary-100' => $dark])>
                        {{ $metric->title }}
                        @if ($metric->unit)
                            <span class="font-normal">({{ $metric->unit }})</span>
                        @endif
                    </span>

                    @if ($metric->year || $metric->geography)
                        <span @class(['mt-1 block text-xs', 'text-muted' => ! $dark, 'text-primary-300' => $dark])>
                            {{ collect([$metric->geography, $metric->year])->filter()->implode(' · ') }}
                        </span>
                    @endif
                </dt>
            </div>
        @endforeach
    </dl>
@endif
