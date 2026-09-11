@php
    $socials = collect([
        'LinkedIn' => $settings['social.linkedin'] ?? null,
        'Facebook' => $settings['social.facebook'] ?? null,
        'Instagram' => $settings['social.instagram'] ?? null,
        'X' => $settings['social.x'] ?? null,
        'YouTube' => $settings['social.youtube'] ?? null,
    ])->filter();

    $phones = collect([
        $settings['contact.phone_primary'] ?? null,
        $settings['contact.phone_secondary'] ?? null,
        $settings['contact.phone_international'] ?? null,
    ])->filter();
@endphp

<footer class="mt-24 bg-primary-900 text-primary-100">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <img src="{{ ($settings['org.logo'] ?? '') ?: asset('images/logo.jpg') }}" alt="" aria-hidden="true"
                    width="167" height="165" loading="lazy" decoding="async"
                    class="logo-glow logo-glow-on-dark mb-5 size-24 rounded-full object-cover">

                <p class="font-serif text-xl font-semibold text-white">{{ $settings['org.legal_name'] ?? 'Global Support Foundation' }}</p>
                <p class="mt-4 text-sm leading-relaxed text-primary-200">{{ $settings['org.description'] ?? '' }}</p>

                @if ($settings['org.founded'] ?? null)
                    <p class="mt-4 text-sm text-primary-300">Established {{ $settings['org.founded'] }}.</p>
                @endif

                @if (($settings['registration.number'] ?? '') !== '')
                    <p class="mt-1 text-sm text-primary-300">
                        Registered with {{ $settings['registration.authority'] }} — {{ $settings['registration.number'] }}
                    </p>
                @endif
            </div>

            <nav class="lg:col-span-2" aria-labelledby="footer-about">
                <h2 id="footer-about" class="eyebrow text-primary-300">About</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('pages.show', 'about-us') }}" @if (url()->current() === route('pages.show', 'about-us')) aria-current="page" @endif class="hover:text-white hover:underline">About Us</a></li>
                    <li><a href="{{ route('pages.show', 'our-history') }}" @if (url()->current() === route('pages.show', 'our-history')) aria-current="page" @endif class="hover:text-white hover:underline">Our History</a></li>
                    <li><a href="{{ route('pages.show', 'our-founder') }}" @if (url()->current() === route('pages.show', 'our-founder')) aria-current="page" @endif class="hover:text-white hover:underline">Our Founder</a></li>
                    <li><a href="{{ route('leadership.index') }}" @if (url()->current() === route('leadership.index')) aria-current="page" @endif class="hover:text-white hover:underline">Leadership</a></li>
                    <li><a href="{{ route('awards.index') }}" @if (url()->current() === route('awards.index')) aria-current="page" @endif class="hover:text-white hover:underline">Awards</a></li>
                </ul>
            </nav>

            <nav class="lg:col-span-2" aria-labelledby="footer-work">
                <h2 id="footer-work" class="eyebrow text-primary-300">Our work</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('programs.index') }}" @if (url()->current() === route('programs.index')) aria-current="page" @endif class="hover:text-white hover:underline">Programs</a></li>
                    <li><a href="{{ route('projects.index') }}" @if (url()->current() === route('projects.index')) aria-current="page" @endif class="hover:text-white hover:underline">Projects</a></li>
                    <li><a href="{{ route('impact') }}" @if (url()->current() === route('impact')) aria-current="page" @endif class="hover:text-white hover:underline">Our Impact</a></li>
                    <li><a href="{{ route('stories.index') }}" @if (url()->current() === route('stories.index')) aria-current="page" @endif class="hover:text-white hover:underline">Success Stories</a></li>
                    <li><a href="{{ route('news.index') }}" @if (url()->current() === route('news.index')) aria-current="page" @endif class="hover:text-white hover:underline">Blog</a></li>
                    <li><a href="{{ route('partners') }}" @if (url()->current() === route('partners')) aria-current="page" @endif class="hover:text-white hover:underline">Partners</a></li>
                    <li><a href="{{ route('gallery') }}" @if (url()->current() === route('gallery')) aria-current="page" @endif class="hover:text-white hover:underline">Photo Gallery</a></li>
                </ul>
            </nav>

            <nav class="lg:col-span-2" aria-labelledby="footer-accountability">
                <h2 id="footer-accountability" class="eyebrow text-primary-300">Accountability</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('resources.index') }}" @if (url()->current() === route('resources.index')) aria-current="page" @endif class="hover:text-white hover:underline">Publications</a></li>
                    <li><a href="{{ route('policies.index') }}" @if (url()->current() === route('policies.index')) aria-current="page" @endif class="hover:text-white hover:underline">Policies</a></li>
                    <li><a href="{{ route('pages.show', 'transparency') }}" @if (url()->current() === route('pages.show', 'transparency')) aria-current="page" @endif class="hover:text-white hover:underline">Transparency</a></li>
                    <li><a href="{{ route('pages.show', 'governance') }}" @if (url()->current() === route('pages.show', 'governance')) aria-current="page" @endif class="hover:text-white hover:underline">Governance</a></li>
                    <li><a href="{{ route('pages.show', 'privacy-policy') }}" @if (url()->current() === route('pages.show', 'privacy-policy')) aria-current="page" @endif class="hover:text-white hover:underline">Privacy Notice</a></li>
                    <li><a href="{{ route('pages.show', 'cookie-policy') }}" @if (url()->current() === route('pages.show', 'cookie-policy')) aria-current="page" @endif class="hover:text-white hover:underline">Cookie Policy</a></li>
                </ul>
            </nav>

            <div class="lg:col-span-2">
                <h2 class="eyebrow text-primary-300">Contact</h2>
                <address class="mt-4 space-y-2 text-sm not-italic">
                    @if ($settings['contact.address'] ?? null)
                        {{-- Each line is optional on its own: a settings table
                             filled in part must not take the whole site down. --}}
                        <p>
                            {{ $settings['contact.address'] }}<br>
                            {{ collect([$settings['contact.city'] ?? null, $settings['contact.state'] ?? null])->filter()->implode(', ') }}<br>
                            {{ $settings['contact.country'] ?? '' }}
                        </p>
                    @endif

                    @if ($settings['contact.email'] ?? null)
                        <p><a href="mailto:{{ $settings['contact.email'] }}" class="hover:text-white hover:underline">{{ $settings['contact.email'] }}</a></p>
                    @endif

                    @foreach ($phones as $phone)
                        <p><a href="tel:{{ Str::remove(' ', $phone) }}" class="hover:text-white hover:underline">{{ $phone }}</a></p>
                    @endforeach
                </address>

                @if ($socials->isNotEmpty())
                    <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm">
                        @foreach ($socials as $label => $url)
                            <li><a href="{{ $url }}" rel="noopener noreferrer" target="_blank" class="hover:text-white hover:underline">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="mt-14 grid gap-8 border-t border-primary-800 pt-10 lg:grid-cols-2 lg:items-center">
            <x-newsletter-form class="lg:max-w-lg" />

            <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                <a href="{{ route('donate') }}" @if (url()->current() === route('donate')) aria-current="page" @endif class="btn-primary">Donate now</a>
                <a href="{{ route('pages.show', 'partner-with-us') }}" @if (url()->current() === route('pages.show', 'partner-with-us')) aria-current="page" @endif class="btn-ghost-light">Partner with us</a>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-primary-800 pt-8 text-xs text-primary-300 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $settings['org.legal_name'] ?? 'Global Support Foundation' }}. All rights reserved.</p>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                {{-- Reopens the preferences dialog. A button, not a link: it
                     goes nowhere, and script is what makes it work. --}}
                <button type="button" data-consent-action="open" class="underline hover:text-white">Cookie settings</button>
                <p><a href="{{ route('pages.show', 'contact-us') }}" @if (url()->current() === route('pages.show', 'contact-us')) aria-current="page" @endif class="hover:text-white hover:underline">Port Harcourt, Rivers State, Nigeria</a></p>
            </div>
        </div>
    </div>
</footer>
