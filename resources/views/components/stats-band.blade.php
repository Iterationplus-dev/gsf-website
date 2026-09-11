@props(['metrics'])

{{-- Reported figures, each carrying the source it came from. A metric with no
     source cannot be published at all, which is enforced on the model. --}}
@if ($metrics->isNotEmpty())
    <section {{ $attributes->class(['border-t border-line bg-sunken']) }} aria-labelledby="stats-band">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <x-section-heading
                eyebrow="By the numbers"
                title="The work so far"
                lead="Figures the foundation has published about its own reach, and what this site currently records."
                id="stats-band"
            />

            <x-impact-counters :metrics="$metrics" class="mt-12" />

            <p class="mt-8 text-sm text-muted">
                Every figure is recorded with its source.
                <a href="{{ route('impact') }}" class="font-medium text-primary-700 underline">See how we report impact</a>.
            </p>
        </div>
    </section>
@endif
