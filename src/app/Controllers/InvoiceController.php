<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Container;
use App\Interface\PaymentGatewayInterface;
use App\Services\InvoiceService;
use App\Services\PaymentGateway\StripePayment;
use App\View;

class InvoiceController
{
    public function __construct(protected Container $container) {
    }
    public function index():View
    {
        $this->container->bind(PaymentGatewayInterface::class, StripePayment::class);

        $this->container->get(InvoiceService::class)->process([], 25);

        return View::make('layouts/invoices/index', ['title' => 'Invoices']);
    }

    public function create(): string
    {
        return "<form action='/invoices/store' method='post'>Amount: <label for='amount'></label><input type='text' name='amount'></form>";
    }

    public function store(): string
    {
        return $_POST['amount'];
    }
}
