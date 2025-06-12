<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway;

use App\Interface\PaymentGatewayInterface;

class StripePayment implements PaymentGatewayInterface
{
	public function charge(array $customer, float $amount, float $tax): bool 
    {
        return true;
    }
}
