@php
    $confirming = $action === 'confirm';
@endphp

<x-layouts.site :title="$confirming ? 'Confirm your subscription' : 'Unsubscribe'" noindex>
    <div class="mx-auto max-w-2xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="card p-8 text-center sm:p-12">
            <h1 class="text-3xl text-ink">
                {{ $confirming ? 'Confirm your subscription' : 'Unsubscribe from our newsletter' }}
            </h1>

            <p class="mt-4 text-lg leading-relaxed text-muted">
                {{ $confirming
                    ? 'Confirm that you would like to receive news, project updates and opportunities to get involved.'
                    : 'You will stop receiving email from Global Support Foundation. You can subscribe again at any time.' }}
            </p>

            {{-- The state change happens on POST, not on opening the link: an email
                 scanner following the URL must not unsubscribe someone. --}}
            <form method="POST"
                action="{{ $confirming
                    ? route('newsletter.confirm.store', $subscriber)
                    : route('newsletter.unsubscribe.store', $subscriber) }}"
                class="mt-8">
                @csrf
                <button type="submit" class="{{ $confirming ? 'btn-primary' : 'btn-secondary' }}">
                    {{ $confirming ? 'Confirm subscription' : 'Unsubscribe' }}
                </button>
            </form>

            <p class="mt-6 text-sm">
                <a href="{{ route('home') }}" class="text-primary-700 hover:underline">Return to the website</a>
            </p>
        </div>
    </div>
</x-layouts.site>
