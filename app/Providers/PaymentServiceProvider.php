<?php

namespace App\Providers;

use App\Services\PaymentGateway\DemoPaymentGateway;
use App\Services\PaymentGateway\PaymentGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, DemoPaymentGateway::class);
    }
}
