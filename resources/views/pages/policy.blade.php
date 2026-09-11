<x-detail-page
    :content="$content"
    eyebrow="Policy"
    :breadcrumbs="[
        ['label' => 'Policies', 'url' => route('policies.index')],
        ['label' => $content->title],
    ]">

    <x-slot:meta>
        <h2 class="text-lg text-ink">Document status</h2>
        <dl class="mt-4 space-y-3 text-sm">
            <div>
                <dt class="font-semibold text-ink">Approved</dt>
                <dd class="mt-0.5 text-muted">{{ $content->detail('approved_on') ?: 'Not recorded' }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-ink">Next review</dt>
                <dd class="mt-0.5 text-muted">{{ $content->detail('review_due') ?: 'Not recorded' }}</dd>
            </div>
        </dl>
    </x-slot:meta>
</x-detail-page>
