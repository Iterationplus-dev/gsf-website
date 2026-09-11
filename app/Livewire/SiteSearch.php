<?php

namespace App\Livewire;

use App\Models\Content;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Site-wide search across published content.
 *
 * Results are drawn through the same `published()` scope the rest of the site
 * uses, so a draft, a scheduled article or demo content can never surface here.
 * The term lives in the query string, which keeps a result set linkable and
 * lets the browser's back button behave the way visitors expect.
 */
class SiteSearch extends Component
{
    #[Url(as: 'q', except: '')]
    public string $term = '';

    #[Url(except: '')]
    public string $type = '';

    /** Types worth searching, in the order results are grouped. */
    public const SEARCHABLE = [
        'program' => 'Programs',
        'project' => 'Projects',
        'post' => 'News',
        'publication' => 'Publications',
        'story' => 'Stories',
        'page' => 'Pages',
    ];

    /**
     * @return Collection<int, Content>
     */
    public function getResultsProperty(): Collection
    {
        $term = trim($this->term);

        if (mb_strlen($term) < 2) {
            return new Collection;
        }

        // `like` with a leading wildcard is acceptable at this content volume and
        // keeps the site free of a search-server dependency. Escape the wildcards
        // so a visitor searching for "100%" does not match everything.
        $escaped = '%'.addcslashes($term, '%_\\').'%';

        return Content::query()
            ->published()
            ->when(
                array_key_exists($this->type, self::SEARCHABLE),
                fn ($query) => $query->where('type', $this->type),
                fn ($query) => $query->whereIn('type', array_keys(self::SEARCHABLE)),
            )
            ->where(fn ($query) => $query
                ->where('title', 'like', $escaped)
                ->orWhere('excerpt', 'like', $escaped)
                ->orWhere('body', 'like', $escaped))
            ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', [$escaped])
            ->latest('published_at')
            ->take(40)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.site-search', [
            'results' => $this->results,
        ]);
    }
}
