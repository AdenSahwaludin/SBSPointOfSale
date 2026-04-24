<?php

namespace App\Providers;

use App\Events\PaymentReceived;
use App\Listeners\UpdateTrustScoreOnPayment;
use Illuminate\Support\Facades\Event;
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
        // Set default locale for Carbon
        \Carbon\Carbon::setLocale('id');
        app()->setLocale('id');

        // Register event listeners
        Event::listen(
            PaymentReceived::class,
            UpdateTrustScoreOnPayment::class
        );
    }
}
