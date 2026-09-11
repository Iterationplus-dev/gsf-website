<div>
    <div class="card p-6">
        <label for="search-term" class="field-label">Search the website</label>
        <div class="flex flex-col gap-3 sm:flex-row">
            <input
                id="search-term"
                type="search"
                wire:model.live.debounce.400ms="term"
                placeholder="Try co-operative, training, agriculture…"
                autocomplete="off"
                class="field-input flex-1"
                aria-describedby="search-hint">

            <label for="search-type" class="sr-only">Filter by type</label>
            <select id="search-type" wire:model.live="type" class="field-input sm:w-52">
                <option value="">Everything</option>
                @foreach (App\Livewire\SiteSearch::SEARCHABLE as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <p id="search-hint" class="mt-2 text-sm text-muted">
            Enter at least two characters. Results update as you type.
        </p>
    </div>

    {{-- aria-live so that a screen reader hears the result count change without
         the focus being pulled out of the search box. --}}
    <div class="mt-8" aria-live="polite" aria-atomic="true">
        <div wire:loading.delay class="text-sm text-muted">Searching…</div>

        <div wire:loading.remove>
            @if (mb_strlen(trim($term)) < 2)
                <p class="text-sm text-muted">Start typing to search programs, projects, news, publications and pages.</p>
            @elseif ($results->isEmpty())
                <x-empty-state
                    title="No results for “{{ $term }}”"
                    description="Try a shorter or more general term, or browse our programs and projects directly."
                >
                    <a href="{{ route('programs.index') }}" class="btn-outline">Programs</a>
                    <a href="{{ route('projects.index') }}" class="btn-outline">Projects</a>
                </x-empty-state>
            @else
                <p class="text-sm text-muted">
                    {{ $results->count() }} {{ Str::plural('result', $results->count()) }} for “{{ $term }}”.
                </p>

                <ul class="mt-6 space-y-4">
                    @foreach ($results as $result)
                        <li class="card p-5">
                            <p class="eyebrow text-accent-600">{{ App\Livewire\SiteSearch::SEARCHABLE[$result->type] ?? $result->type }}</p>
                            <h2 class="mt-2 text-lg text-ink">
                                <a href="{{ $result->url }}" class="hover:text-primary-700 hover:underline">{{ $result->title }}</a>
                            </h2>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ Str::limit($result->excerpt, 180) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
