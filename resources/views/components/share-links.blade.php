@props(['title'])

@php
    $url = url()->current();
    $encodedUrl = rawurlencode($url);
    $encodedTitle = rawurlencode($title);

    $targets = [
        'LinkedIn' => 'https://www.linkedin.com/sharing/share-offsite/?url='.$encodedUrl,
        'X' => 'https://x.com/intent/tweet?url='.$encodedUrl.'&text='.$encodedTitle,
        'Facebook' => 'https://www.facebook.com/sharer/sharer.php?u='.$encodedUrl,
        'Email' => 'mailto:?subject='.$encodedTitle.'&body='.$encodedUrl,
    ];
@endphp

<nav class="card p-6" aria-labelledby="share-heading">
    <h2 id="share-heading" class="text-lg text-ink">Share this page</h2>
    <ul class="mt-4 flex flex-wrap gap-2">
        @foreach ($targets as $label => $href)
            <li>
                {{-- noreferrer as well as noopener: the sharing target has no
                     business knowing which page the visitor came from. --}}
                <a href="{{ $href }}"
                    @if ($label !== 'Email') target="_blank" rel="noopener noreferrer" @endif
                    class="inline-flex rounded-md border border-line px-3 py-1.5 text-sm font-medium text-muted hover:border-primary-600 hover:text-primary-700">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
