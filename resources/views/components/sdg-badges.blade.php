@props(['goals' => []])

@php
    /**
     * The subset of the UN Sustainable Development Goals that grassroots
     * co-operative development can credibly speak to. A project is only ever
     * tagged with goals an administrator has selected, so nothing here asserts
     * alignment on the organisation's behalf.
     */
    $names = [
        1 => 'No Poverty',
        2 => 'Zero Hunger',
        4 => 'Quality Education',
        5 => 'Gender Equality',
        8 => 'Decent Work and Economic Growth',
        9 => 'Industry, Innovation and Infrastructure',
        10 => 'Reduced Inequalities',
        11 => 'Sustainable Cities and Communities',
        12 => 'Responsible Consumption and Production',
        17 => 'Partnerships for the Goals',
    ];

    $selected = collect($goals)->map(fn ($goal): int => (int) $goal)->filter(fn (int $goal): bool => isset($names[$goal]));
@endphp

@if ($selected->isNotEmpty())
    <ul {{ $attributes->class(['flex flex-wrap gap-2']) }}>
        @foreach ($selected as $goal)
            <li class="inline-flex items-center gap-2 rounded-md border border-line bg-surface px-3 py-1.5 text-xs">
                <span class="grid size-6 shrink-0 place-items-center rounded bg-primary-700 font-semibold text-white tabular-nums">{{ $goal }}</span>
                <span class="font-medium text-ink">{{ $names[$goal] }}</span>
            </li>
        @endforeach
    </ul>
@endif
