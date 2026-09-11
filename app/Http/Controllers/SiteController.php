<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ImpactMetric;
use App\Models\Media;
use App\Models\Redirect;
use App\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public website rendering.
 *
 * Every read goes through {@see published()} or {@see find()}, so draft,
 * scheduled-for-later and demo records are excluded from listings, detail
 * pages, search and the sitemap by construction rather than by remembering to
 * filter at each call site.
 */
class SiteController extends Controller
{
    private const PER_PAGE = 12;

    /** Thumbnails are small, so a gallery page carries more of them than a listing. */
    private const GALLERY_PER_PAGE = 24;

    /**
     * Photography for the home page's target-group cards, keyed in the order
     * the section presents them.
     *
     * @var array<string, string>
     */
    private const TARGET_GROUP_IMAGES = [
        'youths' => 'image/upload/v1789047718/unemployed-Youths-1.png',
        'disabilities' => 'image/upload/v1789047715/disabilities-1.png',
        'retirees' => 'image/upload/v1789047715/Retirees_Senior_Citizens.png',
        'businesses' => 'image/upload/v1789045974/Established-Businesses_Startups.png',
    ];

    /**
     * Faint backdrops for the right half of each overview page's hero, and the
     * eyebrow that sits above its title.
     *
     * @var array<string, array{image: string, eyebrow: string}>
     */
    private const OVERVIEW_PAGES = [
        'about-us' => [
            'image' => 'image/upload/v1789045974/Established-Businesses_Startups.png',
            'eyebrow' => 'Who we are',
        ],
        'mission-vision-values' => [
            'image' => 'image/upload/v1789049558/bb2.jpg',
            'eyebrow' => 'What we exist to do',
        ],
        'our-history' => [
            'image' => 'image/upload/v1789049558/bb2.jpg',
            'eyebrow' => 'How we got here',
        ],
    ];

    /** Backdrop for the home page's mission band. */
    private const MISSION_BAND_IMAGE = 'image/upload/v1789047705/ngo.jpg';

    /** Fixed editorial image beside the home page's opening section. */
    private const HOME_SECTION_IMAGE = 'image/upload/v1789044981/stronger-together-brighter-futures-posible.png';

    /**
     * Publicly visible records of one content type.
     */
    private function published(string $type): Builder
    {
        return Content::query()
            ->ofType($type)
            ->published()
            ->with('image');
    }

    /**
     * Resolve a slug within one content type, or fail with a 404.
     */
    private function find(string $type, string $slug): Content
    {
        $content = $this->published($type)->where('slug', $slug)->first();

        if (! $content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    public function home(): View
    {
        return view('pages.home', [
            'programs' => $this->published('program')->orderBy('position')->take(6)->get(),
            'projects' => $this->published('project')
                ->with('project.program')
                ->orderByDesc('featured')
                ->orderByDesc('published_at')
                ->take(3)
                ->get(),
            'stories' => $this->published('story')->latest('published_at')->take(2)->get(),
            'articles' => $this->published('post')->latest('published_at')->take(3)->get(),
            'partners' => $this->published('partner')->orderBy('position')->get(),
            'metrics' => ImpactMetric::published()->orderBy('position')->take(4)->get(),
            'sectionImage' => Media::approved()->where('path', self::HOME_SECTION_IMAGE)->first(),
            'targetGroupImages' => $this->mediaByKey(self::TARGET_GROUP_IMAGES),
            'missionImage' => Media::approved()->where('path', self::MISSION_BAND_IMAGE)->first(),
        ]);
    }

    /**
     * Resolve a map of short name => media path into short name => media, so a
     * template can ask for `youths` rather than repeat a Cloudinary path.
     *
     * @param  array<string, string>  $paths
     * @return array<string, Media>
     */
    private function mediaByKey(array $paths): array
    {
        $media = Media::approved()->whereIn('path', array_values($paths))->get()->keyBy('path');

        return array_filter(array_map(
            fn (string $path): ?Media => $media->get($path),
            $paths,
        ));
    }

    public function page(string $slug): View
    {
        $content = $this->find('page', $slug);

        // A few pages carry behaviour beyond their body copy: a map and an
        // enquiry form, a profile layout, a team listing.
        $template = match ($slug) {
            'about-us', 'mission-vision-values', 'our-history' => 'pages.overview',
            'governance', 'transparency' => 'pages.accountability',
            'contact-us' => 'pages.contact',
            'our-founder' => 'pages.founder',
            'partner-with-us' => 'pages.partner',
            'volunteer' => 'pages.volunteer',
            'get-involved' => 'pages.get-involved',
            'membership' => 'pages.membership',
            default => 'pages.page',
        };

        return view($template, ['content' => $content] + $this->extrasFor($slug));
    }

    /**
     * @return array<string, mixed>
     */
    private function extrasFor(string $slug): array
    {
        return match ($slug) {
            'our-founder' => [
                'founder' => $this->published('person')->where('featured', true)->first(),
                'metrics' => ImpactMetric::published()->orderBy('position')->take(4)->get(),
            ],
            'about-us', 'mission-vision-values', 'our-history' => [
                'heroImage' => Media::approved()
                    ->where('path', self::OVERVIEW_PAGES[$slug]['image'])
                    ->first(),
                'eyebrow' => self::OVERVIEW_PAGES[$slug]['eyebrow'],
                'metrics' => ImpactMetric::published()->orderBy('position')->take(4)->get(),
            ],
            'governance' => ['accountabilitySection' => 'governance'],
            'transparency' => ['accountabilitySection' => 'transparency'],
            default => [],
        };
    }

    public function programs(): View
    {
        return view('pages.programs', [
            'programs' => $this->published('program')->orderBy('position')->get(),
        ]);
    }

    public function program(string $slug): View
    {
        $program = $this->find('program', $slug);

        return view('pages.program', [
            'content' => $program,
            'projects' => $this->published('project')
                ->whereHas('project', fn ($query) => $query->where('program_id', $program->id))
                ->with('project.program')
                ->take(6)
                ->get(),
        ]);
    }

    public function projects(Request $request): View
    {
        $query = $this->published('project')->with('project.program');

        if ($status = $request->string('status')->toString()) {
            $query->whereHas('project', fn ($inner) => $inner->where('status', $status));
        }

        if ($program = $request->string('program')->toString()) {
            $query->whereHas(
                'project.program',
                fn ($inner) => $inner->where('slug', $program),
            );
        }

        return view('pages.projects', [
            'projects' => $query->orderByDesc('featured')->latest('published_at')->paginate(self::PER_PAGE)->withQueryString(),
            'programs' => $this->published('program')->orderBy('position')->get(),
            'status' => $status,
            'program' => $program,
        ]);
    }

    public function project(string $slug): View
    {
        $content = $this->find('project', $slug);
        $content->load('project.program', 'project.manager');

        return view('pages.project', ['content' => $content]);
    }

    public function impact(): View
    {
        return view('pages.impact', [
            'metrics' => ImpactMetric::published()->orderBy('position')->orderByDesc('year')->get(),
            'years' => ImpactMetric::published()->distinct()->orderByDesc('year')->pluck('year')->filter()->values(),
            'stories' => $this->published('story')->latest('published_at')->take(3)->get(),
            // The project card names its parent programme, so that relation is
            // loaded here too; lazy loading is disabled application-wide.
            'projects' => $this->published('project')->with('project.program')->take(6)->get(),
        ]);
    }

    public function stories(): View
    {
        return view('pages.stories', [
            'stories' => $this->published('story')->latest('published_at')->paginate(self::PER_PAGE),
        ]);
    }

    public function story(string $slug): View
    {
        return view('pages.story', ['content' => $this->find('story', $slug)]);
    }

    public function awards(): View
    {
        return view('pages.awards', [
            'awards' => $this->published('award')->orderBy('position')->get(),
            'metrics' => ImpactMetric::published()->orderBy('position')->take(4)->get(),
        ]);
    }

    public function award(string $slug): View
    {
        return view('pages.award', ['content' => $this->find('award', $slug)]);
    }

    public function leadership(): View
    {
        return view('pages.leadership', [
            'content' => $this->published('page')->where('slug', 'leadership')->first(),
            'people' => $this->published('person')->orderBy('position')->get(),
            'board' => $this->published('board')->orderBy('position')->get(),
            'metrics' => ImpactMetric::published()->orderBy('position')->take(4)->get(),
        ]);
    }

    public function person(string $slug): View
    {
        return view('pages.person', ['content' => $this->find('person', $slug)]);
    }

    public function partners(): View
    {
        return view('pages.partners', [
            'partners' => $this->published('partner')->orderBy('position')->get(),
        ]);
    }

    /**
     * Photographs of the foundation's work.
     *
     * Only approved images placed in the gallery collection appear. Approval
     * alone is not enough: a leadership portrait or an award certificate is
     * approved for its own page, not for a public photo gallery. Media is also
     * created unapproved by {@see MediaService::store()}, so a freshly uploaded
     * file cannot reach this page before someone has reviewed it - which matters
     * most here, where the subjects are identifiable people.
     */
    public function gallery(): View
    {
        return view('pages.gallery', [
            'images' => Media::approved()
                ->inCollection(Media::GALLERY)
                ->where('mime', 'like', 'image/%')
                ->latest('id')
                ->paginate(self::GALLERY_PER_PAGE)
                ->withQueryString(),
        ]);
    }

    public function news(Request $request): View
    {
        $query = $this->published('post');

        if ($category = $request->string('category')->toString()) {
            $query->where('category', $category);
        }

        return view('pages.news', [
            'articles' => $query->latest('published_at')->paginate(self::PER_PAGE)->withQueryString(),
            'categories' => $this->published('post')->distinct()->pluck('category')->filter()->sort()->values(),
            'category' => $category,
        ]);
    }

    public function article(string $slug): View
    {
        $content = $this->find('post', $slug);

        return view('pages.article', [
            'content' => $content,
            'related' => $this->published('post')
                ->where('id', '!=', $content->id)
                ->when($content->category, fn ($query) => $query->where('category', $content->category))
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }

    public function resources(): View
    {
        return view('pages.resources', [
            'publications' => $this->published('publication')->orderByDesc('published_at')->paginate(self::PER_PAGE),
        ]);
    }

    public function publication(string $slug): View
    {
        return view('pages.publication', ['content' => $this->find('publication', $slug)]);
    }

    public function policies(): View
    {
        return view('pages.policies', [
            'policies' => $this->published('policy')->orderBy('position')->get(),
        ]);
    }

    public function policy(string $slug): View
    {
        return view('pages.policy', ['content' => $this->find('policy', $slug)]);
    }

    public function search(Request $request): View
    {
        return view('pages.search', ['term' => $request->string('q')->trim()->toString()]);
    }

    /**
     * Crawler directives. Donor-facing pages carry personal detail or expire,
     * so they are excluded even though they are already unguessable and signed.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /donate/receipt/',
            'Disallow: /donate/thank-you/',
            'Disallow: /donate/callback',
            'Disallow: /newsletter/',
            'Disallow: /admin',
            'Disallow: /search',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ];

        return response(implode('
', $lines))->header('Content-Type', 'text/plain');
    }

    /**
     * XML sitemap covering every publicly reachable URL.
     */
    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('programs.index'), 'priority' => '0.9'],
            ['loc' => route('projects.index'), 'priority' => '0.9'],
            ['loc' => route('impact'), 'priority' => '0.9'],
            ['loc' => route('donate'), 'priority' => '0.9'],
            ['loc' => route('stories.index'), 'priority' => '0.7'],
            ['loc' => route('awards.index'), 'priority' => '0.6'],
            ['loc' => route('leadership.index'), 'priority' => '0.7'],
            ['loc' => route('partners'), 'priority' => '0.5'],
            ['loc' => route('gallery'), 'priority' => '0.5'],
            ['loc' => route('news.index'), 'priority' => '0.8'],
            ['loc' => route('resources.index'), 'priority' => '0.6'],
            ['loc' => route('policies.index'), 'priority' => '0.4'],
        ]);

        $content = Content::published()
            ->whereIn('type', ['page', 'program', 'project', 'post', 'story', 'award', 'person', 'publication', 'policy'])
            ->get(['type', 'slug', 'updated_at'])
            ->map(fn (Content $item): array => [
                'loc' => $item->url,
                'lastmod' => $item->updated_at?->toAtomString(),
                'priority' => '0.6',
            ]);

        return response()
            ->view('sitemap', ['urls' => $urls->concat($content)->reject(fn (array $item): bool => array_key_exists('/'.ltrim((string) parse_url($item['loc'], PHP_URL_PATH), '/'), Redirect::map()))->unique('loc')->values()])
            ->header('Content-Type', 'application/xml');
    }
}
