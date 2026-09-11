@props(['project'])

@php
    $detail = $project->project;
@endphp

<article class="card group relative flex flex-col overflow-hidden transition-shadow hover:shadow-raised">
    <x-picture :media="$project->image" :alt="$project->title" ratio="aspect-[16/10]" :width="640"
        sizes="(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw" />

    <div class="flex flex-1 flex-col p-6">
        <div class="flex flex-wrap items-center gap-2">
            @if ($detail?->status)
                <x-status-pill :status="$detail->status" />
            @endif
            @if ($detail?->program)
                <span class="text-xs font-medium text-muted">{{ $detail->program->title }}</span>
            @endif
        </div>

        <h3 class="mt-3 text-lg text-ink">
            <a href="{{ route('projects.show', $project->slug) }}" class="after:absolute after:inset-0 group-hover:text-primary-700">
                {{ $project->title }}
            </a>
        </h3>

        <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">{{ Str::limit($project->excerpt, 140) }}</p>

        <dl class="mt-5 space-y-1 border-t border-line pt-4 text-xs text-muted">
            @if ($detail?->location)
                <div class="flex gap-2">
                    <dt class="font-semibold text-ink">Location</dt>
                    <dd>{{ $detail->location }}{{ $detail->state ? ', '.$detail->state : '' }}</dd>
                </div>
            @endif
            @if ($detail?->beneficiary_count)
                <div class="flex gap-2">
                    <dt class="font-semibold text-ink">Beneficiaries</dt>
                    <dd>{{ number_format($detail->beneficiary_count) }}</dd>
                </div>
            @endif
            @if ($detail?->period)
                <div class="flex gap-2">
                    <dt class="font-semibold text-ink">Period</dt>
                    <dd>{{ $detail->period }}</dd>
                </div>
            @endif
        </dl>
    </div>
</article>
