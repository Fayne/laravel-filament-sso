<?php

namespace Fayne\Sso;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SsoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-sso.php', 'filament-sso');
        $this->app->singleton('sso', fn ($app) => new SsoManager($app));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-sso');
        Route::middleware('web')
            ->prefix(config('filament-sso.routes.prefix', 'sso'))
            ->group(__DIR__.'/../routes/web.php');

        $this->publishes([
            __DIR__.'/../config/filament-sso.php' => config_path('filament-sso.php'),
        ], 'filament-sso-config');
    }
}
