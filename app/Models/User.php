<?php

namespace App\Models;

use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    use HasFactory, Notifiable;

    /** @var array<string, string> */
    public const ROLES = [
        'super_admin' => 'Super administrator',
        'admin' => 'Administrator',
        'content_manager' => 'Content manager',
        'editor' => 'Editor',
        'finance' => 'Donation manager',
    ];

    /**
     * Baseline permissions granted by each role. Individual grants recorded on the
     * user are added to these; nothing is ever subtracted, so removing access means
     * changing the role.
     *
     * @var array<string, list<string>>
     */
    private const ROLE_PERMISSIONS = [
        'admin' => [
            'content.view', 'content.edit', 'content.publish',
            'operations.view', 'operations.edit', 'settings.edit', 'media.edit',
        ],
        'content_manager' => ['content.view', 'content.edit', 'content.publish', 'media.edit'],
        'editor' => ['content.view', 'content.edit', 'media.edit'],
        'finance' => ['donations.view', 'donations.export', 'donations.receipt'],
    ];

    /**
     * Role and activation are assignable because only the policy-gated administration
     * panel and the console account command ever write them; no public route does.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'permissions', 'is_active'];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && array_key_exists((string) $this->role, self::ROLES);
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->role === 'super_admin') {
            return true;
        }

        $granted = array_merge(self::ROLE_PERMISSIONS[$this->role] ?? [], $this->permissions ?? []);

        return in_array($permission, $granted, true);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? 'Unknown';
    }

    /*
    |--------------------------------------------------------------------------
    | Optional two-factor authentication
    |--------------------------------------------------------------------------
    |
    | The secret and recovery codes are encrypted at rest by the model casts and
    | hidden from serialisation. No administration screen reads them back: they
    | belong to the account holder alone.
    |
    */

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    /**
     * @return ?array<string>
     */
    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    /**
     * @param  ?array<string>  $codes
     */
    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }
}
