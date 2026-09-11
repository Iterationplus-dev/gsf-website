<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\Donations\DonationResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\AuditLog;
use App\Models\Content;
use App\Models\Donation;
use App\Models\Media;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_panel_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_the_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_an_active_staff_account_reaches_the_dashboard(): void
    {
        $this->actingAs(User::factory()->role('admin')->create())
            ->get('/admin')
            ->assertOk();
    }

    /**
     * Deactivating an account must revoke access immediately, whatever role it
     * still carries — this is how access is withdrawn when someone leaves.
     */
    public function test_a_deactivated_account_is_refused_whatever_its_role(): void
    {
        $user = User::factory()->role('super_admin')->create(['is_active' => false]);

        $this->assertFalse($user->hasPermission('content.view'));
        $this->assertFalse($user->canAccessPanel(app(Panel::class)));

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_role_permissions_are_granted_as_expected(): void
    {
        $editor = User::factory()->role('editor')->create();
        $this->assertTrue($editor->hasPermission('content.edit'));
        $this->assertFalse($editor->hasPermission('content.publish'));
        $this->assertFalse($editor->hasPermission('donations.view'));

        $manager = User::factory()->role('content_manager')->create();
        $this->assertTrue($manager->hasPermission('content.publish'));
        $this->assertFalse($manager->hasPermission('donations.view'));

        $finance = User::factory()->role('finance')->create();
        $this->assertTrue($finance->hasPermission('donations.view'));
        $this->assertFalse($finance->hasPermission('content.edit'));

        $superAdmin = User::factory()->role('super_admin')->create();
        $this->assertTrue($superAdmin->hasPermission('anything.at.all'));
    }

    public function test_individually_granted_permissions_are_added_to_the_role(): void
    {
        $editor = User::factory()->role('editor')->create(['permissions' => ['donations.view']]);

        $this->assertTrue($editor->hasPermission('donations.view'));
        $this->assertFalse($editor->hasPermission('donations.export'));
    }

    /**
     * An editor may prepare a draft, but the act of publishing is reserved. The
     * model enforces it, so it holds no matter which screen the save came from.
     */
    public function test_an_editor_cannot_publish_content(): void
    {
        $this->actingAs(User::factory()->role('editor')->create());

        $content = Content::factory()->create();
        $content->update(['status' => 'published', 'published_at' => now()]);

        $content->refresh();
        $this->assertSame('draft', $content->status);
        $this->assertNull($content->published_at);
    }

    public function test_a_content_manager_can_publish_content(): void
    {
        $this->actingAs(User::factory()->role('content_manager')->create());

        $content = Content::factory()->create();
        $content->update(['status' => 'published', 'published_at' => now()]);

        $this->assertSame('published', $content->fresh()->status);
    }

    public function test_only_permitted_roles_see_the_donations_resource(): void
    {
        $this->actingAs(User::factory()->role('finance')->create());
        $this->assertTrue(DonationResource::canViewAny());

        $this->actingAs(User::factory()->role('editor')->create());
        $this->assertFalse(DonationResource::canViewAny());

        $this->actingAs(User::factory()->role('content_manager')->create());
        $this->assertFalse(DonationResource::canViewAny());
    }

    public function test_finance_cannot_edit_or_delete_a_settled_donation(): void
    {
        $finance = User::factory()->role('finance')->create();
        $donation = Donation::factory()->successful()->create();

        $this->assertTrue($finance->can('view', $donation));
        $this->assertFalse($finance->can('update', $donation));
        $this->assertFalse($finance->can('delete', $donation));
        $this->assertFalse($finance->can('create', Donation::class));
    }

    public function test_a_receipt_can_only_be_resent_for_a_settled_donation(): void
    {
        $finance = User::factory()->role('finance')->create();

        $this->assertTrue($finance->can('resendReceipt', Donation::factory()->successful()->create()));
        $this->assertFalse($finance->can('resendReceipt', Donation::factory()->create()));
    }

    public function test_finance_cannot_reach_the_content_resource(): void
    {
        $this->actingAs(User::factory()->role('finance')->create());

        $this->assertFalse(ContentResource::canViewAny());
    }

    public function test_only_a_super_administrator_manages_accounts(): void
    {
        $this->actingAs(User::factory()->role('admin')->create());
        $this->assertFalse(UserResource::canViewAny());

        $this->actingAs(User::factory()->role('super_admin')->create());
        $this->assertTrue(UserResource::canViewAny());
    }

    public function test_an_administrator_cannot_delete_their_own_account(): void
    {
        $user = User::factory()->role('super_admin')->create();
        $other = User::factory()->role('admin')->create();

        $this->assertFalse($user->can('delete', $user));
        $this->assertTrue($user->can('delete', $other));
    }

    public function test_only_publishers_may_delete_media(): void
    {
        $media = Media::factory()->create();

        $this->assertFalse(User::factory()->role('editor')->create()->can('delete', $media));
        $this->assertTrue(User::factory()->role('content_manager')->create()->can('delete', $media));
    }

    public function test_settings_are_only_editable_by_permitted_roles(): void
    {
        $this->actingAs(User::factory()->role('content_manager')->create());
        $this->assertFalse(ManageSettings::canAccess());

        $this->actingAs(User::factory()->role('admin')->create());
        $this->assertTrue(ManageSettings::canAccess());
    }

    public function test_content_changes_are_recorded_in_the_audit_trail(): void
    {
        $user = User::factory()->role('content_manager')->create();
        $this->actingAs($user);

        $content = Content::factory()->create();
        $content->update(['title' => 'A revised title']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'updated',
            'subject_type' => Content::class,
            'subject_id' => $content->id,
        ]);
    }

    /**
     * The trail records which fields changed, never their values: it must not
     * become a second, less protected copy of the content itself.
     */
    public function test_the_audit_trail_stores_field_names_but_not_values(): void
    {
        $this->actingAs(User::factory()->role('content_manager')->create());

        $content = Content::factory()->create();
        $content->update(['title' => 'Sensitive unpublished heading']);

        $log = AuditLog::where('action', 'updated')->latest('id')->sole();

        $this->assertContains('title', $log->changed_fields);
        $this->assertStringNotContainsString('Sensitive unpublished heading', json_encode($log->changed_fields));
    }

    public function test_two_factor_secrets_are_encrypted_and_hidden(): void
    {
        $user = User::factory()->role('admin')->create();

        $user->saveAppAuthenticationSecret('SECRETVALUE123');

        $this->assertSame('SECRETVALUE123', $user->fresh()->getAppAuthenticationSecret());

        // Stored encrypted, and never serialised out of the model.
        $stored = DB::table('users')->where('id', $user->id)->value('app_authentication_secret');
        $this->assertNotSame('SECRETVALUE123', $stored);
        $this->assertArrayNotHasKey('app_authentication_secret', $user->fresh()->toArray());
    }
}
