@props(['tone' => 'dark'])

@php
    $dark = $tone === 'dark';
@endphp

<section {{ $attributes }} aria-labelledby="newsletter-heading">
    <h2 id="newsletter-heading" @class(['text-lg font-semibold', 'text-white' => $dark, 'text-ink' => ! $dark])>
        Newsletter
    </h2>
    <p @class(['mt-1.5 text-sm', 'text-primary-200' => $dark, 'text-muted' => ! $dark])>
        News, project updates and opportunities to take part. Unsubscribe at any time.
    </p>

    @if (session('success'))
        <p @class(['mt-3 text-sm font-medium', 'text-primary-100' => $dark, 'text-success-600' => ! $dark]) role="status">
            {{ session('success') }}
        </p>
    @endif

    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-4">
        @csrf
        <x-form.honeypot id="newsletter" />

        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <label for="newsletter-email" class="sr-only">Email address</label>
                <input type="email" id="newsletter-email" name="email" required autocomplete="email"
                    placeholder="you@example.org"
                    @class([
                        'w-full rounded-md px-3.5 py-2.5 text-base sm:text-sm',
                        'border border-primary-700 bg-primary-800 text-white placeholder:text-primary-300' => $dark,
                        'field-input' => ! $dark,
                    ])>
            </div>
            <button type="submit" @class(['btn px-5 py-2.5', 'bg-white text-primary-900 hover:bg-primary-100' => $dark, 'btn-secondary' => ! $dark])>
                Subscribe
            </button>
        </div>

        <label @class(['mt-3 flex items-start gap-2.5 text-xs', 'text-primary-200' => $dark, 'text-muted' => ! $dark])>
            <input type="checkbox" name="consent" value="1" required
                class="mt-0.5 size-5 shrink-0 rounded border-primary-600 text-accent-600">
            <span>
                I agree to receive emails from Global Support Foundation and accept the
                <a href="{{ route('pages.show', 'privacy-policy') }}" class="underline">privacy notice</a>.
            </span>
        </label>
    </form>
</section>
