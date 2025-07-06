<?php

namespace App\Providers;

use App\Services\OrderRowValidator;
use App\Services\ExcelOrderImportService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OrderRowValidator::class, function ($app) {
            return new OrderRowValidator();
        });

        $this->app->singleton(ExcelOrderImportService::class, function ($app) {
            return new ExcelOrderImportService($app->make(OrderRowValidator::class));
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
