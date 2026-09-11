@props(['heading' => 'Membership application', 'description' => null])

@php
    $types = App\Models\MembershipApplication::TYPES;
    $minimum = App\Models\MembershipApplication::MINIMUM_MEMBERS;
@endphp

<section {{ $attributes->class(['card p-6 sm:p-8']) }} aria-labelledby="membership-application">
    <h2 id="membership-application" class="text-2xl text-ink">{{ $heading }}</h2>

    @if ($description)
        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $description }}</p>
    @endif

    <x-alert class="mt-5" />

    <form method="POST" action="{{ route('membership.store') }}" class="mt-6 space-y-5">
        @csrf
        <x-form.honeypot id="membership" />

        <fieldset class="space-y-5">
            <legend class="field-label mb-1">Contact person</legend>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field name="contact_name" label="Contact name" required autocomplete="name" />
                <x-form.field name="contact_phone" label="Contact phone" type="tel" required autocomplete="tel" />
            </div>
        </fieldset>

        <fieldset class="space-y-5">
            <legend class="field-label mb-1">The co-operative society</legend>

            <x-form.field name="organisation_name" label="Name of organisation" required
                autocomplete="organization" />

            <x-form.field name="organisation_address" label="Address of organisation" type="textarea"
                :rows="3" required />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field name="telephone" label="Telephone" type="tel" hint="Optional." />
                <x-form.field name="email" label="Email address" type="email" required autocomplete="email" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field name="cooperative_type" label="Type of co-operative" type="select"
                    :options="$types" prompt="Select a sector" required />

                <x-form.field name="organisation_website" label="Website" type="url"
                    hint="Optional. Include https://" placeholder="https://" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.field name="member_count" label="Number of members" type="number" required
                    :hint="'Minimum of '.$minimum.'. Please attach the member list when we contact you.'"
                    min="{{ $minimum }}" />

                <x-form.field name="ethnic_group" label="Ethnic group" hint="Optional." />
            </div>

            <x-form.field name="message" label="Anything else we should know?" type="textarea" :rows="4"
                hint="Optional." />
        </fieldset>

        <x-form.consent
            text="I confirm that all of the information I have supplied is accurate, and I agree to provide any further information called for in support of this application." />

        <button type="submit" class="btn-secondary w-full sm:w-auto">Submit application</button>
    </form>
</section>
