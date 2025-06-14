<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;
use App\Contracts\PaymentGatewayInterface;
use App\Services\PaymentGateway\StripePayment;

class AppServiceProvider
{
    public function __construct(protected Container $container) {}
    public function register(): void
    {
        // Bind interface to implementation
        $this->container->bind(PaymentGatewayInterface::class, StripePayment::class);
    }

    public function boot(): void
    {
        date_default_timezone_set('UTC');
    }
}
