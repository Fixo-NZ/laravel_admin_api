<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
