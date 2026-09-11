<?php

namespace Tests\Feature;

use App\Mail\Acknowledgement;
use App\Models\Content;
use App\Models\Media;
use App\Models\MembershipApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function application(array $overrides = []): array
    {
        return array_merge([
            'contact_name' => 'Ngozi Okafor',
            'contact_phone' => '+2348056769517',
            'organisation_name' => 'Rumuola Traders Co-operative Society Limited',
            'organisation_address' => 'No 1 Ohiamini Street, Rumuola Road, Port Harcourt',
            'telephone' => '+2347033351303',
            'email' => 'ngozi@example.org',
            'cooperative_type' => 'trading',
            'organisation_website' => 'https://rumuola.example',
            'member_count' => 12,
            'ethnic_group' => 'Ikwerre',
            'message' => 'We have been trading together informally since 2021.',
            'consent' => '1',
            'website' => '',
            'submission_key' => (string) Str::uuid(),
        ], $overrides);
    }

    public function test_the_membership_page_renders_the_application_form(): void
    {
        Content::factory()->published()->type('page')->create([
            'title' => 'Membership Application',
            'slug' => 'membership',
        ]);

        $this->get('/membership')
            ->assertOk()
            ->assertSee('Membership application')
            ->assertSee('Name of organisation')
            ->assertSee('Type of co-operative');
    }

    public function test_it_records_an_application_and_acknowledges_it(): void
    {
        Mail::fake();

        $this->post('/membership', $this->application())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('membership_applications', [
            'organisation_name' => 'Rumuola Traders Co-operative Society Limited',
            'email' => 'ngozi@example.org',
            'cooperative_type' => 'trading',
            'status' => 'new',
        ]);

        $application = MembershipApplication::sole();
        $this->assertNotNull($application->consented_at);
        $this->assertSame(12, $application->member_count);

        Mail::assertQueued(Acknowledgement::class);
    }

    public function test_it_notifies_the_administration_address_when_one_is_configured(): void
    {
        Mail::fake();
        Config::set('foundation.admin_email', 'office@example.org');

        $this->post('/membership', $this->application());

        Mail::assertQueued(Acknowledgement::class, 2);
    }

    /**
     * Seven is the statutory minimum membership for registering a co-operative
     * society, so an ineligible application is refused rather than stored.
     */
    public function test_it_rejects_a_society_with_fewer_than_seven_members(): void
    {
        $this->post('/membership', $this->application(['member_count' => 6]))
            ->assertSessionHasErrors('member_count');

        $this->assertDatabaseCount('membership_applications', 0);
    }

    public function test_it_accepts_a_society_with_exactly_seven_members(): void
    {
        Mail::fake();

        $this->post('/membership', $this->application(['member_count' => 7]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('membership_applications', 1);
    }

    public function test_it_rejects_an_application_without_the_declaration(): void
    {
        $this->post('/membership', $this->application(['consent' => null]))
            ->assertSessionHasErrors('consent');

        $this->assertDatabaseCount('membership_applications', 0);
    }

    public function test_it_rejects_an_unknown_cooperative_type(): void
    {
        $this->post('/membership', $this->application(['cooperative_type' => 'piracy']))
            ->assertSessionHasErrors('cooperative_type');
    }

    public function test_the_honeypot_rejects_a_bot_application(): void
    {
        $this->post('/membership', $this->application(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('membership_applications', 0);
    }

    /**
     * The honeypot field and the society's own website field must not collide:
     * a real website has to survive submission.
     */
    public function test_the_declared_website_is_stored_alongside_the_honeypot(): void
    {
        Mail::fake();

        $this->post('/membership', $this->application())->assertSessionHasNoErrors();

        $this->assertSame('https://rumuola.example', MembershipApplication::sole()->organisation_website);
    }

    public function test_a_resubmitted_application_does_not_create_a_duplicate(): void
    {
        Mail::fake();

        $payload = $this->application();

        $this->post('/membership', $payload);
        $this->post('/membership', $payload);

        $this->assertDatabaseCount('membership_applications', 1);

        Mail::assertQueued(Acknowledgement::class, 1);
    }

    public function test_application_submissions_are_rate_limited(): void
    {
        Mail::fake();

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post('/membership', $this->application())->assertRedirect();
        }

        $this->post('/membership', $this->application())->assertStatus(429);
    }

    public function test_the_gallery_shows_approved_images_from_the_gallery_collection(): void
    {
        $shown = Media::factory()->inGallery()->create(['title' => 'Training day in Port Harcourt']);
        $pending = Media::factory()->inGallery()->pending()->create(['title' => 'Awaiting review']);
        $document = Media::factory()->inGallery()->document()->create(['title' => 'Annual report PDF']);

        $response = $this->get('/gallery')->assertOk();

        $response->assertSee($shown->alt, escape: false);
        $response->assertDontSee($pending->title);
        $response->assertDontSee($document->title);
    }

    /**
     * Approval permits a file to be used; it does not put it in the gallery. A
     * leadership portrait is approved for its own page and must not appear here.
     */
    public function test_the_gallery_excludes_approved_images_outside_the_collection(): void
    {
        $portrait = Media::factory()->create(['title' => 'Portrait of the Executive Director']);

        $this->assertTrue($portrait->approved);
        $this->assertNull($portrait->collection);

        $this->get('/gallery')
            ->assertOk()
            ->assertDontSee($portrait->alt, escape: false)
            ->assertSee('No photographs published yet');
    }

    public function test_the_gallery_shows_an_empty_state_when_nothing_is_approved(): void
    {
        Media::factory()->inGallery()->pending()->count(3)->create();

        $this->get('/gallery')
            ->assertOk()
            ->assertSee('No photographs published yet');
    }

    /**
     * The legacy map is consulted before the router, so a stale entry for a path
     * that now exists would redirect visitors away from a real page.
     */
    public function test_the_legacy_gallery_path_is_not_redirected_away(): void
    {
        $this->get('/gallery')->assertOk();
    }
}
