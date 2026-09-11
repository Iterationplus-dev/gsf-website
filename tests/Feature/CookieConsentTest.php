<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Setting;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The consent gate is only meaningful if the third parties really are absent
 * from the served HTML. A regression here is silent — the page looks identical
 * while the requests go out anyway — so each integration is pinned by the thing
 * that proves it cannot load: no live `src` for the provider.
 */
class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_banner_and_preferences_dialog_are_on_every_page(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('data-consent-banner', escape: false);
        $response->assertSee('data-consent-dialog', escape: false);

        foreach (['accept', 'reject', 'open', 'save'] as $action) {
            $response->assertSee('data-consent-action="'.$action.'"', escape: false);
        }

        foreach (['necessary', 'functional', 'analytics', 'marketing'] as $category) {
            $response->assertSee('data-consent-toggle="'.$category.'"', escape: false);
        }
    }

    /**
     * The necessary category is not a choice, so its control must be both
     * checked and unable to be cleared.
     */
    public function test_the_necessary_category_cannot_be_switched_off(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/data-consent-toggle="necessary"\s+checked\s+disabled/',
            $html,
        );
    }

    public function test_the_analytics_script_is_inert_until_consent_is_given(): void
    {
        config([
            'foundation.analytics_domain' => 'globalsupportfoundation.org',
            'foundation.analytics_script' => 'https://plausible.io/js/script.js',
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString(
            '<script type="text/plain" data-consent-category="analytics"',
            $html,
        );

        // A blocked script still carries its address, but never on a live tag.
        $this->assertSame(0, preg_match('/<script(?![^>]*type="text\/plain")[^>]*plausible\.io/', $html));
    }

    public function test_no_analytics_is_emitted_at_all_when_none_is_configured(): void
    {
        config(['foundation.analytics_domain' => null]);

        $this->get('/')->assertOk()->assertDontSee('data-consent-category="analytics"', escape: false);
    }

    public function test_the_home_page_videos_wait_for_marketing_consent(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-consent-src="https://www.youtube.com/embed/BFqwPHTwnO0"', $html);
        $this->assertSame(4, substr_count($html, 'data-consent-src="https://www.youtube.com/embed/'));

        // Nothing may reach YouTube before consent: no live iframe source.
        $this->assertSame(0, preg_match('/\ssrc="https:\/\/www\.youtube\.com/', $html));
    }

    public function test_the_contact_map_waits_for_functional_consent(): void
    {
        config([
            'foundation.maps_enabled' => true,
            'foundation.maps_key' => 'test-key',
        ]);

        Content::factory()->published()->type('page')->create([
            'title' => 'Contact Us',
            'slug' => 'contact-us',
        ]);

        Setting::query()->create(['key' => 'contact.address', 'value' => '1 Example Road']);
        Setting::query()->create(['key' => 'contact.city', 'value' => 'Port Harcourt']);

        $html = $this->get('/contact-us')->assertOk()->getContent();

        $this->assertStringContainsString('data-consent-category="functional"', $html);
        $this->assertStringContainsString('data-consent-src="https://www.google.com/maps/embed', $html);
        $this->assertSame(0, preg_match('/\ssrc="https:\/\/www\.google\.com\/maps\/embed/', $html));
    }

    /**
     * A blocked embed is useless if the visitor cannot act on it, so each one
     * offers to grant its own category and to open the full preferences.
     */
    public function test_a_blocked_embed_explains_itself_and_offers_a_way_forward(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('YouTube content is blocked');
        $response->assertSee('data-consent-action="allow"', escape: false);
        $response->assertSee('Open on YouTube instead');
    }

    public function test_the_footer_links_to_the_cookie_policy_and_reopens_preferences(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee(route('pages.show', 'cookie-policy'), escape: false);
        $response->assertSee('Cookie settings');
    }

    public function test_the_cookie_policy_page_is_published_and_names_every_category(): void
    {
        $this->seed(ContentSeeder::class);

        $response = $this->get('/cookie-policy')->assertOk();

        $response->assertSee('Cookie Policy');

        foreach (['Strictly necessary', 'Functional', 'Analytics', 'Marketing'] as $heading) {
            $response->assertSee($heading);
        }

        foreach (['Google Maps', 'Plausible', 'YouTube'] as $provider) {
            $response->assertSee($provider);
        }
    }
}
