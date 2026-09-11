@props([
    'type' => 'contact',
    'heading' => 'Send us a message',
    'description' => null,
    'submit' => 'Send message',
    'organisation' => false,
])

<section {{ $attributes->class(['card p-6 sm:p-8']) }} aria-labelledby="enquiry-{{ $type }}">
    <h2 id="enquiry-{{ $type }}" class="text-2xl text-ink">{{ $heading }}</h2>

    @if ($description)
        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $description }}</p>
    @endif

    <x-alert class="mt-5" />

    <form method="POST" action="{{ route('enquiries.store') }}" class="mt-6 space-y-5">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <x-form.honeypot :id="$type" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.field name="name" label="Full name" required autocomplete="name" />
            <x-form.field name="email" label="Email address" type="email" required autocomplete="email" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.field name="phone" label="Phone number" type="tel" autocomplete="tel"
                hint="Optional." />

            @if ($organisation)
                <x-form.field name="organization" label="Organisation" autocomplete="organization" />
            @endif
        </div>

        <x-form.field name="message" label="Your message" type="textarea" required
            :placeholder="$type === 'volunteer' ? 'Tell us about your skills and how much time you can offer.' : null" />

        <x-form.consent />

        <button type="submit" class="btn-secondary w-full sm:w-auto">{{ $submit }}</button>
    </form>
</section>
