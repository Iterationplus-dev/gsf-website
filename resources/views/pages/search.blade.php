<x-layouts.site title="Search" description="Search projects, programs, news, publications and pages." noindex>
    <x-page-header
        title="Search"
        lead="Search across programs, projects, news, publications and pages."
        :breadcrumbs="[['label' => 'Search']]"
    />

    <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        @livewire('site-search', ['term' => $term])
    </div>
</x-layouts.site>
