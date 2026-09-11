@php
    $service = app(App\Services\MediaService::class);
    $project = $content->project;
    $ogImage = $content->image?->approved ? $service->url($content->image, ['width' => 1200]) : null;

    $gallery = App\Models\Media::approved()->whereIn('id', $project?->gallery ?? [])->get();
    $documents = App\Models\Media::approved()->whereIn('id', $project?->documents ?? [])->get();

    /** Multi-line text fields are entered one item per line by administrators. */
    $lines = fn (?string $value) => collect(preg_split('/\r\n|\r|\n/', (string) $value))
        ->map(fn (string $line): string => trim($line))
        ->filter()
        ->values();
@endphp

<x-layouts.site
    :title="$content->seo_title ?: $content->title"
    :description="$content->seo_description ?: $content->excerpt"
    :image="$ogImage">

    <x-page-header
        eyebrow="Project"
        :title="$content->title"
        :lead="$content->excerpt"
        :breadcrumbs="[
            ['label' => 'Projects', 'url' => route('projects.index')],
            ['label' => $content->title],
        ]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <article class="lg:col-span-8">
                @if ($content->image)
                    <x-picture :media="$content->image" :alt="$content->title" ratio="aspect-[16/9]"
                        class="mb-10 rounded-card" :width="1280" eager sizes="(min-width: 1024px) 760px, 100vw" />
                @endif

                <div class="prose-gsf">{!! $content->body !!}</div>

                @foreach ([
                    'Objectives' => $project?->objectives,
                    'Activities' => $project?->activities,
                    'Outcomes' => $project?->outcomes,
                    'Key results' => $project?->key_results,
                ] as $heading => $field)
                    @if (filled($field))
                        <section class="mt-12">
                            <h2 class="text-2xl text-ink">{{ $heading }}</h2>
                            <ul class="prose-gsf mt-4 list-disc space-y-2 pl-6">
                                @foreach ($lines($field) as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                @endforeach

                @if ($project?->sdgs)
                    <section class="mt-12">
                        <h2 class="text-2xl text-ink">Sustainable Development Goals</h2>
                        <p class="mt-2 text-sm text-muted">The goals this project contributes towards.</p>
                        <x-sdg-badges :goals="$project->sdgs" class="mt-4" />
                    </section>
                @endif

                @if ($gallery->isNotEmpty())
                    <section class="mt-12" aria-labelledby="project-gallery">
                        <h2 id="project-gallery" class="text-2xl text-ink">Gallery</h2>
                        <ul class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3">
                            @foreach ($gallery as $item)
                                <li>
                                    <button type="button" class="block w-full cursor-zoom-in overflow-hidden rounded-card"
                                        data-lightbox="project-{{ $content->id }}"
                                        data-lightbox-full="{{ $service->url($item, ['quality' => 'auto:best', 'width' => 2000]) }}"
                                        data-lightbox-alt="{{ $item->alt }}"
                                        data-lightbox-caption="{{ $item->caption }}">
                                        <x-picture :media="$item" ratio="aspect-[4/3]" :width="480" sizes="(min-width: 640px) 240px, 45vw" />
                                        <span class="sr-only">Enlarge: {{ $item->alt ?? $item->title }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($documents->isNotEmpty())
                    <section class="mt-12" aria-labelledby="project-documents">
                        <h2 id="project-documents" class="text-2xl text-ink">Documents</h2>
                        <ul class="mt-4 space-y-2">
                            @foreach ($documents as $document)
                                <li>
                                    <a href="{{ $service->url($document) }}" class="inline-flex items-center gap-2 font-medium text-primary-700 hover:underline">
                                        {{ $document->title }}
                                        <span class="text-xs text-muted">({{ Str::upper(Str::afterLast($document->mime, '/')) }}, {{ round($document->size / 1024) }} KB)</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </article>

            <aside class="lg:col-span-4">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Project details</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            @if ($project?->status)
                                <div>
                                    <dt class="font-semibold text-ink">Status</dt>
                                    <dd class="mt-1"><x-status-pill :status="$project->status" /></dd>
                                </div>
                            @endif

                            @foreach ([
                                'Program' => $project?->program?->title,
                                'Location' => collect([$project?->location, $project?->state, $project?->country])->filter()->implode(', '),
                                'Period' => $project?->period,
                                'Beneficiaries' => $project?->beneficiary_category,
                                'People reached' => $project?->beneficiary_count ? number_format($project->beneficiary_count) : null,
                            ] as $label => $value)
                                @if (filled($value))
                                    <div>
                                        <dt class="font-semibold text-ink">{{ $label }}</dt>
                                        <dd class="mt-0.5 text-muted">{{ $value }}</dd>
                                    </div>
                                @endif
                            @endforeach

                            @if ($project?->public_budget_minor)
                                <div>
                                    <dt class="font-semibold text-ink">Published budget</dt>
                                    <dd class="mt-0.5 text-muted">
                                        {{ $project->budget_currency }} {{ number_format($project->public_budget_minor / 100, 2) }}
                                    </dd>
                                </div>
                            @endif

                            @foreach (['Partners' => $project?->partners, 'Funders' => $project?->donors] as $label => $list)
                                @if (filled($list))
                                    <div>
                                        <dt class="font-semibold text-ink">{{ $label }}</dt>
                                        <dd class="mt-0.5 text-muted">{{ collect($list)->implode(', ') }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>

                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Support this project</h2>
                        <a href="{{ route('donate') }}" class="btn-primary mt-4 w-full">Donate now</a>
                    </div>

                    <x-share-links :title="$content->title" />
                </div>
            </aside>
        </div>
    </div>

    <x-donate-cta />
</x-layouts.site>
