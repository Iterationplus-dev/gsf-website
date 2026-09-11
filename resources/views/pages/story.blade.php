<x-detail-page
    :content="$content"
    eyebrow="Story of impact"
    :breadcrumbs="[
        ['label' => 'Stories', 'url' => route('stories.index')],
        ['label' => $content->title],
    ]"
/>
