<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Interface\PaymentGatewayInterface;
use App\Model\Invoice;
use App\Model\User;
use Ramsey\Uuid\Uuid;

class InvoiceService
{
    public function __construct(
        protected SalesTaxService $salesTaxService,
        protected PaymentGatewayInterface $paymentGatewayService,
        protected EmailService $emailService
    ) {
    }

    public function process(array $customer, float $amount): bool
    {
        //1. calculate sales tax
        $tax = $this->salesTaxService->calculate($customer, $amount);

        //2. process invoice
        if (!$this->paymentGatewayService->charge($customer, $amount, $tax)) {
            return false;
        }

        //3. send payment receipt
        $this->emailService->send($customer, 'receipt');

        echo 'Invoive has been Processed</br>';

        return true;
    }
}
