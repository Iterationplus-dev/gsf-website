<x-layouts.site title="Page not found" noindex>
    <div class="mx-auto max-w-3xl px-4 py-24 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="eyebrow text-accent-600">Error 404</p>
            <h1 class="mt-3 text-4xl text-ink sm:text-5xl">We could not find that page</h1>
            <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-muted">
                The page may have moved, or the address may be slightly wrong. These links cover most of
                what people come here for.
            </p>

            <div class="mt-9 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-secondary">Return home</a>
                <a href="{{ route('search') }}" class="btn-outline">Search the site</a>
            </div>
        </div>

        <ul class="mt-14 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['Our programs', 'The four areas of work we deliver.', route('programs.index')],
                ['Projects', 'What we are delivering, and where.', route('projects.index')],
                ['Our impact', 'Results, reach and how we measure them.', route('impact')],
                ['Contact us', 'Reach our office in Port Harcourt.', route('pages.show', 'contact-us')],
            ] as [$title, $description, $url])
                <li class="card p-6">
                    <h2 class="text-lg text-ink">
                        <a href="{{ $url }}" class="hover:text-primary-700 hover:underline">{{ $title }}</a>
                    </h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $description }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</x-layouts.site>
