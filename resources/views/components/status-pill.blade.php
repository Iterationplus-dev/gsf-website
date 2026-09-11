@props(['status'])

@php
    $styles = [
        'planned' => 'bg-info-600/10 text-info-600',
        'active' => 'bg-primary-600/10 text-primary-700',
        'completed' => 'bg-success-600/10 text-success-600',
        'suspended' => 'bg-warning-600/10 text-warning-600',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', $styles[$status] ?? 'bg-sunken text-muted']) }}>
    {{ App\Models\Project::STATUSES[$status] ?? Str::headline($status) }}
</span>
