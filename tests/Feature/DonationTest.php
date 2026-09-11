<?php

namespace Tests\Feature;

use App\Jobs\SendDonationReceipt;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\PaymentTransaction;
use App\Services\DonationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'sk_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('foundation.donations_enabled', true);
        Config::set('foundation.paystack_secret', self::SECRET);
        Config::set('foundation.currencies', ['NGN']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Adaeze Okonkwo',
            'email' => 'adaeze@example.org',
            'amount' => '10000',
            'currency' => 'NGN',
            'consent' => '1',
            'website' => '',
            'submission_key' => (string) Str::uuid(),
        ], $overrides);
    }

    private function fakeInitialize(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123'],
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function providerData(Donation $donation, array $overrides = []): array
    {
        return array_merge([
            'id' => 987654,
            'status' => 'success',
            'reference' => $donation->reference,
            'amount' => $donation->amount_minor,
            'currency' => $donation->currency,
            'customer' => ['email' => $donation->email],
        ], $overrides);
    }

    public function test_the_donate_page_renders(): void
    {
        $this->get('/donate')->assertOk()->assertSee('Make a donation');
    }

    public function test_giving_is_refused_when_the_provider_is_not_configured(): void
    {
        Config::set('foundation.paystack_secret', null);

        $this->get('/donate')->assertOk()->assertSee('Online giving is not yet available');

        $this->post('/donate', $this->payload())->assertStatus(503);

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_it_initializes_a_donation_and_redirects_to_the_provider(): void
    {
        $this->fakeInitialize();

        $this->post('/donate', $this->payload())
            ->assertRedirect('https://checkout.paystack.com/abc123');

        $donation = Donation::sole();

        $this->assertSame(1_000_000, $donation->amount_minor);
        $this->assertSame('pending', $donation->status);
        $this->assertSame('adaeze@example.org', $donation->email);

        // The reference is a UUID we generate, never a guessable sequence.
        $this->assertTrue(Str::isUuid($donation->reference));

        $this->assertDatabaseHas('payment_transactions', [
            'donation_id' => $donation->id,
            'status' => 'initialized',
        ]);
    }

    public function test_it_converts_decimal_amounts_to_minor_units_exactly(): void
    {
        $this->fakeInitialize();

        $this->post('/donate', $this->payload(['amount' => '1250.75']));

        $this->assertSame(125_075, Donation::sole()->amount_minor);
    }

    public function test_it_rejects_a_donation_without_consent(): void
    {
        $this->post('/donate', $this->payload(['consent' => null]))
            ->assertSessionHasErrors('consent');

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_it_rejects_a_currency_the_account_does_not_accept(): void
    {
        $this->post('/donate', $this->payload(['currency' => 'USD']))
            ->assertSessionHasErrors('currency');

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_it_rejects_an_inactive_campaign(): void
    {
        $campaign = Campaign::factory()->inactive()->create();

        $this->post('/donate', $this->payload(['campaign_id' => $campaign->id]))
            ->assertSessionHasErrors('campaign_id');
    }

    public function test_the_honeypot_rejects_a_bot_submission(): void
    {
        $this->post('/donate', $this->payload(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_resubmitting_the_same_form_does_not_create_a_second_donation(): void
    {
        $this->fakeInitialize();

        $payload = $this->payload();

        $this->post('/donate', $payload);
        $this->post('/donate', $payload);

        $this->assertDatabaseCount('donations', 1);
    }

    public function test_a_reused_submission_key_with_different_details_is_refused(): void
    {
        $this->fakeInitialize();

        $payload = $this->payload();
        $this->post('/donate', $payload);

        // Same key, different amount: the second request is an attempt to reuse
        // an initialized donation, not a retry of the first.
        $this->post('/donate', array_merge($payload, ['amount' => '999999']))
            ->assertSessionHasErrors('amount');

        $this->assertSame(1_000_000, Donation::sole()->amount_minor);
    }

    public function test_a_browser_redirect_alone_never_marks_a_donation_paid(): void
    {
        $donation = Donation::factory()->create();

        // The callback is reached with a valid reference, but the provider says
        // the charge failed. The redirect must carry no authority of its own.
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => $this->providerData($donation, ['status' => 'failed']),
            ]),
        ]);

        $this->get('/donate/callback?reference='.$donation->reference);

        $this->assertSame('failed', $donation->fresh()->status);
    }

    public function test_a_verified_callback_settles_the_donation_and_queues_a_receipt(): void
    {
        Queue::fake();

        $donation = Donation::factory()->create();

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => $this->providerData($donation),
            ]),
        ]);

        $this->get('/donate/callback?reference='.$donation->reference)
            ->assertRedirectContains('/donate/thank-you/');

        $donation->refresh();

        $this->assertSame('success', $donation->status);
        $this->assertSame('987654', $donation->provider_id);
        $this->assertNotNull($donation->paid_at);

        Queue::assertPushed(SendDonationReceipt::class);
    }

    public function test_settlement_is_refused_when_the_amount_does_not_match(): void
    {
        $donation = Donation::factory()->create(['amount_minor' => 500_000]);

        $this->expectException(ValidationException::class);

        app(DonationService::class)->settle($this->providerData($donation, ['amount' => 100]));
    }

    public function test_settlement_is_refused_when_the_currency_does_not_match(): void
    {
        $donation = Donation::factory()->create(['currency' => 'NGN']);

        $this->expectException(ValidationException::class);

        app(DonationService::class)->settle($this->providerData($donation, ['currency' => 'USD']));
    }

    public function test_settlement_is_refused_when_the_payer_email_does_not_match(): void
    {
        $donation = Donation::factory()->create();

        $this->expectException(ValidationException::class);

        app(DonationService::class)->settle(
            $this->providerData($donation, ['customer' => ['email' => 'someone-else@example.org']]),
        );
    }

    public function test_a_webhook_without_a_valid_signature_is_rejected(): void
    {
        $donation = Donation::factory()->create();

        $body = ['event' => 'charge.success', 'data' => $this->providerData($donation)];

        $this->postJson('/donate/webhook', $body, ['x-paystack-signature' => 'not-the-signature'])
            ->assertUnauthorized();

        $this->assertSame('pending', $donation->fresh()->status);
    }

    public function test_a_webhook_with_no_signature_at_all_is_rejected(): void
    {
        $donation = Donation::factory()->create();

        $this->postJson('/donate/webhook', ['event' => 'charge.success', 'data' => $this->providerData($donation)])
            ->assertUnauthorized();
    }

    public function test_a_correctly_signed_webhook_settles_the_donation(): void
    {
        Queue::fake();

        $donation = Donation::factory()->create();

        $body = json_encode(['event' => 'charge.success', 'data' => $this->providerData($donation)], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/donate/webhook',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_PAYSTACK_SIGNATURE' => hash_hmac('sha512', $body, self::SECRET),
            ],
            content: $body,
        )->assertOk();

        $this->assertSame('success', $donation->fresh()->status);
    }

    /**
     * The webhook is retried by the provider until it gets a 200, so replaying
     * the same event must never settle twice or send a second receipt.
     */
    public function test_a_replayed_webhook_settles_only_once(): void
    {
        Queue::fake();

        $donation = Donation::factory()->create();
        $data = $this->providerData($donation);

        app(DonationService::class)->settle($data);
        $paidAt = $donation->fresh()->paid_at;

        $this->travel(5)->minutes();
        app(DonationService::class)->settle($data);

        $donation->refresh();

        $this->assertSame('success', $donation->status);
        $this->assertEquals($paidAt, $donation->paid_at);

        $this->assertSame(1, PaymentTransaction::where('donation_id', $donation->id)->where('status', 'success')->count());

        Queue::assertPushed(SendDonationReceipt::class, 1);
    }

    public function test_a_successful_payment_arriving_after_a_failure_is_recorded(): void
    {
        Queue::fake();

        $donation = Donation::factory()->create();

        app(DonationService::class)->settle($this->providerData($donation, ['status' => 'failed']));
        $this->assertSame('failed', $donation->fresh()->status);

        app(DonationService::class)->settle($this->providerData($donation));
        $this->assertSame('success', $donation->fresh()->status);
    }

    public function test_a_success_without_a_provider_identifier_is_refused(): void
    {
        $donation = Donation::factory()->create();

        $this->expectException(ValidationException::class);

        app(DonationService::class)->settle($this->providerData($donation, ['id' => null]));
    }

    public function test_the_thank_you_page_requires_a_signed_url(): void
    {
        $donation = Donation::factory()->successful()->create();

        $this->get('/donate/thank-you/'.$donation->reference)->assertForbidden();

        $this->get(URL::temporarySignedRoute(
            'donations.thanks',
            now()->addHour(),
            ['donation' => $donation->reference],
        ))->assertOk()->assertSee('Thank you');
    }

    public function test_the_receipt_requires_a_signed_url_and_is_never_cached_or_indexed(): void
    {
        $donation = Donation::factory()->successful()->create();

        $this->get('/donate/receipt/'.$donation->reference)->assertForbidden();

        $this->get(URL::temporarySignedRoute(
            'donations.receipt',
            now()->addHour(),
            ['donation' => $donation->reference],
        ))
            ->assertOk()
            ->assertSee('Donation receipt')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        // Asserted as directives rather than an exact string: the framework may
        // add its own, but `no-store` and `private` must always be present.
        $cacheControl = $this->get(URL::temporarySignedRoute(
            'donations.receipt',
            now()->addHour(),
            ['donation' => $donation->reference],
        ))->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('private', $cacheControl);
    }

    public function test_no_receipt_is_available_for_an_unsettled_donation(): void
    {
        $donation = Donation::factory()->create();

        $this->get(URL::temporarySignedRoute(
            'donations.receipt',
            now()->addHour(),
            ['donation' => $donation->reference],
        ))->assertNotFound();
    }

    public function test_the_secret_key_never_reaches_the_browser(): void
    {
        $this->get('/donate')->assertOk()->assertDontSee(self::SECRET);
    }
}
