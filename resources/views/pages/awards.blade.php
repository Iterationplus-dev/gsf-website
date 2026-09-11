<x-layouts.site
    title="Awards & Recognition"
    description="Awards and recognition received in connection with Global Support Foundation and its founder's work in co-operative development.">

    <x-page-header
        eyebrow="Recognition"
        title="Awards & Recognition"
        lead="Certificates and honours held in the organization's archive. Each record is published only once its details have been confirmed."
        :breadcrumbs="[['label' => 'Awards']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($awards->isEmpty())
            <x-empty-state
                title="Award records are being verified"
                description="Certificates from the organization's archive are held securely, but the award titles and awarding institutions have not yet been transcribed and confirmed. They will be published once they have been."
            >
                <a href="{{ route('leadership.index') }}" class="btn-outline">Meet our leadership</a>
            </x-empty-state>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($awards as $award)
                    <x-card.award :award="$award" />
                @endforeach
            </div>

            <p class="mt-8 text-sm text-muted">Select a certificate to view it at full size.</p>
        @endif
    </div>

    <x-donate-cta />

    <x-stats-band :metrics="$metrics" />
</x-layouts.site>
