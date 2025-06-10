<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends HttpKernel
{
    /**
     * Các nhóm middleware được định nghĩa cho ứng dụng.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // Middleware cho các yêu cầu web
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // Middleware cho các yêu cầu API
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Các middleware toàn cục được định nghĩa cho ứng dụng.
     *
     * @var array
     */
    protected $middleware = [
        // Middleware toàn cục
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ];

    /**
     * Các middleware có thể được sử dụng cho các tuyến riêng lẻ.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // Middleware cho các tuyến riêng lẻ
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'auth.api_token' => \App\Http\Middleware\AuthenticateApiToken::class,
        'auth.api.token' => \App\Http\Middleware\AuthenticateApiToken::class,
        // Thêm các middleware khác nếu cần
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('tracking:sync-from-factory')->dailyAt('02:00');
    }
}
