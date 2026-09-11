@php
    $currencies = config('foundation.currencies');
    $enabled = config('foundation.donations_enabled') && config('foundation.paystack_secret');
    $presets = [5000, 10000, 25000, 50000, 100000];
@endphp

<x-layouts.site
    title="Donate"
    description="Support grassroots enterprise in Nigeria. Your donation funds training, business advice and co-operative development.">

    <x-page-header
        tone="dark"
        eyebrow="Support our work"
        title="Donate"
        lead="Your gift funds training, business advice and the practical work of getting a co-operative registered and trading."
        :breadcrumbs="[['label' => 'Donate']]"
    />

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-7">
                @if (! $enabled)
                    <div class="card border-warning-600/40 bg-warning-600/5 p-6" role="status">
                        <h2 class="text-lg text-ink">Online giving is not yet available</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            We are completing the setup of our online payment facility. In the meantime,
                            please contact the foundation directly and we will arrange your gift.
                        </p>
                        <a href="{{ route('pages.show', 'contact-us') }}" class="btn-secondary mt-5">Contact us</a>
                    </div>
                @else
                    <section class="card p-6 sm:p-8" aria-labelledby="donate-form-heading">
                        <h2 id="donate-form-heading" class="text-2xl text-ink">Make a donation</h2>

                        <x-alert class="mt-5" />

                        <form method="POST" action="{{ route('donations.store') }}" class="mt-6 space-y-6"
                            x-data="{ amount: '{{ old('amount', '') }}' }">
                            @csrf
                            <x-form.honeypot id="donate" />

                            <fieldset>
                                <legend class="field-label">Choose an amount</legend>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($presets as $preset)
                                        <button type="button"
                                            @click="amount = '{{ $preset }}'"
                                            :class="amount === '{{ $preset }}'
                                                ? 'border-primary-700 bg-primary-700 text-white'
                                                : 'border-line text-ink hover:border-primary-600'"
                                            class="rounded-md border px-4 py-2.5 text-sm font-semibold tabular-nums">
                                            {{ number_format($preset) }}
                                        </button>
                                    @endforeach
                                </div>

                                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                    <div class="sm:col-span-2">
                                        <label for="amount" class="field-label">
                                            Amount <span class="text-accent-600" aria-hidden="true">*</span>
                                            <span class="sr-only">(required)</span>
                                        </label>
                                        <input id="amount" name="amount" type="text" inputmode="decimal" required
                                            x-model="amount" placeholder="10000"
                                            aria-describedby="amount-hint @error('amount') amount-error @enderror"
                                            aria-invalid="{{ $errors->has('amount') ? 'true' : 'false' }}"
                                            @class(['field-input', 'border-error-600' => $errors->has('amount')])>
                                        <p id="amount-hint" class="mt-1.5 text-sm text-muted">Enter any amount, or choose one above.</p>
                                        @error('amount')
                                            <p id="amount-error" class="field-error"><span>{{ $message }}</span></p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="currency" class="field-label">Currency</label>
                                        <select id="currency" name="currency" class="field-input" required>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency }}" @selected(old('currency') === $currency)>{{ $currency }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </fieldset>

                            @if ($campaigns->isNotEmpty())
                                <div>
                                    <label for="campaign_id" class="field-label">What would you like to support?</label>
                                    <select id="campaign_id" name="campaign_id" class="field-input">
                                        <option value="">Where it is needed most</option>
                                        @foreach ($campaigns as $campaign)
                                            <option value="{{ $campaign->id }}" @selected((string) old('campaign_id') === (string) $campaign->id)>
                                                {{ $campaign->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="grid gap-5 sm:grid-cols-2">
                                <x-form.field name="name" label="Full name" required autocomplete="name" />
                                <x-form.field name="email" label="Email address" type="email" required autocomplete="email"
                                    hint="Your receipt is sent here." />
                            </div>

                            <x-form.field name="phone" label="Phone number" type="tel" autocomplete="tel" hint="Optional." />

                            <x-form.field name="message" label="Message" type="textarea" rows="3"
                                hint="Optional. Tell us why you are giving." />

                            <label class="flex items-start gap-3 text-sm text-muted">
                                <input type="checkbox" name="anonymous" value="1" @checked(old('anonymous'))
                                    class="mt-0.5 size-4 shrink-0 rounded border-line text-primary-700 focus:ring-primary-600">
                                <span>Make my donation anonymous. Your name will not appear in any public listing.</span>
                            </label>

                            <x-form.consent text="I agree that Global Support Foundation may use these details to process my donation and send my receipt." />

                            <button type="submit" class="btn-primary w-full">Continue to secure payment</button>

                            <p class="text-center text-xs text-muted">
                                Payment is processed by Paystack. Your card details are entered on Paystack's
                                secure checkout and never reach this website.
                            </p>
                        </form>
                    </section>
                @endif
            </div>

            <aside class="lg:col-span-5">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Where your money goes</h2>
                        <ul class="mt-4 space-y-3 text-sm text-muted">
                            @foreach ([
                                'Training' => 'Business management, co-operative skills and the practical experience behind a foundation qualification.',
                                'Business advice' => 'Feasibility studies, business analysis and management reviews — free of charge in most cases.',
                                'Formation support' => 'Getting a group from an interested meeting to a registered, trading co-operative.',
                                'Follow-up' => 'The mentoring visits that decide whether an enterprise is still trading a year later.',
                            ] as $heading => $description)
                                <li>
                                    <span class="block font-semibold text-ink">{{ $heading }}</span>
                                    <span class="mt-0.5 block leading-relaxed">{{ $description }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Giving securely</h2>
                        <ul class="mt-4 space-y-2.5 text-sm text-muted">
                            <li>Payments are confirmed on our server against Paystack's records, not by your browser.</li>
                            <li>We never see or store your card details.</li>
                            <li>Your receipt reaches you by email with a unique reference.</li>
                            <li>Donor details are never sent to analytics services.</li>
                        </ul>
                        <a href="{{ route('pages.show', 'privacy-policy') }}" class="mt-4 inline-block text-sm font-semibold text-primary-700 hover:underline">
                            Read our privacy notice &rarr;
                        </a>
                    </div>

                    <div class="card p-6">
                        <h2 class="text-lg text-ink">Other ways to give</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            For institutional gifts, multi-year funding or a transfer arrangement, speak to us directly.
                        </p>
                        <a href="{{ route('pages.show', 'partner-with-us') }}" class="btn-outline mt-4 w-full">Partner with us</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.site>
