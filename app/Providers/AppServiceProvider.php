<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
<<<<<<< HEAD
=======
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Services\EmailService;
use App\Contracts\EmailServiceInterface;
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
<<<<<<< HEAD
        //
=======
        $this->app->singleton(EmailServiceInterface::class, function ($app) {
            return new EmailService();
        });

        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService();
        });
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
<<<<<<< HEAD
        //
=======
        // Define API rate limiter: by authenticated user id or IP when unauthenticated
        RateLimiter::for('api', function (Request $request) {
            $key = optional($request->user())->id ?: $request->ip();
            return Limit::perMinute(60)->by($key);
        });
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
    }
}
