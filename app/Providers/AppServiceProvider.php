<?php

namespace App\Providers;

use App\Services\DB\DataCleanupService;
use App\Services\DB\StoreService;
use App\Services\Import\UKPostcode\Context\UKPostcodeImportContext;
use App\Services\Import\UKPostcode\Validator\CSVValidator;
use App\Services\Import\UKPostcodeImportService;
use Illuminate\Support\ServiceProvider;
use App\Services\Import\UKPostCode\Factories\UKPostcodeImportStrategyFactory;

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
        $this->app->singleton(DataCleanupService::class, function ($app) {
            return new DataCleanupService();
        });
        $this->app->singleton(UKPostcodeImportService::class, function ($app) {
            return new UKPostcodeImportService($app->make(DataCleanupService::class));
        });
        $this->app->singleton(CSVValidator::class, function ($app) {
            return new CSVValidator();
        });
        $this->app->singleton(UKPostcodeImportStrategyFactory::class, function ($app) {
            return new UKPostcodeImportStrategyFactory();
        });
        $this->app->singleton(UKPostcodeImportContext::class, function ($app) {
            return new UKPostcodeImportContext($app->make(UKPostcodeImportStrategyFactory::class));
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
