<?php

namespace App\Services;

class SalesTaxService
{
    public function calculate(array $customer, float $amount): float
    {
        //sleep(1);

        return $amount * 6.5 / 100;
    }
}
