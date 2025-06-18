<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\ServiceProviderInterface;
use App\Services\PaymentGateway\StripePayment;
use Illuminate\Container\Container;

class AppServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected Container $container) {}
    public function register(): void
    {
        $this->container->bind(PaymentGatewayInterface::class, StripePayment::class);
    }

    public function boot(): void
    {
        date_default_timezone_set('UTC');
    }
}
