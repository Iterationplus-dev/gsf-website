<?php

namespace Tests\Feature;

use App\Mail\Acknowledgement;
use App\Models\Enquiry;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class EngagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function enquiry(array $overrides = []): array
    {
        return array_merge([
            'type' => 'contact',
            'name' => 'Chinelo Eze',
            'email' => 'chinelo@example.org',
            'message' => 'I would like to know more about your co-operative training programmes.',
            'consent' => '1',
            'website' => '',
            'submission_key' => (string) Str::uuid(),
        ], $overrides);
    }

    public function test_it_records_a_contact_enquiry_and_acknowledges_it(): void
    {
        Mail::fake();

        $this->post('/enquiries', $this->enquiry())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enquiries', [
            'type' => 'contact',
            'email' => 'chinelo@example.org',
            'status' => 'new',
        ]);

        $this->assertNotNull(Enquiry::sole()->consented_at);

        Mail::assertQueued(Acknowledgement::class);
    }

    public function test_it_notifies_the_administration_address_when_one_is_configured(): void
    {
        Mail::fake();
        Config::set('foundation.admin_email', 'office@example.org');

        $this->post('/enquiries', $this->enquiry());

        Mail::assertQueued(Acknowledgement::class, 2);
    }

    public function test_it_rejects_an_enquiry_without_consent(): void
    {
        $this->post('/enquiries', $this->enquiry(['consent' => null]))
            ->assertSessionHasErrors('consent');

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_it_rejects_an_unknown_enquiry_type(): void
    {
        $this->post('/enquiries', $this->enquiry(['type' => 'newsletter']))
            ->assertSessionHasErrors('type');
    }

    public function test_the_honeypot_rejects_a_bot_enquiry(): void
    {
        $this->post('/enquiries', $this->enquiry(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_a_resubmitted_enquiry_does_not_create_a_duplicate(): void
    {
        Mail::fake();

        $payload = $this->enquiry();

        $this->post('/enquiries', $payload);
        $this->post('/enquiries', $payload);

        $this->assertDatabaseCount('enquiries', 1);

        // The acknowledgement is sent once, not once per click.
        Mail::assertQueued(Acknowledgement::class, 1);
    }

    public function test_enquiry_submissions_are_rate_limited(): void
    {
        Mail::fake();

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post('/enquiries', $this->enquiry())->assertRedirect();
        }

        $this->post('/enquiries', $this->enquiry())->assertStatus(429);
    }

    public function test_subscribing_records_consent_and_sends_a_confirmation(): void
    {
        Mail::fake();
        Config::set('foundation.newsletter_double_opt_in', true);

        $this->post('/newsletter', [
            'email' => 'Reader@Example.org',
            'consent' => '1',
            'website' => '',
        ])->assertSessionHas('success');

        $subscriber = Subscriber::sole();

        // Stored lower-case so the same address cannot subscribe twice.
        $this->assertSame('reader@example.org', $subscriber->email);
        $this->assertSame('pending', $subscriber->status);
        $this->assertNotNull($subscriber->consented_at);

        Mail::assertQueued(Acknowledgement::class);
    }

    public function test_subscribing_without_double_opt_in_confirms_immediately(): void
    {
        Mail::fake();
        Config::set('foundation.newsletter_double_opt_in', false);

        $this->post('/newsletter', ['email' => 'reader@example.org', 'consent' => '1', 'website' => '']);

        $this->assertSame('subscribed', Subscriber::sole()->status);
    }

    public function test_subscribing_twice_does_not_duplicate_the_record(): void
    {
        Mail::fake();

        $payload = ['email' => 'reader@example.org', 'consent' => '1', 'website' => ''];

        $this->post('/newsletter', $payload);
        $this->post('/newsletter', $payload);

        $this->assertDatabaseCount('subscribers', 1);
    }

    public function test_it_rejects_a_subscription_without_consent(): void
    {
        $this->post('/newsletter', ['email' => 'reader@example.org', 'website' => ''])
            ->assertSessionHasErrors('consent');

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_confirming_a_subscription_requires_a_signed_link(): void
    {
        $subscriber = Subscriber::factory()->create();

        $this->get('/newsletter/confirm/'.$subscriber->id)->assertForbidden();

        $this->get(URL::temporarySignedRoute('newsletter.confirm', now()->addDay(), ['subscriber' => $subscriber->id]))
            ->assertOk()
            ->assertSee('Confirm your subscription');
    }

    /**
     * Email clients and security scanners follow links in messages. Opening the
     * unsubscribe link must therefore show a form, and only the POST may change
     * anything.
     */
    public function test_opening_the_unsubscribe_link_does_not_unsubscribe_on_its_own(): void
    {
        $subscriber = Subscriber::factory()->subscribed()->create();

        $this->get(URL::temporarySignedRoute('newsletter.unsubscribe', now()->addDay(), ['subscriber' => $subscriber->id]))
            ->assertOk();

        $this->assertSame('subscribed', $subscriber->fresh()->status);

        $this->post('/newsletter/unsubscribe/'.$subscriber->id)->assertRedirect('/');

        $subscriber->refresh();
        $this->assertSame('unsubscribed', $subscriber->status);
        $this->assertNotNull($subscriber->unsubscribed_at);
    }

    public function test_confirming_marks_the_subscriber_as_subscribed(): void
    {
        $subscriber = Subscriber::factory()->create();

        $this->post('/newsletter/confirm/'.$subscriber->id)->assertRedirect('/');

        $subscriber->refresh();
        $this->assertSame('subscribed', $subscriber->status);
        $this->assertNotNull($subscriber->confirmed_at);
    }
}
