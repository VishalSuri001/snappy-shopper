<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DB\StoreService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StoreService::class, function ($app) {
            return new StoreService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
