<x-layouts.site
    title="Projects"
    description="Projects delivered by Global Support Foundation, with their location, status, beneficiaries and results.">

    <x-page-header
        eyebrow="Our work"
        title="Projects"
        lead="What we are delivering, where, for whom, and with what result."
        :breadcrumbs="[['label' => 'Projects']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">

        {{-- Filters submit as a plain GET form, so results stay linkable,
             shareable and usable without JavaScript. --}}
        <form method="GET" action="{{ route('projects.index') }}" class="card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-48 flex-1">
                <label for="filter-program" class="field-label">Program</label>
                <select id="filter-program" name="program" class="field-input">
                    <option value="">All programs</option>
                    @foreach ($programs as $option)
                        <option value="{{ $option->slug }}" @selected($program === $option->slug)>{{ $option->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-48 flex-1">
                <label for="filter-status" class="field-label">Status</label>
                <select id="filter-status" name="status" class="field-input">
                    <option value="">Any status</option>
                    @foreach (App\Models\Project::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-secondary">Apply filters</button>
                @if ($status || $program)
                    <a href="{{ route('projects.index') }}" class="btn-outline">Clear</a>
                @endif
            </div>
        </form>

        <div class="mt-10">
            @if ($projects->isEmpty())
                <x-empty-state
                    title="No projects match this view"
                    description="Project records are published once the organisation has verified their details. Try clearing the filters, or explore our programs."
                >
                    <a href="{{ route('programs.index') }}" class="btn-outline">View programs</a>
                </x-empty-state>
            @else
                <p class="mb-6 text-sm text-muted" role="status">
                    Showing {{ $projects->firstItem() }}–{{ $projects->lastItem() }} of {{ $projects->total() }} projects.
                </p>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-card.project :project="$project" />
                    @endforeach
                </div>

                <div class="mt-12">{{ $projects->links() }}</div>
            @endif
        </div>
    </div>

    <x-donate-cta />

    <x-explore-our-work current="projects" />
</x-layouts.site>
