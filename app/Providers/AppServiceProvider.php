<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\Content;
use App\Models\ImpactMetric;
use App\Models\Media;
use App\Models\Setting;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Services\AdminNotification;
use App\Services\Media\MediaStorageManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Records whose changes are worth attributing to a person.
     *
     * @var list<class-string<Model>>
     */
    private const AUDITED = [
        Content::class,
        Campaign::class,
        ImpactMetric::class,
        Media::class,
        Setting::class,
        User::class,
    ];

    public function register(): void
    {
        $this->app->singleton(MediaStorageManager::class);
    }

    public function boot(): void
    {
        // Lazy loading is a silent performance problem in listing views; in
        // development it should be a loud one.
        Model::preventLazyLoading(! $this->app->isProduction());

        // Behind TLS termination the generated URLs must still be https, or
        // signed donation links break and mixed content appears.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        foreach (self::AUDITED as $model) {
            $model::observe(AuditObserver::class);
        }

        AdminNotification::configureActions();

        $this->shareOrganisationDetails();
        $this->shareBlogMenu();
        $this->shareCallToActionBackground();
    }

    /**
     * Backdrop for the giving call to action, which appears on most pages and
     * is therefore not passed in by any one controller.
     */
    private function shareCallToActionBackground(): void
    {
        View::composer('components.donate-cta', function ($view): void {
            $view->with('ctaBackground', Media::approved()
                ->where('path', 'image/upload/v1789044992/build-support.jpg')
                ->first());
        });
    }

    /**
     * The most recent posts, for the Blog menu in the header.
     *
     * Scoped to the header alone rather than to `components.*`, which would run
     * this query once for every component rendered on the page.
     */
    private function shareBlogMenu(): void
    {
        View::composer('components.site-header', function ($view): void {
            $view->with('recentPosts', Content::query()
                ->ofType('post')
                ->published()
                ->latest('published_at')
                ->take(4)
                ->get(['id', 'title', 'slug', 'published_at']));
        });
    }

    /**
     * Organisational contact details, social links and navigation are needed by
     * the header and footer on every page, so they are resolved once per request
     * from the cached settings table rather than passed by each controller.
     */
    private function shareOrganisationDetails(): void
    {
        View::composer([
            'components.*', 'pages.*', 'livewire.*', 'emails.*', 'errors.*',
            'donate', 'donation-status', 'receipt', 'newsletter-action',
        ], function ($view): void {
            $view->with('settings', Setting::values());
        });
    }
}
