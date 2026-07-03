<?php

namespace App\Providers;

use App\Domain\Banner\Models\Banner;
use App\Domain\Banner\Observers\BannerObserver;
use App\Domain\Shipment\Models\ShipmentImage;
use App\Domain\Shipment\Observers\ShipmentImageObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Banner::observe(BannerObserver::class);
        ShipmentImage::observe(ShipmentImageObserver::class);

        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Horizon::auth(static fn () => true);
    }
}
