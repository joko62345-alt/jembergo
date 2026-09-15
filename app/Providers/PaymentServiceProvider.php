<?php

namespace App\Providers;

use App\Services\PaymentGateway\DemoPaymentGateway;
use App\Services\PaymentGateway\MidtransGateway;
use App\Services\PaymentGateway\PaymentGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function (): PaymentGateway {
            return config('payment_gateway.driver', config('services.payment_gateway.driver', 'demo')) === 'midtrans'
                ? app(MidtransGateway::class)
                : app(DemoPaymentGateway::class);
        });
    }
}
