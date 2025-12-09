<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Services\EmailService;
use App\Contracts\EmailServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EmailServiceInterface::class, function ($app) {
            return new EmailService();
        });

        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define API rate limiter: by authenticated user id or IP when unauthenticated
        RateLimiter::for('api', function (Request $request) {
            $key = optional($request->user())->id ?: $request->ip();
            return Limit::perMinute(60)->by($key);
        });
    }
}
