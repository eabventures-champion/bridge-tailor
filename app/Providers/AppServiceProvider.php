<?php

namespace App\Providers;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);

        if ($this->app->environment('production') || config('app.force_https', false)) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $view->with('uiText', fn (string $key, ?string $default = null) => PlatformSetting::text($key, $default));
            $view->with('uiVisible', fn (string $key, bool $default = true) => PlatformSetting::visible($key, $default));
        });
    }
}
