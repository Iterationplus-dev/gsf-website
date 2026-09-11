<?php

namespace Tests\Feature;

use App\Livewire\SiteSearch;
use App\Models\Content;
use App\Models\ImpactMetric;
use App\Models\Project;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Global Support Foundation', escape: false);
    }

    public function test_published_content_is_reachable_at_its_url(): void
    {
        $page = Content::factory()->published()->type('page')->create(['title' => 'About Us', 'slug' => 'about-us']);

        $this->get('/about-us')->assertOk()->assertSee($page->title);
    }

    /**
     * The single most important rule on this site: unverified content must not
     * reach a donor. Each state is checked at the detail URL, in the listing,
     * and in the sitemap.
     */
    public function test_unpublished_content_is_not_reachable(): void
    {
        $draft = Content::factory()->type('post')->create(['slug' => 'draft-article']);
        $scheduled = Content::factory()->scheduled()->type('post')->create(['slug' => 'scheduled-article']);
        $demo = Content::factory()->demo()->published()->type('post')->create(['slug' => 'demo-article']);

        $this->get('/news/draft-article')->assertNotFound();
        $this->get('/news/scheduled-article')->assertNotFound();
        $this->get('/news/demo-article')->assertNotFound();

        $listing = $this->get('/news')->assertOk();
        $listing->assertDontSee($draft->title);
        $listing->assertDontSee($scheduled->title);
        $listing->assertDontSee($demo->title);

        $sitemap = $this->get('/sitemap.xml')->assertOk();
        $sitemap->assertDontSee('draft-article');
        $sitemap->assertDontSee('scheduled-article');
        $sitemap->assertDontSee('demo-article');
    }

    public function test_scheduled_content_appears_once_its_publication_time_passes(): void
    {
        Content::factory()->type('post')->create([
            'slug' => 'timed-article',
            'title' => 'Timed article',
            'status' => 'scheduled',
            'published_at' => now()->addHour(),
        ]);

        $this->get('/news/timed-article')->assertNotFound();

        $this->travel(2)->hours();

        $this->get('/news/timed-article')->assertOk()->assertSee('Timed article');
    }

    public function test_demo_content_cannot_be_published_even_if_someone_tries(): void
    {
        $content = Content::factory()->demo()->create();

        $content->update(['status' => 'published', 'published_at' => now()]);

        $this->assertSame('draft', $content->fresh()->status);
    }

    public function test_a_slug_only_resolves_under_its_own_content_type(): void
    {
        Content::factory()->published()->type('post')->create(['slug' => 'shared-slug']);

        $this->get('/news/shared-slug')->assertOk();

        // The same slug must not resolve as a project, a program or a page.
        $this->get('/projects/shared-slug')->assertNotFound();
        $this->get('/programs/shared-slug')->assertNotFound();
        $this->get('/shared-slug')->assertNotFound();
    }

    public function test_projects_can_be_filtered_by_program_and_status(): void
    {
        $program = Content::factory()->published()->type('program')->create(['title' => 'Agriculture', 'slug' => 'agriculture']);

        $active = Content::factory()->published()->type('project')->create(['title' => 'Active farming project']);
        Project::factory()->for($active, 'content')->create(['status' => 'active', 'program_id' => $program->id]);

        $completed = Content::factory()->published()->type('project')->create(['title' => 'Completed training project']);
        Project::factory()->for($completed, 'content')->completed()->create();

        $this->get('/projects?status=active')
            ->assertOk()
            ->assertSee('Active farming project')
            ->assertDontSee('Completed training project');

        $this->get('/projects?program=agriculture')
            ->assertOk()
            ->assertSee('Active farming project')
            ->assertDontSee('Completed training project');
    }

    public function test_impact_metrics_require_a_source_before_they_can_be_published(): void
    {
        $metric = ImpactMetric::factory()->unsourced()->create(['title' => 'Unevidenced figure']);

        // Publishing without evidence is refused at the model, not just in the UI.
        $metric->update(['published' => true]);
        $this->assertFalse($metric->fresh()->published);

        $this->get('/impact')->assertOk()->assertDontSee('Unevidenced figure');

        $metric->update(['source' => 'Training attendance registers, 2026', 'published' => true]);
        $this->assertTrue($metric->fresh()->published);

        $this->get('/impact')->assertOk()->assertSee('Unevidenced figure');
    }

    public function test_legacy_urls_redirect_permanently(): void
    {
        Redirect::create(['from_path' => '/our-team', 'to_path' => '/leadership']);

        $this->get('/our-team')->assertRedirect('/leadership')->assertStatus(301);
    }

    public function test_search_only_returns_published_content(): void
    {
        Content::factory()->published()->type('post')->create(['title' => 'Cooperative training visible']);
        Content::factory()->type('post')->create(['title' => 'Cooperative training hidden']);

        Livewire::test(SiteSearch::class)
            ->set('term', 'Cooperative training')
            ->assertSee('Cooperative training visible')
            ->assertDontSee('Cooperative training hidden');
    }

    public function test_search_ignores_a_term_that_is_too_short(): void
    {
        Content::factory()->published()->type('post')->create(['title' => 'Agriculture']);

        Livewire::test(SiteSearch::class)
            ->set('term', 'a')
            ->assertSee('Start typing to search');
    }

    public function test_a_missing_page_returns_a_helpful_404(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee('We could not find that page');
    }

    public function test_security_headers_are_applied(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
