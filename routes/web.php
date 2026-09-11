<?php

use App\Http\Controllers\DonationController;
use App\Http\Controllers\EngagementController;
use App\Http\Controllers\SiteController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

/*
| Public website
|--------------------------------------------------------------------------
| The `/{slug}` page route is registered last so that every named section
| takes precedence over an editable page with a colliding slug. Slugs are
| resolved in the controller rather than by implicit binding, so each route
| can require both the expected content type and public visibility - a draft
| project must not become reachable through the news URL.
*/

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/search', [SiteController::class, 'search'])->name('search');

Route::get('/programs', [SiteController::class, 'programs'])->name('programs.index');
Route::get('/programs/{slug}', [SiteController::class, 'program'])->name('programs.show');

Route::get('/projects', [SiteController::class, 'projects'])->name('projects.index');
Route::get('/projects/{slug}', [SiteController::class, 'project'])->name('projects.show');

Route::get('/impact', [SiteController::class, 'impact'])->name('impact');

Route::get('/stories', [SiteController::class, 'stories'])->name('stories.index');
Route::get('/stories/{slug}', [SiteController::class, 'story'])->name('stories.show');

Route::get('/awards', [SiteController::class, 'awards'])->name('awards.index');
Route::get('/awards/{slug}', [SiteController::class, 'award'])->name('awards.show');

Route::get('/leadership', [SiteController::class, 'leadership'])->name('leadership.index');
Route::get('/leadership/{slug}', [SiteController::class, 'person'])->name('leadership.show');

Route::get('/partners', [SiteController::class, 'partners'])->name('partners');

Route::get('/gallery', [SiteController::class, 'gallery'])->name('gallery');

Route::get('/news', [SiteController::class, 'news'])->name('news.index');
Route::get('/news/{slug}', [SiteController::class, 'article'])->name('news.show');

Route::get('/resources', [SiteController::class, 'resources'])->name('resources.index');
Route::get('/resources/{slug}', [SiteController::class, 'publication'])->name('resources.show');

Route::get('/policies', [SiteController::class, 'policies'])->name('policies.index');
Route::get('/policies/{slug}', [SiteController::class, 'policy'])->name('policies.show');

Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('sitemap');

// Served by the application rather than as a static file so that the sitemap
// reference is always an absolute URL for the domain actually in use.
Route::get('/robots.txt', [SiteController::class, 'robots'])->name('robots');

/*
| Giving
|--------------------------------------------------------------------------
| Payment is confirmed server-side only. The browser redirect lands on the
| callback, which verifies with Paystack before anything is recorded as paid;
| the webhook settles the same donation idempotently if the donor closes the
| tab. Thank-you and receipt URLs are signed and time-limited so a donation
| reference cannot be enumerated.
*/

Route::get('/donate', [DonationController::class, 'index'])->name('donate');

Route::post('/donate', [DonationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('donations.store');

Route::get('/donate/callback', [DonationController::class, 'callback'])->name('donations.callback');

Route::post('/donate/webhook', [DonationController::class, 'webhook'])
    ->withoutMiddleware([PreventRequestForgery::class])
    ->name('donations.webhook');

Route::get('/donate/thank-you/{donation}', [DonationController::class, 'thanks'])
    ->middleware('signed')
    ->name('donations.thanks');

Route::get('/donate/receipt/{donation}', [DonationController::class, 'receipt'])
    ->middleware('signed')
    ->name('donations.receipt');

/*
| Engagement forms
|--------------------------------------------------------------------------
| Rate limited, honeypot-protected and idempotent on a per-submission key, so
| a double click or a browser retry cannot create duplicate records.
*/

Route::post('/enquiries', [EngagementController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('enquiries.store');

Route::post('/newsletter', [EngagementController::class, 'subscribe'])
    ->middleware('throttle:6,1')
    ->name('newsletter.subscribe');

Route::post('/membership', [EngagementController::class, 'apply'])
    ->middleware('throttle:6,1')
    ->name('membership.store');

Route::middleware('signed')->group(function (): void {
    Route::get('/newsletter/confirm/{subscriber}', [EngagementController::class, 'confirm'])->name('newsletter.confirm');
    Route::get('/newsletter/unsubscribe/{subscriber}', [EngagementController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
});

Route::post('/newsletter/confirm/{subscriber}', [EngagementController::class, 'confirmStore'])->name('newsletter.confirm.store');
Route::post('/newsletter/unsubscribe/{subscriber}', [EngagementController::class, 'unsubscribeStore'])->name('newsletter.unsubscribe.store');

/*
| Editable pages
|--------------------------------------------------------------------------
| Registered last: any published page of type `page` is served at the site
| root by its slug.
*/

Route::get('/{slug}', [SiteController::class, 'page'])->name('pages.show');
