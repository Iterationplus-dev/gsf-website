@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'noindex' => false,
])

<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo
        :title="$title"
        :description="$description"
        :image="$image"
        :noindex="$noindex"
    />

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

    {{-- Self-hosted @font-face rules and preloads for the families declared in
         vite.config.js. Without this the build downloads the font files but
         nothing references them, and the browser silently falls back to a
         system sans. --}}
    {{ Vite::fonts() }}

    @vite(['resources/css/app.css', 'resources/ts/app.ts'])

    @if (config('foundation.analytics_domain'))
        {{-- Privacy-conscious analytics: no personal data and no donation
             identifiers are ever passed to it. It is still served inert as
             `text/plain` and only turned into a real script by
             resources/ts/consent.ts once analytics consent is given, so the
             request is never made without it. --}}
        <script type="text/plain" data-consent-category="analytics" defer
            data-domain="{{ config('foundation.analytics_domain') }}"
            src="{{ config('foundation.analytics_script') }}"></script>
    @endif

    @stack('head')
</head>
<body class="min-h-screen bg-canvas font-sans text-ink">
    <a href="#main" class="skip-link">Skip to main content</a>

    <x-cookie-consent />

    <x-site-header />

    <main id="main" tabindex="-1">
        {{ $slot }}
    </main>

    <x-site-footer />

    {{-- Alpine drives the header menus and ships inside Livewire's bundle.
         Livewire only injects its assets on pages that actually render a
         component, so without this the header would be inert everywhere except
         the search page. --}}
    @livewireScripts

    @stack('scripts')
</body>
</html>
