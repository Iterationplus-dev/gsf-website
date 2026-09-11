<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_slider_renders_every_slide_with_its_copy(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('Providing Business/Entrepreneurship Training');
        $response->assertSee('Children Development Programmes');
        $response->assertSee('Advisory Services to Co-operatives.');
        $response->assertSee('Partners with Central Bank of Nigeria');

        foreach (['slide-1.jpg', 'slide-2.jpg', 'slide-3.jpg', 'slider-6.png'] as $image) {
            $response->assertSee('https://res.cloudinary.com/dt6xndtv/image/upload/', escape: false);
            $response->assertSee($image, escape: false);
        }
    }

    /**
     * The banner rotates, so no single caption can be the page heading. The
     * document still needs exactly one h1.
     */
    public function test_the_home_page_has_a_single_heading(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
    }

    /**
     * The build downloads the font files, but nothing references them unless the
     * layout emits the face declarations. When that link is missing the browser
     * falls back to a system sans without any error, so it is worth pinning.
     */
    public function test_the_layout_declares_the_self_hosted_font_faces(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('@font-face', $html);
        $this->assertStringContainsString('DM Sans', $html);
        $this->assertStringContainsString('dm-sans-400-normal', $html);

        // Matched against the built filename rather than the word "Inter",
        // which also occurs in "setInterval" in the slider script.
        $this->assertStringNotContainsString('/inter-', $html);
    }

    public function test_the_logo_is_served_from_the_configured_url(): void
    {
        Setting::query()->create([
            'key' => 'org.logo',
            'value' => 'https://res.cloudinary.com/dt6xndtv/image/upload/v1789025382/logo.jpg',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://res.cloudinary.com/dt6xndtv/image/upload/v1789025382/logo.jpg', escape: false);
    }

    /**
     * An administrator who clears the field must not be left with broken images,
     * so a missing or empty value falls back to the bundled file.
     */
    public function test_the_logo_falls_back_to_the_bundled_file_when_unset(): void
    {
        Setting::query()->create(['key' => 'org.logo', 'value' => '']);

        $this->get('/')->assertOk()->assertSee('images/logo.jpg', escape: false);
    }

    public function test_the_blog_menu_lists_recent_posts(): void
    {
        $recent = Content::factory()->published()->type('post')->create([
            'title' => 'Co-operative registration explained',
            'slug' => 'co-operative-registration-explained',
        ]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('Blog');
        $response->assertSee('Co-operative registration explained');
        $response->assertSee(route('news.show', $recent->slug), escape: false);
    }

    public function test_the_blog_menu_excludes_unpublished_posts(): void
    {
        Content::factory()->type('post')->create([
            'title' => 'Unfinished draft article',
            'slug' => 'unfinished-draft-article',
        ]);

        $this->get('/')->assertOk()->assertDontSee('Unfinished draft article');
    }

    /**
     * With nothing published the menu must still lead somewhere rather than
     * opening onto an empty panel.
     */
    public function test_the_blog_menu_still_offers_the_archive_when_no_posts_exist(): void
    {
        $this->assertSame(0, Content::query()->ofType('post')->count());

        $this->get('/')->assertOk()->assertSee('All posts');
    }

    public function test_the_blog_menu_shows_only_the_four_newest_posts(): void
    {
        foreach (range(1, 6) as $age) {
            Content::factory()->published()->type('post')->create([
                'title' => 'Post number '.$age,
                'slug' => 'post-number-'.$age,
                'published_at' => now()->subDays($age),
            ]);
        }

        $response = $this->get('/')->assertOk();

        foreach ([1, 2, 3, 4] as $newest) {
            $response->assertSee('Post number '.$newest);
        }

        foreach ([5, 6] as $older) {
            $response->assertDontSee('Post number '.$older);
        }
    }
}
