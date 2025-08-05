<?php

namespace App\Providers;

use App\Services\OrderRowValidator;
use App\Services\ExcelOrderImportService;
use App\Services\OrderValidationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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

        $this->app->singleton(OrderValidationService::class, function ($app) {
            return new OrderValidationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Hoặc force HTTPS nếu có header từ proxy/load balancer
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
    }
}
