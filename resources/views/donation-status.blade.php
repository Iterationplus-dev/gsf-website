@php
    $successful = $donation->status === 'success';
@endphp

<x-layouts.site title="Thank you" noindex>
    <div class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="card p-8 text-center sm:p-12">
            @if ($successful)
                <svg viewBox="0 0 20 20" class="mx-auto size-14 text-success-600" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.7-9.3-4.2 4.2a1 1 0 0 1-1.4 0l-2-2 1.4-1.4L8.8 10.8l3.5-3.5 1.4 1.4Z" clip-rule="evenodd"/>
                </svg>

                <h1 class="mt-6 text-3xl text-ink">Thank you, {{ $donation->display_name }}</h1>

                <p class="mt-4 text-lg leading-relaxed text-muted">
                    We have received your donation of
                    <strong class="text-ink">{{ $donation->currency }} {{ $donation->amount }}</strong>.
                    A receipt is on its way to your email address.
                </p>

                <dl class="mx-auto mt-8 max-w-sm space-y-2 rounded-md bg-sunken p-5 text-left text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Reference</dt>
                        <dd class="font-mono text-xs break-all text-ink">{{ $donation->reference }}</dd>
                    </div>
                    @if ($donation->campaign)
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted">Supporting</dt>
                            <dd class="text-ink">{{ $donation->campaign->title }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Received</dt>
                        <dd class="text-ink">{{ $donation->paid_at?->format('j F Y, H:i') }}</dd>
                    </div>
                </dl>

                <p class="mt-6 text-sm text-muted">Keep your reference safe. Quote it in any correspondence about this gift.</p>
            @else
                <h1 class="text-3xl text-ink">Your payment is being confirmed</h1>

                <p class="mt-4 text-lg leading-relaxed text-muted">
                    We have not yet received confirmation from the payment provider. This can take a
                    few minutes.
                </p>

                <p class="mt-4 rounded-md border border-warning-600/30 bg-warning-600/5 p-4 text-sm text-ink">
                    <strong>Please do not pay again.</strong> If your card has been charged, the donation will be
                    recorded automatically. Contact us with reference
                    <span class="font-mono text-xs break-all">{{ $donation->reference }}</span> if you have any concern.
                </p>
            @endif

            <div class="mt-9 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-secondary">Return home</a>
                <a href="{{ route('impact') }}" class="btn-outline">See our impact</a>
                @if ($successful)
                    <a href="{{ URL::temporarySignedRoute('donations.receipt', now()->addHour(), ['donation' => $donation->reference]) }}" class="btn-outline">View receipt</a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.site>
