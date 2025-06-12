<?php

declare(strict_types=1);

namespace App\Interface;

interface PaymentGatewayInterface
{
    public function charge(array $customer, float $amount, float $tax): bool;
}
