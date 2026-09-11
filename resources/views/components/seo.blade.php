@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'noindex' => false,
])

@php
    $organisation = ($settings['org.name'] ?? '') ?: 'Global Support Foundation';
    // Search results truncate around 60 characters. A long page title keeps the
    // whole title and drops the organisation suffix, rather than being cut mid-word.
    $shortName = ($settings['org.short_name'] ?? '') ?: 'GSF';
    $pageTitle = $title
        ? (mb_strlen($title) > 46 ? $title : $title.' | '.$organisation)
        : $organisation.' | Youth Co-operatives in Nigeria';

    if ($title && mb_strlen($pageTitle) > 60 && mb_strlen($title.' | '.$shortName) <= 60) {
        $pageTitle = $title.' | '.$shortName;
    }
    $metaDescription = $description ?: (($settings['seo.description'] ?? '') ?: 'Global Support Foundation supports youth co-operative enterprises through business training, advice and grassroots entrepreneurship in Nigeria and across Africa.');
    $metaDescription = Str::squish(html_entity_decode(strip_tags($metaDescription), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $canonical = url()->current();
    $queryKeys = match (request()->route()?->getName()) {
        'projects.index' => ['program', 'status', 'page'],
        'news.index' => ['category', 'page'],
        'stories.index', 'resources.index', 'gallery' => ['page'],
        default => [],
    };
    $canonicalQuery = collect(request()->only($queryKeys))
        ->filter(fn ($value): bool => is_scalar($value) && (string) $value !== '')
        ->all();
    $page = filter_var($canonicalQuery['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
    if ($page > 1) {
        $canonicalQuery['page'] = $page;
        $pageTitle .= ' | Page '.$page;
    } else {
        unset($canonicalQuery['page']);
    }
    ksort($canonicalQuery);
    if ($canonicalQuery !== []) {
        $canonical .= '?'.http_build_query($canonicalQuery, '', '&', PHP_QUERY_RFC3986);
    }
    $logo = ($settings['org.logo'] ?? '') ?: asset('images/logo.jpg');
    $hasPageImage = filled($image);
    $image = $image ?: $logo;
    $isArticle = request()->routeIs('news.show');
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ Str::limit(strip_tags($metaDescription), 157) }}">
<link rel="canonical" href="{{ $canonical }}">

@if ($noindex)
    <meta name="robots" content="noindex, follow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif

<meta property="og:type" content="{{ $isArticle ? 'article' : 'website' }}">
<meta property="og:locale" content="en_NG">
<meta property="og:site_name" content="{{ $organisation }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($metaDescription), 200) }}">
<meta property="og:url" content="{{ $canonical }}">
@if ($image)
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:alt" content="{{ $hasPageImage ? ($title ?: $organisation) : $organisation.' logo' }}">
    <meta name="twitter:card" content="{{ $hasPageImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:image" content="{{ $image }}">
@else
    <meta name="twitter:card" content="summary">
@endif
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($metaDescription), 200) }}">

{{-- Organisation structured data. Only fields the organisation has actually
     confirmed are emitted: an empty social profile or registration number is
     omitted rather than published as a blank claim. --}}
@php
    $sameAs = collect([
        $settings['social.linkedin'] ?? null,
        $settings['social.facebook'] ?? null,
        $settings['social.instagram'] ?? null,
        $settings['social.x'] ?? null,
        $settings['social.youtube'] ?? null,
    ])->filter()->values();

    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'NGO',
        '@id' => url('/').'#organisation',
        'logo' => $logo,
        'name' => $settings['org.legal_name'] ?? $organisation,
        'alternateName' => $organisation,
        'url' => url('/'),
        'description' => $settings['org.description'] ?? null,
        'foundingDate' => '2015-08',
        'sameAs' => $sameAs->isNotEmpty() ? $sameAs->all() : null,
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $settings['contact.address'] ?? null,
            'addressLocality' => $settings['contact.city'] ?? null,
            'addressRegion' => $settings['contact.state'] ?? null,
            'addressCountry' => $settings['contact.country'] ?? null,
        ]),
        'email' => $settings['contact.email'] ?? null,
        'telephone' => $settings['contact.phone_primary'] ?? null,
    ]);
@endphp

<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>

@php
    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => url('/').'#website',
        'url' => url('/'),
        'name' => $organisation,
        'publisher' => ['@id' => url('/').'#organisation'],
        'inLanguage' => 'en',
    ];
@endphp
<script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
{{ $slot ?? '' }}
