<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\PlatformOverview;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Optional per-account two-factor. Offered rather than enforced so
            // that a staff member without a smartphone is not locked out of the
            // content they are responsible for.
            ->multiFactorAuthentication(
                AppAuthentication::make()
                    ->recoverable(),
                isRequired: false,
            )
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('Global Support Foundation')
            ->favicon(asset('images/logo.jpg'))
            ->colors([
                // Mirrors the public site so the two do not feel like different
                // systems to the staff who move between them.
                'primary' => Color::hex('#0f5539'),
                'danger' => Color::hex('#b42318'),
                'warning' => Color::hex('#b45309'),
                'success' => Color::hex('#157347'),
            ])
            ->navigationGroups(['Content', 'Giving', 'Engagement', 'Settings'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                PlatformOverview::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
